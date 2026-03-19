<?php
/**
 * BACKEND DO CHATBOT (IA): agent_backend.php
 * Interface principal de IA que utiliza a API do Google Gemini para 
 * responder dúvidas sobre o inventário, chamados e gestão de TI.
 * Possui lógica de fallback para comandos locais caso a API falhe.
 */
ob_start(); // Inicia o buffer para evitar qualquer saída acidental antes do JSON
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'conexao.php';
ob_clean(); // Limpa o buffer de qualquer aviso ou erro anterior
header('Content-Type: application/json');

// ============================================================
// CONFIGURAÇÃO DA API GEMINI
// ============================================================
require_once 'funcoes_ai.php';

// Check connection
if ($conn->connect_error) {
    echo json_encode(['reply' => __('Erro de conexão com o banco de dados.')]);
    exit;
}

$message = isset($_POST['message']) ? mb_strtolower(trim($_POST['message'])) : '';
$userId = isset($_SESSION['id_usuarios']) ? $_SESSION['id_usuarios'] : 0;
$userName = isset($_SESSION['nome_usuario']) ? $_SESSION['nome_usuario'] : 'Usuário';

// Pre-check: Verify if AI Agent is globally enabled or specific module is enabled
$iaStatusQuery = mysqli_query($conn, "SELECT ia_agente_ativo, ia_chat_ativo FROM configuracoes_alertas LIMIT 1");
$iaEnabled = true;
$chatEnabled = true;

if ($iaStatusQuery && mysqli_num_rows($iaStatusQuery) > 0) {
    $iaRow = mysqli_fetch_assoc($iaStatusQuery);
    $iaEnabled = (bool) ($iaRow['ia_agente_ativo'] ?? 1);
    $chatEnabled = (bool) ($iaRow['ia_chat_ativo'] ?? 1);
}

if (!$iaEnabled || !$chatEnabled) {
    $reason = !$iaEnabled ? __('Agente de IA') : __('Chat por IA');
    echo json_encode([
        'reply' => "⚠️ " . sprintf(__('O %s está desabilitado no momento. Entre em contato com o administrador para mais informações.'), $reason),
        'debug' => ['ai_enabled' => $iaEnabled, 'chat_enabled' => $chatEnabled]
    ]);
    exit;
}

// Handle clear history request
if (isset($_POST['clear_history'])) {
    $_SESSION['chat_history'] = [];
    echo json_encode(['reply' => 'ok']);
    exit;
}

if (empty($message)) {
    echo json_encode(['reply' => __('Por favor, digite algo.')]);
    exit;
}

// Função auxiliar para formatar valores monetários
function formatMoney($val)
{
    return 'R$ ' . number_format($val, 2, ',', '.');
}

// Helper: Get system context for AI
function getSystemContext($conn, $userName)
{
    $context = __("Você é o assistente virtual do sistema Asset Management (Asset MGT). ");
    $context .= sprintf(__("O usuário atual é: %s."), $userName) . "\n";
    $context .= __("O sistema gerencia ativos de TI, usuários, fornecedores, centros de custo, licenças e chamados de suporte. ");
    $context .= __("Responda sempre no idioma solicitado pelo usuário (Português ou Inglês) de forma profissional, proativa e amigável.") . "\n\n";
    $context .= __("Dados atuais do sistema:") . "\n";

    $safeQuery = function ($sql) use ($conn) {
        try {
            $r = @$conn->query($sql);
            return $r ?: null;
        } catch (Exception $e) {
            return null;
        }
    };

    $r = $safeQuery("SELECT COUNT(*) as total FROM ativos");
    if ($r) {
        $row = $r->fetch_assoc();
        $context .= "- " . __('Total de ativos:') . " " . $row['total'] . "\n";
    }

    $r = $safeQuery("SELECT status, COUNT(*) as total FROM ativos GROUP BY status");
    if ($r && $r->num_rows > 0) {
        $statuses = [];
        while ($row = $r->fetch_assoc()) {
            $statuses[] = $row['status'] . ": " . $row['total'];
        }
        $context .= "- " . __('Status dos ativos:') . " " . implode(", ", $statuses) . "\n";
    }

    $r = $safeQuery("SELECT id, titulo, status FROM chamados WHERE status != 'Fechado' ORDER BY data_abertura DESC LIMIT 3");
    if ($r && $r->num_rows > 0) {
        $context .= "- " . __('Chamados recentes:') . " ";
        $tks = [];
        while ($row = $r->fetch_assoc()) {
            $tks[] = "#" . $row['id'] . " " . $row['titulo'] . " (" . $row['status'] . ")";
        }
        $context .= implode(" | ", $tks) . "\n";
    }

    return $context;
}

// ============================================================
// CONVERSATION HISTORY (stored in session)
// ============================================================
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// Keep last 10 messages for context
$conversationHistory = $_SESSION['chat_history'];

// ============================================================
// LOCAL PATTERN MATCHING (Direct DB queries - fast responses)
// ============================================================
$handledLocally = false;

// 1. Greet
if (preg_match('/(oi|ola|olá|bom dia|boa tarde|boa noite|hey|e aí|eae|hi|hello|good morning|good afternoon|good evening)/', $message)) {
    $handledLocally = true;
    $nome = isset($_SESSION['nome_usuario']) ? $_SESSION['nome_usuario'] : __('Usuário');
    $reply = sprintf(__('Olá, %s! 👋 Como posso ajudar você hoje? Posso responder sobre ativos, chamados, fornecedores e muito mais!'), $nome);
}

// 2. My Assets (Meus ativos)
elseif (preg_match('/(meus ativos|comigo|minha posse|my assets|with me|my possession)/', $message)) {
    $handledLocally = true;
    if ($userId == 0) {
        $reply = __('Não consegui identificar seu usuário para buscar seus ativos.');
    } else {
        $sql = "SELECT modelo, tag, hostName, categoria FROM ativos WHERE assigned_to = '$userId'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = "📦 " . $row['modelo'] . " (Tag: " . $row['tag'] . " | " . $row['categoria'] . ")";
            }
            $reply = __('Você tem os seguintes ativos em sua posse:') . "\n" . implode("\n", $items);
        } else {
            $reply = __('Você não possui nenhum ativo vinculado ao seu usuário no momento.');
        }
    }
}

// 3. Search Asset by Tag or Hostname
elseif (preg_match('/(buscar|procurar|onde est[áa]|localizar|search|find|where is).*ativo (.+)/', $message, $matches)) {
    $handledLocally = true;
    $query = $conn->real_escape_string(trim($matches[2]));
    $query = str_replace('?', '', $query);

    $sql = "SELECT a.modelo, a.tag, a.hostName, a.status, a.categoria, a.valor, u.nome as usuario_nome 
            FROM ativos a 
            LEFT JOIN usuarios u ON a.assigned_to = u.id_usuarios 
            WHERE a.tag LIKE '%$query%' OR a.hostName LIKE '%$query%' OR a.modelo LIKE '%$query%' LIMIT 1";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $asset = $result->fetch_assoc();
        $assigned = $asset['usuario_nome'] ? sprintf(__('está com **%s**'), $asset['usuario_nome']) : __('não está atribuído a ninguém');
        $reply = "🔍 " . sprintf(__('Encontrei o ativo **%s** (Tag: %s).'), $asset['modelo'], $asset['tag']) . "\n" .
            "📂 " . __('Categoria:') . " " . $asset['categoria'] . "\n" .
            "📊 " . __('Status:') . " " . $asset['status'] . "\n" .
            "💰 " . __('Valor:') . " " . formatMoney($asset['valor']) . "\n" .
            "👤 " . sprintf(__('Atualmente %s.'), $assigned);
    } else {
        $reply = sprintf(__('Não encontrei nenhum ativo com a Tag, Hostname ou Modelo contendo \'%s\'.'), $query);
    }
}

// 4. Supplier Info
elseif (preg_match('/(contato|telefone|email|dados|quem [ée]|contact|phone|info|who is).*fornecedor (.+)/', $message, $matches)) {
    $handledLocally = true;
    $query = $conn->real_escape_string(trim($matches[2]));
    $query = str_replace('?', '', $query);

    $sql = "SELECT nomeEmpresa, telefone, email, servico FROM fornecedor WHERE nomeEmpresa LIKE '%$query%' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $f = $result->fetch_assoc();
        $reply = "🏢 " . __('Fornecedor:') . " **" . $f['nomeEmpresa'] . "**\n" .
            "🔧 " . __('Serviço:') . " " . $f['servico'] . "\n" .
            "📞 " . __('Tel:') . " " . $f['telefone'] . "\n" .
            "📧 " . __('Email:') . " " . $f['email'];
    } else {
        $reply = sprintf(__('Não encontrei informações sobre o fornecedor \'%s\'.'), $query);
    }
}

// 5. List Open Tickets (Detailed)
elseif (preg_match('/(listar|quais|mostrar|list|show).*chamados.*(abertos|pendentes|open|pending)/', $message)) {
    $handledLocally = true;
    $sql = "SELECT id, titulo, status, prioridade, data_abertura FROM chamados WHERE status != 'Fechado' AND status != 'Resolvido' ORDER BY data_abertura DESC LIMIT 5";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $tickets = [];
        while ($row = $result->fetch_assoc()) {
            $date = date('d/m/Y', strtotime($row['data_abertura']));
            $priority = isset($row['prioridade']) ? " | " . $row['prioridade'] : "";
            $tickets[] = "🎫 #" . $row['id'] . " - " . $row['titulo'] . " (" . $row['status'] . $priority . ") em $date";
        }
        $reply = __('Aqui estão os últimos chamados abertos:') . "\n" . implode("\n", $tickets);
    } else {
        $reply = "✅ " . __('Não há chamados abertos no momento. Tudo em dia!');
    }
}

// 6. Total Value of Assets
elseif (preg_match('/(valor|custo|preço|value|cost|price).*total.*ativos/', $message)) {
    $handledLocally = true;
    $sql = "SELECT SUM(valor) as total FROM ativos";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $reply = "💰 " . sprintf(__('O valor total estimado de todos os ativos cadastrados é de **%s**.'), formatMoney($row['total']));
}

// 7. General Stats (Count Assets)
elseif (preg_match('/(quantos|total|n[uú]mero|how many|number).*ativos/', $message)) {
    $handledLocally = true;
    $sql = "SELECT COUNT(*) as total FROM ativos";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $reply = "📊 " . sprintf(__('Atualmente, temos um total de **%s** ativos cadastrados no sistema.'), $row['total']);
}

// 8. General Stats (Count Tickets)
elseif (preg_match('/(chamados|tickets).* (abertos|pendentes|open|pending)/', $message)) {
    $handledLocally = true;
    $sql = "SELECT COUNT(*) as total FROM chamados WHERE status IN ('Aberto', 'Pendente', 'Em Andamento')";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $reply = "🎫 " . sprintf(__('Existem **%s** chamados em aberto ou em andamento.'), $row['total']);
}

// 9. Who is user X
elseif (preg_match('/(quem (é|e)|who is) (.+)/', $message, $matches)) {
    $handledLocally = true;
    $name = $conn->real_escape_string(trim($matches[3]));
    $name = str_replace('?', '', $name);

    $sql = "SELECT nome, sobrenome, email, funcao, centroDeCusto FROM usuarios WHERE nome LIKE '%$name%' OR sobrenome LIKE '%$name%' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $reply = "👤 " . sprintf(__('Encontrei: **%s %s**'), $user['nome'], $user['sobrenome']) . "\n" .
            "💼 " . __('Função:') . " " . $user['funcao'] . "\n" .
            "📧 " . __('Email:') . " " . $user['email'] . "\n" .
            "🏢 " . __('Centro de Custo:') . " " . ($user['centroDeCusto'] ?: __('Não definido'));
    } else {
        $reply = sprintf(__('Desculpe, não encontrei nenhum usuário com o nome \'%s\'.'), $name);
    }
}

// 10. Help / Menu
elseif (preg_match('/(ajuda|help|menu|opções|opcoes|o que voc[eê] faz|what do you do)/', $message)) {
    $handledLocally = true;
    $reply = "🤖 " . __('Sou o assistente virtual do Asset MGT, integrado com **IA de ponta**!') . "\n\n" .
        "📌 **" . __('Consultas rápidas (dados do sistema):') . "**\n" .
        "- '" . __('Quais são meus ativos?') . "'\n" .
        "- '" . __('Onde está o ativo [Tag/Hostname]?') . "'\n" .
        "- '" . __('Contato do fornecedor [Nome]') . "'\n" .
        "- '" . __('Listar chamados abertos') . "'\n" .
        "- '" . __('Qual o valor total dos ativos?') . "'\n" .
        "- '" . __('Quantos chamados temos?') . "'\n" .
        "- '" . __('Quem é [Nome]?') . "'\n\n" .
        "🧠 **" . __('Perguntas inteligentes (IA):') . "**\n" .
        "- " . __('Qualquer pergunta sobre TI, gestão de ativos, suporte...') . "\n" .
        "- '" . __('Sugira melhorias para o ciclo de vida dos ativos') . "'\n" .
        "- '" . __('Qual a melhor prática para gestão de inventário?') . "'\n" .
        "- '" . __('Me dê um resumo geral do sistema') . "'";
}

// 11. Where to register asset
elseif (preg_match('/(onde|como|where|how).*(cadastrar|criar|novo|register|create|new).*ativo/', $message)) {
    $handledLocally = true;
    $reply = "📝 " . sprintf(__('Você pode cadastrar novos ativos no menu **%s** → **%s**.'), __('Ativos'), __('Cadastrar Novo'));
}

// 12. List Categories
elseif (preg_match('/(categorias|tipos|categories|types).*ativos/', $message)) {
    $handledLocally = true;
    $sql = "SELECT DISTINCT categoria FROM ativos WHERE categoria IS NOT NULL LIMIT 10";
    $result = $conn->query($sql);
    $cats = [];
    while ($row = $result->fetch_assoc()) {
        $cats[] = $row['categoria'];
    }
    $reply = "📂 " . __('As categorias de ativos cadastradas são:') . " " . implode(', ', $cats);
}

// 13. System summary / dashboard
elseif (preg_match('/(resumo|dashboard|visão geral|panorama|status geral|summary|overview)/', $message)) {
    $handledLocally = true;
    // Total ativos
    $r = $conn->query("SELECT COUNT(*) as total FROM ativos");
    $totalAtivos = $r->fetch_assoc()['total'];

    // Ativos ativos
    $r = $conn->query("SELECT COUNT(*) as total FROM ativos WHERE status = 'Ativo'");
    $totalAtivosAtivos = $r->fetch_assoc()['total'];

    // Valor total
    $r = $conn->query("SELECT SUM(valor) as total FROM ativos");
    $valorTotal = $r->fetch_assoc()['total'];

    // Chamados abertos
    $r = $conn->query("SELECT COUNT(*) as total FROM chamados WHERE status IN ('Aberto', 'Pendente', 'Em Andamento')");
    $chamadosAbertos = $r->fetch_assoc()['total'];

    // Usuários
    $r = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    $totalUsuarios = $r->fetch_assoc()['total'];

    // Fornecedores
    $r = $conn->query("SELECT COUNT(*) as total FROM fornecedor");
    $totalFornecedores = $r->fetch_assoc()['total'];

    $reply = "📊 **" . __('Resumo Geral do Sistema') . "**\n\n" .
        "📦 " . sprintf(__('Ativos cadastrados: **%s** (%s ativos)'), $totalAtivos, $totalAtivosAtivos) . "\n" .
        "💰 " . sprintf(__('Valor total: **%s**'), formatMoney($valorTotal)) . "\n" .
        "🎫 " . sprintf(__('Chamados em aberto: **%s**'), $chamadosAbertos) . "\n" .
        "👥 " . sprintf(__('Usuários: **%s**'), $totalUsuarios) . "\n" .
        "🏢 " . sprintf(__('Fornecedores: **%s**'), $totalFornecedores);
}

// DEFAULT: Send to AI, fallback to local response
else {
    $systemContext = getSystemContext($conn, $userName);
    $aiReply = callAI($_POST['message'], $systemContext, $conversationHistory);

    if ($aiReply !== null) {
        $reply = $aiReply;
    } else {
        // Fallback: provide a helpful local response instead of an error
        $r = @$conn->query("SELECT COUNT(*) as total FROM ativos");
        $totalAtivos = $r ? $r->fetch_assoc()['total'] : '?';
        $r = @$conn->query("SELECT COUNT(*) as total FROM chamados WHERE status IN ('Aberto','Pendente','Em Andamento')");
        $chamados = $r ? $r->fetch_assoc()['total'] : '?';

        $reply = "🤖 " . __('Não consegui acessar a IA no momento, mas posso ajudar com comandos locais!') . "\n\n" .
            "📊 **" . __('Status rápido:') . "** " . sprintf(__('%s ativos cadastrados, %s chamados abertos.'), $totalAtivos, $chamados) . "\n\n" .
            "📌 **" . __('Comandos disponíveis:') . "**\n" .
            "- **" . __('resumo') . "** → " . __('Visão geral do sistema') . "\n" .
            "- **" . __('meus ativos') . "** → " . __('Seus ativos atribuídos') . "\n" .
            "- **" . __('chamados abertos') . "** → " . __('Listar chamados') . "\n" .
            "- **" . __('onde está [tag]') . "** → " . __('Localizar ativo') . "\n" .
            "- **" . __('ajuda') . "** → " . __('Ver todos os comandos');
    }
}

// Save conversation history
$_SESSION['chat_history'][] = ['role' => 'user', 'text' => $_POST['message']];
$_SESSION['chat_history'][] = ['role' => 'model', 'text' => $reply];

// Keep only last 20 messages (10 exchanges)
if (count($_SESSION['chat_history']) > 20) {
    $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -20);
}

echo json_encode([
    'reply' => $reply,
    'debug' => [
        'handled_locally' => $handledLocally,
        'last_ai_error' => $_SESSION['last_ai_error'] ?? null,
        'gemini_key_set' => defined('GEMINI_API_KEY'),
        'github_token_set' => defined('GITHUB_TOKEN')
    ]
]);

$conn->close();
