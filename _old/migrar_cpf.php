<?php
include 'conexao.php';

$sql = "SHOW COLUMNS FROM usuarios LIKE 'cpf'";
$result = $conn->query($sql);

if ($result && $result->num_rows == 0) {
    $sql_alter = "ALTER TABLE usuarios ADD COLUMN cpf VARCHAR(14) AFTER email";
    if ($conn->query($sql_alter)) {
        echo "Coluna 'cpf' adicionada com sucesso!";
    } else {
        echo "Erro ao adicionar coluna: " . $conn->error;
    }
} else {
    echo "A coluna 'cpf' já existe.";
}

$conn->close();
?>