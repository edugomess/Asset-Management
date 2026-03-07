<?php
include 'conexao.php';

// Adicionar coluna 'imagem' na tabela 'venda' se não existir
$result = $conn->query("SHOW COLUMNS FROM venda LIKE 'imagem'");
if ($result->num_rows == 0) {
    if ($conn->query("ALTER TABLE venda ADD COLUMN imagem VARCHAR(255) NULL AFTER macAdress")) {
        echo "Coluna 'imagem' adicionada com sucesso na tabela 'venda'.";
    } else {
        echo "Erro ao adicionar coluna 'imagem': " . $conn->error;
    }
} else {
    echo "A coluna 'imagem' já existe na tabela 'venda'.";
}

$conn->close();
?>