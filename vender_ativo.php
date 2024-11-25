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

    // Buscar os dados do ativo na tabela `ativos`
    $sql_select = "SELECT * FROM ativos WHERE id_asset = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param('i', $id_asset);
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Ativo não encontrado.');
    }

    $asset = $result->fetch_assoc();

    // Inserir os dados do ativo na tabela `venda`
    $sql_insert = "INSERT INTO venda ( categoria, fabricante, modelo, tag, hostName, ip, macAdress, status, assigned_to, centroDeCusto, data_venda) 
                   VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param(
        'isssssssis',
        
        $asset['categoria'],
        $asset['fabricante'],
        $asset['modelo'],
        $asset['tag'],
        $asset['hostName'],
        $asset['ip'],
        $asset['macAdress'],
        $asset['status'],
        $asset['assigned_to'],
        $asset['centroDeCusto']
    );
    $stmt_insert->execute();

    if ($stmt_insert->affected_rows === 0) {
        throw new Exception('Erro ao inserir ativo na tabela de vendas.');
    }

    // Só depois de inserir, remova da tabela `ativos`
    $sql_delete = "DELETE FROM ativos WHERE id_asset = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param('i', $id_asset);
    $stmt_delete->execute();

    if ($stmt_delete->affected_rows === 0) {
        throw new Exception('Erro ao remover ativo da tabela de ativos.');
    }

    // Retornar sucesso
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Retornar erro
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Fechar a conexão
$conn->close();


?>
