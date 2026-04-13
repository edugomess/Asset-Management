<?php
include 'conexao.php';

$sql = "ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS last_seen DATETIME DEFAULT CURRENT_TIMESTAMP";

if (mysqli_query($conn, $sql)) {
    echo "Coluna last_seen adicionada com sucesso.\n";
} else {
    echo "Erro ao adicionar coluna: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
