<?php
session_start();
include 'conexao.php';
header('Content-Type: application/json');

// Check connection
if ($conn->connect_error) {
    echo json_encode(['reply' => 'Erro de conexão com o banco de dados.']);
    exit;
}

$message = isset($_POST['message']) ? mb_strtolower(trim($_POST['message'])) : '';

if (empty($message)) {
    echo json_encode(['reply' => 'Por favor, digite algo.']);
    exit;
}

$reply = "";
$userId = isset($_SESSION['id_usuarios']) ? $_SESSION['id_usuarios'] : 0;

// Helper function to format money
function formatMoney($val)
{
    return 'R$ ' . number_format($val, 2, ',', '.');
}

// 1. Greet
if (preg_match('/(oi|ola|olá|bom dia|boa tarde|boa noite)/', $message)) {
    $nome = isset($_SESSION['nome_usuario']) ? $_SESSION['nome_usuario'] : 'Usuário';
    $reply = "Olá, $nome! Como posso ajudar você hoje?";
}

// 2. My Assets (Meus ativos)
elseif (preg_match('/(meus ativos|comigo|minha posse)/', $message)) {
    if ($userId == 0) {
        $reply = "Não consegui identificar seu usuário para buscar seus ativos.";
    }
    else {
        $sql = "SELECT modelo, tag, hostName FROM ativos WHERE assigned_to = '$userId'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row['modelo'] . " (Tag: " . $row['tag'] . ")";
            }
            $reply = "Você tem os seguintes ativos em sua posse:\n- " . implode("\n- ", $items);
        }
        else {
            $reply = "Você não possui nenhum ativo vinculado ao seu usuário no momento.";
        }
    }
}

// 3. Search Asset by Tag or Hostname
elseif (preg_match('/(buscar|procurar|onde est[áa]|localizar).*ativo (.+)/', $message, $matches)) {
    $query = $conn->real_escape_string(trim($matches[2]));
    // Remove ? if present
    $query = str_replace('?', '', $query);

    $sql = "SELECT a.modelo, a.tag, a.hostName, a.status, u.nome as usuario_nome 
            FROM ativos a 
            LEFT JOIN usuarios u ON a.assigned_to = u.id_usuarios 
            WHERE a.tag LIKE '%$query%' OR a.hostName LIKE '%$query%' LIMIT 1";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $asset = $result->fetch_assoc();
        $assigned = $asset['usuario_nome'] ? "está com **" . $asset['usuario_nome'] . "**" : "não está atribuído a ninguém";
        $reply = "Encontrei o ativo **" . $asset['modelo'] . "** (Tag: " . $asset['tag'] . ").\n" .
            "Status: " . $asset['status'] . ".\n" .
            "Atualmente $assigned.";
    }
    else {
        $reply = "Não encontrei nenhum ativo com a Tag ou Hostname contendo '$query'.";
    }
}

// 4. Supplier Info
elseif (preg_match('/(contato|telefone|email|dados|quem [ée]).*fornecedor (.+)/', $message, $matches)) {
    $query = $conn->real_escape_string(trim($matches[2]));
    $query = str_replace('?', '', $query);

    $sql = "SELECT nomeEmpresa, telefone, email, servico FROM fornecedores WHERE nomeEmpresa LIKE '%$query%' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $f = $result->fetch_assoc();
        $reply = "Fornecedor: **" . $f['nomeEmpresa'] . "**\n" .
            "Serviço: " . $f['servico'] . "\n" .
            "Tel: " . $f['telefone'] . "\n" .
            "Email: " . $f['email'];
    }
    else {
        $reply = "Não encontrei informações sobre o fornecedor '$query'.";
    }
}

// 5. List Open Tickets (Detailed)
elseif (preg_match('/(listar|quais|mostrar).*chamados.*(abertos|pendentes)/', $message)) {
    $sql = "SELECT id, titulo, status, data_abertura FROM chamados WHERE status != 'Fechado' AND status != 'Resolvido' ORDER BY data_abertura DESC LIMIT 5";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $tickets = [];
        while ($row = $result->fetch_assoc()) {
            $date = date('d/m/Y', strtotime($row['data_abertura']));
            $tickets[] = "#" . $row['id'] . " - " . $row['titulo'] . " (" . $row['status'] . ") em $date";
        }
        $reply = "Aqui estão os últimos chamados abertos:\n" . implode("\n", $tickets);
    }
    else {
        $reply = "Não há chamados abertos no momento.";
    }
}

// 6. Total Value of Assets
elseif (preg_match('/(valor|custo|preço).*total.*ativos/', $message)) {
    $sql = "SELECT SUM(valor) as total FROM ativos";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $reply = "O valor total estimado de todos os ativos cadastrados é de **" . formatMoney($row['total']) . "**.";
}

// 7. General Stats (Count Assets)
elseif (preg_match('/(quantos|total|n[uú]mero).*ativos/', $message)) {
    $sql = "SELECT COUNT(*) as total FROM ativos";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $reply = "Atualmente, temos um total de **" . $row['total'] . "** ativos cadastrados no sistema.";
}

// 8. General Stats (Count Tickets)
elseif (preg_match('/(chamados|tickets).*(abertos|pendentes)/', $message)) {
    $sql = "SELECT COUNT(*) as total FROM chamados WHERE status IN ('Aberto', 'Pendente', 'Em Andamento')";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $reply = "Existem **" . $row['total'] . "** chamados em aberto ou em andamento.";
}

// 9. Who is user X
elseif (preg_match('/quem (é|e) (.+)/', $message, $matches)) {
    $name = $conn->real_escape_string(trim($matches[2]));
    $name = str_replace('?', '', $name);

    $sql = "SELECT nome, sobrenome, email, funcao FROM usuarios WHERE nome LIKE '%$name%' OR sobrenome LIKE '%$name%' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $reply = "Encontrei: **" . $user['nome'] . " " . $user['sobrenome'] . "**.\nFunção: " . $user['funcao'] . "\nEmail: " . $user['email'];
    }
    else {
        $reply = "Desculpe, não encontrei nenhum usuário com o nome '$name'.";
    }
}

// 10. Help / Menu
elseif (preg_match('/(ajuda|help|menu|opções|opcoes|o que você faz)/', $message)) {
    $reply = "Estou ficando mais inteligente! Tente perguntar:\n" .
        "- 'Quais são meus ativos?'\n" .
        "- 'Onde está o ativo [Tag/Hostname]?'\n" .
        "- 'Contato do fornecedor [Nome]'\n" .
        "- 'Listar chamados abertos'\n" .
        "- 'Qual o valor total dos ativos?'\n" .
        "- 'Quantos chamados temos?'";
}

// 11. Where to register asset
elseif (preg_match('/(onde|como).*(cadastrar|criar|novo).*ativo/', $message)) {
    $reply = "Você pode cadastrar novos ativos no menu 'Ativos' -> 'Novo Ativo'.";
}

// 12. List Categories
elseif (preg_match('/(categorias|tipos).*ativos/', $message)) {
    $sql = "SELECT DISTINCT categoria FROM ativos WHERE categoria IS NOT NULL LIMIT 10";
    $result = $conn->query($sql);
    $cats = [];
    while ($row = $result->fetch_assoc()) {
        $cats[] = $row['categoria'];
    }
    $reply = "As categorias de ativos cadastradas são: " . implode(', ', $cats);
}

// Default response
else {
    $reply = "Ainda estou aprendendo. Tente perguntar sobre 'meus ativos', 'fornecedores' ou digite 'ajuda' para ver mais opções.";
}

echo json_encode(['reply' => $reply]);

$conn->close();
?>
