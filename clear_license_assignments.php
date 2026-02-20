<?php
include 'auth.php';
include 'conexao.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_licenca'])) {
    $id_licenca = intval($data['id_licenca']);

    mysqli_begin_transaction($conn);

    try {
        // 1. Remove todas as atribuições da licença
        $sql_del = "DELETE FROM atribuicoes_licencas WHERE id_licenca = $id_licenca";
        if (!mysqli_query($conn, $sql_del)) {
            throw new Exception("Erro ao remover atribuições: " . mysqli_error($conn));
        }

        // 2. Reseta o contador de uso na tabela de licenças
        $sql_reset = "UPDATE licencas SET quantidade_uso = 0 WHERE id_licenca = $id_licenca";
        if (!mysqli_query($conn, $sql_reset)) {
            throw new Exception("Erro ao resetar contador de uso.");
        }

        mysqli_commit($conn);
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID da licença não fornecido.']);
}

mysqli_close($conn);
?>