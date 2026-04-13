<?php
include 'conexao.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['lote']) || empty($data['lote'])) {
        throw new Exception(__('Nenhum ativo selecionado para leilão.'));
    }

    $ids = $data['lote'];

    // Sanitizar e preparar array de IDs inteiros
    $int_ids = array_map('intval', $ids);
    $ids_str = implode(',', $int_ids);

    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");
    mysqli_begin_transaction($conn);

    // Selecionar os ativos para repassar à tabela de vendas
    $query = "SELECT * FROM ativos WHERE id_asset IN ($ids_str)";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $queryVenda = "INSERT INTO venda (id_asset, id_lote, categoria, fabricante, modelo, tag, hostName, valor, macAdress, status, assigned_to, centroDeCusto, dataAtivacao, descricao, data_venda)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmtVenda = $conn->prepare($queryVenda);
        $status_leilao = 'Leiloado';

        while ($ativo = mysqli_fetch_assoc($result)) {
            $stmtVenda->bind_param(
                'iissssssssisss',
                $ativo['id_asset'],
                $ativo['id_lote'],
                $ativo['categoria'],
                $ativo['fabricante'],
                $ativo['modelo'],
                $ativo['tag'],
                $ativo['hostName'],
                $ativo['valor'],
                $ativo['macAdress'],
                $status_leilao,
                $ativo['assigned_to'],
                $ativo['centroDeCusto'],
                $ativo['dataAtivacao'],
                $ativo['descricao']
            );
            
            if (!$stmtVenda->execute()) {
                throw new Exception(__('Erro ao transferir equipamentos para a tabela de histórico: ') . $stmtVenda->error);
            }
        }

        // Remover os ativos
        $queryDelete = "DELETE FROM ativos WHERE id_asset IN ($ids_str)";
        if (!mysqli_query($conn, $queryDelete)) {
            throw new Exception(__('Erro ao deletar ativos do inventário atual.'));
        }

        mysqli_commit($conn);
        echo json_encode(['success' => true]);

    } else {
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => __('Nenhum ativo localizado no banco.')]);
    }

} catch (Exception $e) {
    if (isset($conn)) mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

if (isset($conn)) {
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");
    mysqli_close($conn);
}
?>
