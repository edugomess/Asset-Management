<?php
include 'conexao.php';

$sql = "ALTER TABLE licencas ADD COLUMN id_centro_custo INT NULL AFTER valor_unitario";

if (mysqli_query($conn, $sql)) {
    echo "Coluna id_centro_custo adicionada com sucesso!";
} else {
    echo "Erro ao adicionar coluna: " . mysqli_error($conn);
}

mysqli_close($conn);
?>