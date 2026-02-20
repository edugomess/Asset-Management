<?php
include 'auth.php';
include 'conexao.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_licenca']) && isset($data['id_usuario'])) {
    $id_licenca = intval($data['id_licenca']);
    $id_usuario = intval($data['id_usuario']);

    // Inicia transação
    mysqli_begin_transaction($conn);

    try {
        // 1. Verifica se há seats disponíveis
        $sql_check = "SELECT quantidade_total, quantidade_uso FROM licencas WHERE id_licenca = $id_licenca";
        $res_check = mysqli_query($conn, $sql_check);
        $row_check = mysqli_fetch_assoc($res_check);

        if ($row_check['quantidade_uso'] >= $row_check['quantidade_total']) {
            throw new Exception("Limite de ativações atingido para esta licença.");
        }

        // 2. Insere a atribuição
        $sql_attr = "INSERT INTO atribuicoes_licencas (id_licenca, id_usuario) VALUES ($id_licenca, $id_usuario)";
        if (!mysqli_query($conn, $sql_attr)) {
            throw new Exception("Erro ao registrar atribuição: " . mysqli_error($conn));
        }

        // 3. Incrementa o contador de uso
        $sql_update = "UPDATE licencas SET quantidade_uso = quantidade_uso + 1 WHERE id_licenca = $id_licenca";
        if (!mysqli_query($conn, $sql_update)) {
            throw new Exception("Erro ao atualizar contador de uso.");
        }

        mysqli_commit($conn);
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
}

mysqli_close($conn);
?>