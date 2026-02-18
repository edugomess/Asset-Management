<?php
include 'conexao.php';
session_start();

header('Content-Type: application/json');

// Obtém o id do ativo a ser desatribuído
$data = json_decode(file_get_contents('php://input'), true);
$assetId = $data['id_asset'];

// Buscar quem estava com o ativo antes da devolução
$sql_check = "SELECT u.nome, u.sobrenome FROM ativos a JOIN usuarios u ON a.assigned_to = u.id_usuarios WHERE a.id_asset = $assetId";
$result_check = mysqli_query($conn, $sql_check);
$nome_usuario_anterior = "Estoque";

if ($result_check && mysqli_num_rows($result_check) > 0) {
    $row_check = mysqli_fetch_assoc($result_check);
    $nome_usuario_anterior = $row_check['nome'] . ' ' . $row_check['sobrenome'];
}

// Atualiza a tabela de ativos, removendo a atribuição do usuário
$sql = "UPDATE ativos SET assigned_to = NULL WHERE id_asset = $assetId";

if (mysqli_query($conn, $sql)) {
    $usuario_id = isset($_SESSION['id_usuarios']) ? $_SESSION['id_usuarios'] : 'NULL';
    $acao = 'Devolução';
    $detalhes = "Ativo devolvido de: $nome_usuario_anterior";

    $sql_historico = "INSERT INTO historico_ativos (ativo_id, usuario_id, acao, detalhes) VALUES ('$assetId', $usuario_id, '$acao', '$detalhes')";
    mysqli_query($conn, $sql_historico);

    echo json_encode(['success' => true]);
}
else {
    echo json_encode(['success' => false]);
}

mysqli_close($conn);
?>
