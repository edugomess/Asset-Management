<?php
include 'conexao.php'; // Inclui a conexão com o banco de dados

header('Content-Type: application/json');

// Obtém os dados enviados no corpo da requisição
$input = json_decode(file_get_contents('php://input'), true);
$assetId = isset($input['id_asset']) ? (int)$input['id_asset'] : 0;
$userId = isset($input['id_usuarios']) ? (int)$input['id_usuarios'] : 0;

// Valida os dados recebidos
if ($assetId <= 0 || $userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

// Atualiza o banco de dados para associar o ativo ao usuário
$sql = "UPDATE ativos SET id_usuarios = $userId WHERE id_asset = $assetId";
if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o banco de dados.']);
}

// Fecha a conexão com o banco de dados
mysqli_close($conn);
?>
