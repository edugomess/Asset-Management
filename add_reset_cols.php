<?php
include 'conexao.php';

$sql = "ALTER TABLE usuarios 
        ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL, 
        ADD COLUMN reset_token_expira DATETIME DEFAULT NULL";

if ($conn->query($sql) === TRUE) {
    echo "Colunas reset_token e reset_token_expira adicionadas com sucesso.";
} else {
    echo "Erro ao adicionar colunas: " . $conn->error;
}

$conn->close();
?>
