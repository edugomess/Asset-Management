<?php
include 'conexao.php';

$sql = "ALTER TABLE ativos ADD COLUMN is_scanner VARCHAR(10) DEFAULT NULL AFTER polegadas";

if (mysqli_query($conn, $sql)) {
    echo "Coluna 'is_scanner' adicionada com sucesso!\n";
} else {
    echo "Erro ao adicionar coluna: " . mysqli_error($conn) . "\n";
}
?>
