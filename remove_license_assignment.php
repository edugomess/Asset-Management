<?php
include 'auth.php';
include 'conexao.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_atribuicao'])) {
    $id_atribuicao = intval($data['id_atribuicao']);

    // Inicia transação para garantir integridade
    mysqli_begin_transaction($conn);

    try {
        // 1. Busca o ID da licença vinculada a esta atribuição antes de deletar
        $sql_info = "SELECT id_licenca FROM atribuicoes_licencas WHERE id_atribuicao = $id_atribuicao";
        $res_info = mysqli_query($conn, $sql_info);
        $row_info = mysqli_fetch_assoc($res_info);

        if (!$row_info) {
            throw new Exception("Atribuição não encontrada.");
        }

        $id_licenca = $row_info['id_licenca'];

        // 2. Remove a atribuição
        $sql_del = "DELETE FROM atribuicoes_licencas WHERE id_atribuicao = $id_atribuicao";
        if (!mysqli_query($conn, $sql_del)) {
            throw new Exception("Erro ao remover atribuição: " . mysqli_error($conn));
        }

        // 3. Decrementa o contador de uso na tabela de licenças
        $sql_update = "UPDATE licencas SET quantidade_uso = cantidad_uso - 1 WHERE id_licenca = $id_licenca AND quantidade_uso > 0";
        // Corrigindo o nome da coluna de 'cantidad_uso' para 'quantidade_uso' (confirmado no setup)
        $sql_update = "UPDATE licencas SET quantidade_uso = quantidade_uso - 1 WHERE id_licenca = $id_licenca AND quantidade_uso > 0";
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
    echo json_encode(['success' => false, 'message' => 'ID não fornecido.']);
}

mysqli_close($conn);
?>