<?php
include 'conexao.php';
$sql = "ALTER TABLE venda ADD COLUMN id_lote INT NULL AFTER id_asset";
if ($conn->query($sql) === TRUE) {
    echo "Coluna id_lote adicionada com sucesso à tabela venda.\n";
} else {
    echo "Erro ao adicionar coluna: " . $conn->error . "\n";
}
$conn->close();
?>
