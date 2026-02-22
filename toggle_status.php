<?php
include 'conexao.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['id_usuarios'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

// Get input data
$data = json_decode(file_get_contents('php://input'), true);
$id_asset = isset($data['id_asset']) ? intval($data['id_asset']) : 0;
$novo_status = isset($data['novo_status']) ? $data['novo_status'] : '';

// Validate input
if ($id_asset <= 0 || !in_array($novo_status, ['Ativo', 'Inativo', 'Manutencao'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

// Update status
$sql_update = "UPDATE ativos SET status = ? WHERE id_asset = ?";
$stmt = $conn->prepare($sql_update);
$stmt->bind_param('si', $novo_status, $id_asset);

if ($stmt->execute()) {
    // Log history
    $usuario_id = $_SESSION['id_usuarios'];
    if ($novo_status == 'Ativo') {
        $acao = 'Ativação';
    } elseif ($novo_status == 'Inativo') {
        $acao = 'Desativação';
    } else {
        $acao = 'Manutenção';
    }
    $detalhes = "Status alterado para: $novo_status";

    $sql_historico = "INSERT INTO historico_ativos (ativo_id, usuario_id, acao, detalhes) VALUES (?, ?, ?, ?)";
    $stmt_hist = $conn->prepare($sql_historico);
    $stmt_hist->bind_param('iiss', $id_asset, $usuario_id, $acao, $detalhes);
    $stmt_hist->execute();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>