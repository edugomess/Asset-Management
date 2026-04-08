<?php
require 'conexao.php';

// NT7000 (ID 64 em venda) sumiu de ativos. Vamos restaurar para o inventário.
$res = $conn->query("SELECT * FROM venda WHERE id_venda = 64");
if ($row = $res->fetch_assoc()) {
    $sql = "INSERT INTO ativos (categoria, fabricante, modelo, tag, hostName, valor, macAdress, status, dataAtivacao, centroDeCusto, descricao, assigned_to) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Disponível', ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssdssssi', 
        $row['categoria'], $row['fabricante'], $row['modelo'], $row['tag'], 
        $row['hostName'], $row['valor'], $row['macAdress'], 
        $row['dataAtivacao'], $row['centroDeCusto'], $row['descricao'], $row['assigned_to']
    );
    if ($stmt->execute()) {
        $conn->query("DELETE FROM venda WHERE id_venda = 64");
        echo "Sucesso: NT7000 restaurado ao inventário para re-processamento.\n";
    }
}
?>
