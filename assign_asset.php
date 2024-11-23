<?php
include 'conexao.php'; // Inclui a conexão com o banco de dados

header('Content-Type: application/json');

// Obtém os dados enviados no corpo da requisição
$input = json_decode(file_get_contents('php://input'), true);

// Depuração: Exibe os dados recebidos para verificar se estão corretos
var_dump($input);

// Obtém os valores do assetId e assigned_to
$assetId = isset($input['id_asset']) ? (int)$input['id_asset'] : 0;
$userId = isset($input['assigned_to']) ? (int)$input['assigned_to'] : 0;

// Verifique os valores recebidos para garantir que são válidos
var_dump($assetId, $userId);  // Depura os valores de assetId e assigned_to

// Valida os dados recebidos
if ($assetId <= 0 || $userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

// Verifica se a conexão com o banco de dados está funcionando
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Erro na conexão com o banco de dados.']);
    exit;
}

// Atualiza o banco de dados para associar o ativo ao usuário
$sql = "UPDATE ativos SET assigned_to = ? WHERE id_asset = ?";
$stmt = mysqli_prepare($conn, $sql);

// Verificar se a consulta foi preparada corretamente
if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao preparar a consulta.',
        'error' => mysqli_error($conn) // Mostra a mensagem de erro do MySQL
    ]);
    exit;
}

// Liga os parâmetros e executa a consulta
mysqli_stmt_bind_param($stmt, 'ii', $userId, $assetId);

// Verifique se a execução foi bem-sucedida
if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    // Caso o erro persista, exibe a mensagem detalhada do erro
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar o banco de dados.',
        'error' => mysqli_error($conn) // Mostra a mensagem de erro do MySQL
    ]);
}

// Fecha a declaração e a conexão
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
