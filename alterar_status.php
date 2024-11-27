<?php
include 'conexao.php';

// Configura o cabeçalho da resposta como JSON
header('Content-Type: application/json');

try {
    // Recebe os dados enviados via POST
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id_asset'], $data['status'])) {
        throw new Exception('Dados incompletos.');
    }

    $id_asset = intval($data['id_asset']);
    $status = $data['status'];

    // Atualiza o status no banco de dados
    $sql = "UPDATE ativos SET status = ? WHERE id_asset = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $id_asset);

    if (!$stmt->execute()) {
        throw new Exception('Erro ao atualizar o status: ' . $stmt->error);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
