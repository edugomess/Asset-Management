<?php
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
require_once 'credentials.php';
$GEMINI_API_KEY = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
// ============================================================

// Check connection
if ($conn->connect_error) {
    echo json_encode(['reply' => 'Erro de conexão com o banco de dados.']);
    exit;
}

$message = isset($_POST['message']) ? mb_strtolower(trim($_POST['message'])) : '';

// Handle clear history request
if (isset($_POST['clear_history'])) {
    $_SESSION['chat_history'] = [];
    echo json_encode(['reply' => 'ok']);
    exit;
}

if (empty($message)) {
    echo json_encode(['reply' => 'Por favor, digite algo.']);
    exit;
}

$reply = "";
$userId = isset($_SESSION['id_usuarios']) ? $_SESSION['id_usuarios'] : 0;
$userName = isset($_SESSION['nome_usuario']) ? $_SESSION['nome_usuario'] : 'Usuário';

// Helper function to format money
function formatMoney($val)
{
    return 'R$ ' . number_format($val, 2, ',', '.');
}

// Helper: Get system context for Gemini (summarized DB data)
function getSystemContext($conn, $userName)
{
    $context = "Você é o assistente virtual do sistema Asset Management (Asset MGT). ";
    $context .= "O usuário atual é: " . $userName . ".\n";
    $context .= "O sistema gerencia ativos de TI, usuários, fornecedores, centros de custo, licenças e chamados de suporte. ";
    $context .= "Responda sempre em português brasileiro de forma profissional, proativa e amigável. ";
    $context .= "Seja um consultor de TI: não apenas liste dados, mas interprete-os e sugira melhorias se apropriado.\n\n";
    $context .= "Dados atuais do sistema:\n";

    // Safe query helper
    $safeQuery = function ($sql) use ($conn) {
        try {
            $r = @$conn->query($sql);
            return $r ?: null;
        } catch (Exception $e) {
            return null;
        }
    };

    // Total assets
    $r = $safeQuery("SELECT COUNT(*) as total FROM ativos");
    if ($r) {
        $row = $r->fetch_assoc();
        $context .= "- Total de ativos: " . $row['total'] . "\n";
    }

    // Assets by status (including Maintenance)
    $r = $safeQuery("SELECT status, COUNT(*) as total FROM ativos GROUP BY status");
    if ($r && $r->num_rows > 0) {
        $statuses = [];
        while ($row = $r->fetch_assoc()) {
            $statuses[] = $row['status'] . ": " . $row['total'];
        }
        $context .= "- Status dos ativos: " . implode(", ", $statuses) . "\n";
    }

    // Assets in maintenance detail
    $r = $safeQuery("SELECT modelo, tag, problema FROM ativos WHERE status = 'Manutenção' LIMIT 5");
    if ($r && $r->num_rows > 0) {
        $context .= "- Ativos em manutenção:\n";
        while ($row = $r->fetch_assoc()) {
            $context .= "  * " . $row['modelo'] . " (Tag: " . $row['tag'] . ") - Problema: " . ($row['problema'] ?: 'N/A') . "\n";
        }
    }

    // Licenses count and summary
    $r = $safeQuery("SELECT software, tipo, COUNT(*) as total FROM licencas GROUP BY software, tipo");
    if ($r && $r->num_rows > 0) {
        $context .= "- Licenças de software:\n";
        while ($row = $r->fetch_assoc()) {
            $context .= "  * " . $row['software'] . " (" . $row['tipo'] . "): " . $row['total'] . "\n";
        }
    }

    // Cost Centers
    $r = $safeQuery("SELECT nomeSetor, unidade FROM centro_de_custo LIMIT 10");
    if ($r && $r->num_rows > 0) {
        $ccs = [];
        while ($row = $r->fetch_assoc()) {
            $ccs[] = $row['nomeSetor'] . " (" . $row['unidade'] . ")";
        }
        $context .= "- Centros de Custo principais: " . implode(", ", $ccs) . "\n";
    }

    // Open tickets
    $r = $safeQuery("SELECT COUNT(*) as total FROM chamados WHERE status IN ('Aberto', 'Pendente', 'Em Andamento')");
    if ($r) {
        $row = $r->fetch_assoc();
        $context .= "- Chamados ativos: " . $row['total'] . "\n";
    }

    // Recent tickets
    $r = $safeQuery("SELECT id, titulo, status, prioridade FROM chamados ORDER BY data_abertura DESC LIMIT 3");
    if ($r && $r->num_rows > 0) {
        $context .= "- Últimos chamados: ";
        $tks = [];
        while ($row = $r->fetch_assoc()) {
            $tks[] = "#" . $row['id'] . " " . $row['titulo'] . " (" . $row['status'] . ")";
        }
        $context .= implode(" | ", $tks) . "\n";
    }

    return $context;
}

// Helper: Call Gemini API
function callGemini($userMessage, $systemContext, $apiKey, $conversationHistory = [])
{
    if (empty($apiKey)) {
        return null;
    }

    // Models in order of performance/availability
    $models = ['gemini-flash-latest'];

    // Build payload
    $payload = [
        'system_instruction' => [
            'parts' => [['text' => $systemContext]]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 1024,
            'topP' => 0.9,
        ]
    ];

    // Build contents with history
    $contents = [];
    if (!empty($conversationHistory)) {
        foreach ($conversationHistory as $msg) {
            $contents[] = [
                'role' => $msg['role'],
                'parts' => [['text' => $msg['text']]]
            ];
        }
    }
    $contents[] = [
        'role' => 'user',
        'parts' => [['text' => $userMessage]]
    ];
    $payload['contents'] = $contents;
    $jsonPayload = json_encode($payload);

    // Try each model, with retry on rate limit
    foreach ($models as $model) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=" . $apiKey;

        $maxRetries = 2;
        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => $jsonPayload,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                error_log("Gemini cURL Error ($model): " . $curlError);
                break; // Try next model
            }

            // Rate limited - wait and retry or try next model
            if ($httpCode === 429 && $attempt < $maxRetries - 1) {
                sleep(5);
                continue;
            }

            if ($httpCode === 429) {
                break; // Try next model
            }

            if ($httpCode !== 200) {
                error_log("Gemini HTTP Error $httpCode ($model): " . $response);
                $_SESSION['last_ai_error'] = "Model $model returned HTTP $httpCode: " . substr($response, 0, 100);
                break; // Try next model
            }

            $data = json_decode($response, true);
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $data['candidates'][0]['content']['parts'][0]['text'];
            }

            return null;
        }
    }

    // All models exhausted - return null so caller can fallback to local response
    return null;
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
$handledLocally = true;

// 1. Greet
if (preg_match('/(oi|ola|olá|bom dia|boa tarde|boa noite|hey|e aí|eae)/', $message)) {
    $nome = isset($_SESSION['nome_usuario']) ? $_SESSION['nome_usuario'] : 'Usuário';
    $reply = "Olá, $nome! 👋 Como posso ajudar você hoje? Posso responder sobre ativos, chamados, fornecedores e muito mais!";
}

// 2. My Assets (Meus ativos)
elseif (preg_match('/(meus ativos|comigo|minha posse)/', $message)) {
    if ($userId == 0) {
        $reply = "Não consegui identificar seu usuário para buscar seus ativos.";
    } else {
        $sql = "SELECT modelo, tag, hostName, categoria FROM ativos WHERE assigned_to = '$userId'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = "📦 " . $row['modelo'] . " (Tag: " . $row['tag'] . " | " . $row['categoria'] . ")";
            }
            $reply = "Você tem os seguintes ativos em sua posse:\n" . implode("\n", $items);
        } else {
            $reply = "Você não possui nenhum ativo vinculado ao seu usuário no momento.";
        }
    }
}

// 3. Search Asset by Tag or Hostname
elseif (preg_match('/(buscar|procurar|onde est[áa]|localizar).*ativo (.+)/', $message, $matches)) {
    $query = $conn->real_escape_string(trim($matches[2]));
    $query = str_replace('?', '', $query);

    $sql = "SELECT a.modelo, a.tag, a.hostName, a.status, a.categoria, a.valor, u.nome as usuario_nome 
            FROM ativos a 
            LEFT JOIN usuarios u ON a.assigned_to = u.id_usuarios 
            WHERE a.tag LIKE '%$query%' OR a.hostName LIKE '%$query%' OR a.modelo LIKE '%$query%' LIMIT 1";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $asset = $result->fetch_assoc();
        $assigned = $asset['usuario_nome'] ? "está com **" . $asset['usuario_nome'] . "**" : "não está atribuído a ninguém";
        $reply = "🔍 Encontrei o ativo **" . $asset['modelo'] . "** (Tag: " . $asset['tag'] . ").\n" .
            "📂 Categoria: " . $asset['categoria'] . "\n" .
            "📊 Status: " . $asset['status'] . "\n" .
            "💰 Valor: " . formatMoney($asset['valor']) . "\n" .
            "👤 Atualmente $assigned.";
    } else {
        $reply = "Não encontrei nenhum ativo com a Tag, Hostname ou Modelo contendo '$query'.";
    }
}

// 4. Supplier Info
elseif (preg_match('/(contato|telefone|email|dados|quem [ée]).*fornecedor (.+)/', $message, $matches)) {
    $query = $conn->real_escape_string(trim($matches[2]));
    $query = str_replace('?', '', $query);

    $sql = "SELECT nomeEmpresa, telefone, email, servico FROM fornecedor WHERE nomeEmpresa LIKE '%$query%' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $f = $result->fetch_assoc();
        $reply = "🏢 Fornecedor: **" . $f['nomeEmpresa'] . "**\n" .
            "🔧 Serviço: " . $f['servico'] . "\n" .
            "📞 Tel: " . $f['telefone'] . "\n" .
            "📧 Email: " . $f['email'];
    } else {
        $reply = "Não encontrei informações sobre o fornecedor '$query'.";
    }
}

// 5. List Open Tickets (Detailed)
elseif (preg_match('/(listar|quais|mostrar).*chamados.*(abertos|pendentes)/', $message)) {
    $sql = "SELECT id, titulo, status, prioridade, data_abertura FROM chamados WHERE status != 'Fechado' AND status != 'Resolvido' ORDER BY data_abertura DESC LIMIT 5";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $tickets = [];
        while ($row = $result->fetch_assoc()) {
            $date = date('d/m/Y', strtotime($row['data_abertura']));
            $priority = isset($row['prioridade']) ? " | " . $row['prioridade'] : "";
            $tickets[] = "🎫 #" . $row['id'] . " - " . $row['titulo'] . " (" . $row['status'] . $priority . ") em $date";
        }
        $reply = "Aqui estão os últimos chamados abertos:\n" . implode("\n", $tickets);
    } else {
        $reply = "✅ Não há chamados abertos no momento. Tudo em dia!";
    }
}

// 6. Total Value of Assets
elseif (preg_match('/(valor|custo|preço).*total.*ativos/', $message)) {
    $sql = "SELECT SUM(valor) as total FROM ativos";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $reply = "💰 O valor total estimado de todos os ativos cadastrados é de **" . formatMoney($row['total']) . "**.";
}

// 7. General Stats (Count Assets)
elseif (preg_match('/(quantos|total|n[uú]mero).*ativos/', $message)) {
    $sql = "SELECT COUNT(*) as total FROM ativos";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $reply = "📊 Atualmente, temos um total de **" . $row['total'] . "** ativos cadastrados no sistema.";
}

// 8. General Stats (Count Tickets)
elseif (preg_match('/(chamados|tickets).*(abertos|pendentes)/', $message)) {
    $sql = "SELECT COUNT(*) as total FROM chamados WHERE status IN ('Aberto', 'Pendente', 'Em Andamento')";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $reply = "🎫 Existem **" . $row['total'] . "** chamados em aberto ou em andamento.";
}

// 9. Who is user X
elseif (preg_match('/quem (é|e) (.+)/', $message, $matches)) {
    $name = $conn->real_escape_string(trim($matches[2]));
    $name = str_replace('?', '', $name);

    $sql = "SELECT nome, sobrenome, email, funcao, centroDeCusto FROM usuarios WHERE nome LIKE '%$name%' OR sobrenome LIKE '%$name%' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $reply = "👤 Encontrei: **" . $user['nome'] . " " . $user['sobrenome'] . "**\n" .
            "💼 Função: " . $user['funcao'] . "\n" .
            "📧 Email: " . $user['email'] . "\n" .
            "🏢 Centro de Custo: " . ($user['centroDeCusto'] ?: 'Não definido');
    } else {
        $reply = "Desculpe, não encontrei nenhum usuário com o nome '$name'.";
    }
}

// 10. Help / Menu
elseif (preg_match('/(ajuda|help|menu|opções|opcoes|o que voc[eê] faz)/', $message)) {
    $reply = "🤖 Sou o assistente virtual do Asset MGT, integrado com **Google Gemini AI**!\n\n" .
        "📌 **Consultas rápidas (dados do sistema):**\n" .
        "- 'Quais são meus ativos?'\n" .
        "- 'Onde está o ativo [Tag/Hostname]?'\n" .
        "- 'Contato do fornecedor [Nome]'\n" .
        "- 'Listar chamados abertos'\n" .
        "- 'Qual o valor total dos ativos?'\n" .
        "- 'Quantos chamados temos?'\n" .
        "- 'Quem é [Nome]?'\n\n" .
        "🧠 **Perguntas inteligentes (Gemini AI):**\n" .
        "- Qualquer pergunta sobre TI, gestão de ativos, suporte...\n" .
        "- 'Sugira melhorias para o ciclo de vida dos ativos'\n" .
        "- 'Qual a melhor prática para gestão de inventário?'\n" .
        "- 'Me dê um resumo geral do sistema'";
}

// 11. Where to register asset
elseif (preg_match('/(onde|como).*(cadastrar|criar|novo).*ativo/', $message)) {
    $reply = "📝 Você pode cadastrar novos ativos no menu **Ativos** → **Cadastrar Novo**.";
}

// 12. List Categories
elseif (preg_match('/(categorias|tipos).*ativos/', $message)) {
    $sql = "SELECT DISTINCT categoria FROM ativos WHERE categoria IS NOT NULL LIMIT 10";
    $result = $conn->query($sql);
    $cats = [];
    while ($row = $result->fetch_assoc()) {
        $cats[] = $row['categoria'];
    }
    $reply = "📂 As categorias de ativos cadastradas são: " . implode(', ', $cats);
}

// 13. System summary / dashboard
elseif (preg_match('/(resumo|dashboard|visão geral|panorama|status geral)/', $message)) {
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

    $reply = "📊 **Resumo Geral do Sistema**\n\n" .
        "📦 Ativos cadastrados: **$totalAtivos** ($totalAtivosAtivos ativos)\n" .
        "💰 Valor total: **" . formatMoney($valorTotal) . "**\n" .
        "🎫 Chamados em aberto: **$chamadosAbertos**\n" .
        "👥 Usuários: **$totalUsuarios**\n" .
        "🏢 Fornecedores: **$totalFornecedores**";
}

// DEFAULT: Send to Gemini AI, fallback to local response
else {
    $geminiReply = null;

    if (!empty($GEMINI_API_KEY)) {
        $systemContext = getSystemContext($conn, $userName);
        $geminiReply = callGemini($_POST['message'], $systemContext, $GEMINI_API_KEY, $conversationHistory);
    }

    if ($geminiReply !== null) {
        $reply = $geminiReply;
    } else {
        // Fallback: provide a helpful local response instead of an error
        $r = @$conn->query("SELECT COUNT(*) as total FROM ativos");
        $totalAtivos = $r ? $r->fetch_assoc()['total'] : '?';
        $r = @$conn->query("SELECT COUNT(*) as total FROM chamados WHERE status IN ('Aberto','Pendente','Em Andamento')");
        $chamados = $r ? $r->fetch_assoc()['total'] : '?';

        $reply = "🤖 Não consegui acessar a IA no momento, mas posso ajudar com comandos locais!\n\n" .
            "📊 **Status rápido:** $totalAtivos ativos cadastrados, $chamados chamados abertos.\n\n" .
            "📌 **Comandos disponíveis:**\n" .
            "- **resumo** → Visão geral do sistema\n" .
            "- **meus ativos** → Seus ativos atribuídos\n" .
            "- **chamados abertos** → Listar chamados\n" .
            "- **onde está [tag]** → Localizar ativo\n" .
            "- **ajuda** → Ver todos os comandos";
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
        'api_key_set' => !empty($GEMINI_API_KEY)
    ]
]);

$conn->close();
