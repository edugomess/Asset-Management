<?php
include 'conexao.php';

$data = json_decode(file_get_contents('php://input'), true);
$assetId = $data['id_asset'];
$userId = $data['id_usuarios'];

$sql = "UPDATE ativos SET assigned_to = ? WHERE id_asset = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $id_usuarios, $id_asset);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>
