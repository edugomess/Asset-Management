
<?php
include 'conexao.php';

// Configurar cabeçalho para JSON
header('Content-Type: application/json');

try {
    // Receber os dados enviados via POST
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id_asset'])) {
        throw new Exception('ID do ativo não fornecido.');
    }

    $id_asset = $data['id_asset'];

    // Desativar verificação de chave estrangeira
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");

    // Consultar o ativo a ser vendido
    $query = "SELECT * FROM ativos WHERE id_asset = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_asset);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Transferir o ativo para a tabela "venda"
        $ativo = $result->fetch_assoc();
        $queryVenda = "INSERT INTO venda (categoria, fabricante, modelo, tag, hostName, valor, macAdress, status, dataAtivacao, centroDeCusto, descricao)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtVenda = $conn->prepare($queryVenda);
        $stmtVenda->bind_param('sssssssssss',
            $ativo['categoria'], $ativo['fabricante'], $ativo['modelo'], $ativo['tag'], $ativo['hostName'], $ativo['valor'], $ativo['macAdress'], $ativo['status'], $ativo['dataAtivacao'], $ativo['centroDeCusto'], $ativo['descricao']);
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

    // Reativar verificação de chave estrangeira
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Retornar erro
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Fechar a conexão
$conn->close();
?>