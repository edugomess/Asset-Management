<?php
include 'conexao.php';
$res = $conn->query("SHOW COLUMNS FROM fornecedor LIKE 'imagem'");
if ($res->num_rows == 0) {
    if ($conn->query("ALTER TABLE fornecedor ADD COLUMN imagem VARCHAR(255) DEFAULT NULL")) {
        echo "Coluna 'imagem' adicionada com sucesso.";
    } else {
        echo "Erro ao adicionar coluna 'imagem': " . $conn->error;
    }
} else {
    echo "Coluna 'imagem' já existe.";
}
?>
