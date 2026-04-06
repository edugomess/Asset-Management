<?php
include 'conexao.php';
$sql = "ALTER TABLE configuracoes_alertas ADD COLUMN IF NOT EXISTS dashboard_cards TEXT";
if ($conn->query($sql)) {
    echo "Column added successfully";
} else {
    echo "Error: " . $conn->error;
}
?>
