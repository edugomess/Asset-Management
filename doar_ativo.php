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

    // Consultar o ativo a ser doado
    $query = "SELECT * FROM ativos WHERE id_asset = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_asset);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $ativo = $result->fetch_assoc();

        // Verificar se já passaram 3 dias desde a ativação
        $data_ativacao = new DateTime($ativo['dataAtivacao']);
        $data_atual = new DateTime();
        $diff = $data_ativacao->diff($data_atual);

        if ($diff->days < 3) {
            echo json_encode(['success' => false, 'message' => 'Ativo não elegível para doação. Necessário aguardar 3 dias após ativação.']);
            exit;
        }

        // Transferir o ativo para a tabela "venda" (que agora representa doações)
        $queryDoacao = "INSERT INTO venda (categoria, fabricante, modelo, tag, hostName, valor, macAdress, status, dataAtivacao, centroDeCusto, descricao)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtDoacao = $conn->prepare($queryDoacao);
        $stmtDoacao->bind_param('sssssssssss',
            $ativo['categoria'], $ativo['fabricante'], $ativo['modelo'], $ativo['tag'], $ativo['hostName'], $ativo['valor'], $ativo['macAdress'], $ativo['status'], $ativo['dataAtivacao'], $ativo['centroDeCusto'], $ativo['descricao']);
        if ($stmtDoacao->execute()) {
            // Remover o ativo da tabela "ativos"
            $queryDelete = "DELETE FROM ativos WHERE id_asset = ?";
            $stmtDelete = $conn->prepare($queryDelete);
            $stmtDelete->bind_param('i', $id_asset);
            $stmtDelete->execute();

            echo json_encode(['success' => true]);
        }
        else {
            echo json_encode(['success' => false, 'message' => 'Erro ao transferir o ativo para a tabela de doações.']);
        }
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Ativo não encontrado.']);
    }

    // Reativar verificação de chave estrangeira
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");

    $stmt->close();
    $conn->close();
}
catch (Exception $e) {
    // Retornar erro
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Fechar a conexão
$conn->close();
?>
