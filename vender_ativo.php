<?php
include 'conexao.php';

// Lê os dados enviados pelo JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$assetId = isset($data['assetId']) ? (int)$data['assetId'] : 0;

if ($assetId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID do ativo inválido.']);
    exit;
}

// Inicia uma transação para garantir que ambas as operações (inserir e deletar) sejam atômicas
mysqli_begin_transaction($conn);

try {
    // Consulta o ativo para transferi-lo para a tabela vendas
    $sql = "SELECT * FROM ativos WHERE id_asset = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $assetId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Insere os dados do ativo na tabela vendas
        $sqlInsert = "
            INSERT INTO vendas 
            (categoria, fabricante, modelo, tag, hostName, ip, macAddress, status, dataAtivacao, centroDeCusto, assigned_to)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmtInsert = mysqli_prepare($conn, $sqlInsert);
        mysqli_stmt_bind_param(
            $stmtInsert, 'ssssssssssi',
            $row['categoria'],
            $row['fabricante'],
            $row['modelo'],
            $row['tag'],
            $row['hostName'],
            $row['ip'],
            $row['macAdress'],
            $row['status'],
            $row['dataAtivacao'],
            $row['centroDeCusto'],
            $row['assigned_to']
        );
        mysqli_stmt_execute($stmtInsert);

        if (mysqli_stmt_affected_rows($stmtInsert) > 0) {
            // Remove o ativo da tabela ativos
            $sqlDelete = "DELETE FROM ativos WHERE id_asset = ?";
            $stmtDelete = mysqli_prepare($conn, $sqlDelete);
            mysqli_stmt_bind_param($stmtDelete, 'i', $assetId);
            mysqli_stmt_execute($stmtDelete);

            if (mysqli_stmt_affected_rows($stmtDelete) > 0) {
                // Confirma a transação
                mysqli_commit($conn);
                echo json_encode(['success' => true]);
                exit;
            } else {
                throw new Exception('Erro ao remover o ativo da tabela.');
            }
        } else {
            throw new Exception('Erro ao inserir o ativo na tabela vendas.');
        }
    } else {
        throw new Exception('Ativo não encontrado.');
    }
} catch (Exception $e) {
    // Reverte a transação em caso de erro
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>
