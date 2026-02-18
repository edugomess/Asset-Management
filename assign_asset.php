<?php
include 'conexao.php'; // Inclui a conexão com o banco de dados
session_start();

header('Content-Type: application/json');

// Obtém os dados enviados no corpo da requisição
$input = json_decode(file_get_contents('php://input'), true);

// Obtém os valores do assetId e assigned_to
$assetId = isset($input['id_asset']) ? (int)$input['id_asset'] : 0;
$userId = isset($input['assigned_to']) ? (int)$input['assigned_to'] : 0;

// Valida os dados recebidos
if ($assetId <= 0 || $userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

// Verifica e busca informações do usuário para o histórico
$query_user = "SELECT nome, sobrenome FROM usuarios WHERE id_usuarios = ?";
$stmt_user = mysqli_prepare($conn, $query_user);
mysqli_stmt_bind_param($stmt_user, 'i', $userId);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$userReference = "ID $userId";

if ($row = mysqli_fetch_assoc($result_user)) {
    $userReference = $row['nome'] . ' ' . $row['sobrenome'];
}
mysqli_stmt_close($stmt_user);

// Atualiza o banco de dados para associar o ativo ao usuário
$sql = "UPDATE ativos SET assigned_to = ? WHERE id_asset = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erro na preparação da query.']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'ii', $userId, $assetId);

if (mysqli_stmt_execute($stmt)) {
    // Registrar histórico
    $admin_id = isset($_SESSION['id_usuarios']) ? $_SESSION['id_usuarios'] : NULL;
    $acao = 'Atribuição';
    $detalhes = "Ativo atribuído ao usuário: $userReference";

    $sql_hist = "INSERT INTO historico_ativos (ativo_id, usuario_id, acao, detalhes) VALUES (?, ?, ?, ?)";
    $stmt_hist = mysqli_prepare($conn, $sql_hist);
    mysqli_stmt_bind_param($stmt_hist, 'iiss', $assetId, $admin_id, $acao, $detalhes);
    mysqli_stmt_execute($stmt_hist);
    mysqli_stmt_close($stmt_hist);

    echo json_encode(['success' => true]);
}
else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar: ' . mysqli_error($conn)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
