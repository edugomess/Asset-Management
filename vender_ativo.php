<?php
include 'conexao.php';  // Certifique-se de que a conexão com o banco de dados esteja correta

// Lê os dados JSON enviados via POST
$data = json_decode(file_get_contents('php://input'), true);

// Verifica se os dados foram recebidos corretamente
if ($data === null) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos ou ausentes']);
    exit;
}

$id_asset = $data['id_asset']; // Acessa o id do ativo

// Verifica se o ID do ativo está presente
if (!isset($id_asset)) {
    echo json_encode(['success' => false, 'message' => 'ID do ativo não encontrado']);
    exit;
}

// Transferir o ativo para a tabela "venda" e removê-lo da tabela "ativos"
$sql = "INSERT INTO venda (id_asset, categoria, fabricante, modelo, status) 
        SELECT id_asset, categoria, fabricante, modelo, status 
        FROM ativos WHERE id_asset = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_asset);

if ($stmt->execute()) {
    // Exclui o ativo da tabela 'ativos'
    $sql_delete = "DELETE FROM ativos WHERE id_asset = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param('i', $id_asset);
    $stmt_delete->execute();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao vender o ativo.']);
}

$stmt->close();
$conn->close();
?>
