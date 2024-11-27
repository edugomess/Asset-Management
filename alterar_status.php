<?php
include 'conexao.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_asset']) && isset($data['status'])) {
    $id_asset = $data['id_asset'];
    $newStatus = $data['status'];

    $query = "UPDATE ativos SET status = ? WHERE id_asset = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die('prepare() failed: ' . htmlspecialchars($conn->error));
    }

    $bind = $stmt->bind_param('si', $newStatus, $id_asset);
    if ($bind === false) {
        die('bind_param() failed: ' . htmlspecialchars($stmt->error));
    }

    $exec = $stmt->execute();
    if ($exec) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o status: ' . htmlspecialchars($stmt->error)]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
}
?>
