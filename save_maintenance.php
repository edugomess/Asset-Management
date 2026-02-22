<?php
header('Content-Type: application/json');
include 'conexao.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id_asset'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$id_asset = (int) $data['id_asset'];
$acao = isset($data['acao']) ? $data['acao'] : 'iniciar';
$admin_id = isset($_SESSION['id_usuarios']) ? $_SESSION['id_usuarios'] : 'NULL';

if ($acao === 'iniciar') {
    // Verificar se o ativo está atribuído a algum usuário
    $asset_check = $conn->query("SELECT assigned_to FROM ativos WHERE id_asset = $id_asset");
    if ($asset_check && $row = $asset_check->fetch_assoc()) {
        if (!empty($row['assigned_to']) && $row['assigned_to'] != 0) {
            echo json_encode(['success' => false, 'message' => 'Não é possível enviar para manutenção: o ativo está atribuído a um usuário.']);
            exit;
        }
    }

    // Verificar se já existe manutenção aberta para este ativo
    $check = $conn->query("SELECT id_manutencao FROM manutencao WHERE id_asset = $id_asset AND status_manutencao = 'Em Manutenção'");
    if ($check && $check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Este ativo já está em manutenção']);
        exit;
    }

    $observacoes = isset($data['observacoes']) ? mysqli_real_escape_string($conn, $data['observacoes']) : '';
    $sql = "INSERT INTO manutencao (id_asset, status_manutencao, observacoes) VALUES ($id_asset, 'Em Manutenção', '$observacoes')";
    if ($conn->query($sql) === TRUE) {
        $conn->query("UPDATE ativos SET status = 'Inativo' WHERE id_asset = $id_asset");
        // Log no histórico
        $conn->query("INSERT INTO historico_ativos (ativo_id, usuario_id, acao, detalhes) VALUES ($id_asset, $admin_id, 'Manutenção', 'Ativo enviado para manutenção: $observacoes')");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar: ' . $conn->error]);
    }
} elseif ($acao === 'liberar') {
    // Finalizar a manutenção ativa para este ativo
    $sql = "UPDATE manutencao SET status_manutencao = 'Concluído', data_fim = NOW() 
            WHERE id_asset = $id_asset AND status_manutencao = 'Em Manutenção'";

    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            $conn->query("UPDATE ativos SET status = 'Ativo' WHERE id_asset = $id_asset");
            // Log no histórico
            $conn->query("INSERT INTO historico_ativos (ativo_id, usuario_id, acao, detalhes) VALUES ($id_asset, $admin_id, 'Fim de Manutenção', 'Manutenção concluída')");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Nenhuma manutenção ativa encontrada para este ativo']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao liberar: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Ação não suportada']);
}

$conn->close();
?>