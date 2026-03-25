<?php
include 'conexao.php';

$sql = "ALTER TABLE ativos ADD COLUMN polegadas VARCHAR(20) DEFAULT NULL AFTER gpu";

if (mysqli_query($conn, $sql)) {
    echo "Coluna 'polegadas' adicionada com sucesso!\n";
} else {
    echo "Erro ao adicionar coluna: " . mysqli_error($conn) . "\n";
}
?>
