<?php
include 'conexao.php';

// Check if the 'prioridade' column exists
$result = $conn->query("SHOW COLUMNS FROM chamados LIKE 'prioridade'");

if ($result->num_rows == 0) {
    // Column does not exist, so add it
    $sql = "ALTER TABLE chamados ADD COLUMN prioridade ENUM('Baixa', 'Média', 'Alta') DEFAULT 'Média' AFTER status";

    if ($conn->query($sql) === TRUE) {
        echo "Coluna 'prioridade' adicionada com sucesso tabela 'chamados'.";
    }
    else {
        echo "Erro ao adicionar coluna: " . $conn->error;
    }
}
else {
    echo "A coluna 'prioridade' já existe na tabela 'chamados'.";
}

$conn->close();
?>
