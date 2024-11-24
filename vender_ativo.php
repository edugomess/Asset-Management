<?php
// Conectar ao banco de dados
include 'conexao.php';

// Receber os dados JSON enviados pelo fetch
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_asset'])) {
    $id_asset = $data['id_asset'];

    // Consultar o ativo a ser vendido
    $query = "SELECT * FROM ativos WHERE id_asset = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_asset);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Transferir o ativo para a tabela "venda"
        $ativo = $result->fetch_assoc();
        $queryVenda = "INSERT INTO venda (categoria, fabricante, modelo, tag, hostName, ip, macAdress, status, dataAtivacao, centroDeCusto, descricao)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtVenda = $conn->prepare($queryVenda);
        $stmtVenda->bind_param('sssssssssss',
            $ativo['categoria'], $ativo['fabricante'], $ativo['modelo'], $ativo['tag'], $ativo['hostName'], $ativo['ip'], $ativo['macAdress'], $ativo['status'], $ativo['dataAtivacao'], $ativo['centroDeCusto'], $ativo['descricao']);
        if ($stmtVenda->execute()) {
            // Remover o ativo da tabela "ativos"
            $queryDelete = "DELETE FROM ativos WHERE id_asset = ?";
            $stmtDelete = $conn->prepare($queryDelete);
            $stmtDelete->bind_param('i', $id_asset);
            $stmtDelete->execute();

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao transferir o ativo para a tabela de venda.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Ativo não encontrado.']);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'ID do ativo não fornecido.']);
}
?>

