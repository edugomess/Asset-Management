<?php
include 'conexao.php';

header('Content-Type: application/json');

// Obtém o id do ativo a ser desatribuído
$data = json_decode(file_get_contents('php://input'), true);
$assetId = $data['id_asset'];

// Atualiza a tabela de ativos, removendo a atribuição do usuário
$sql = "UPDATE ativos SET assigned_to = NULL WHERE id_asset = $assetId";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

mysqli_close($conn);
?>
