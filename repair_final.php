<?php
require 'conexao.php';

echo "Antes:\n";
$res = $conn->query("SELECT id_venda, status FROM venda WHERE id_venda >= 64");
while($row = $res->fetch_assoc()) {
    echo "ID " . $row['id_venda'] . ": [" . $row['status'] . "]\n";
}

// Direct update using specific IDs if possible or just the criteria
$conn->query("UPDATE venda SET status = 'Leiloado' WHERE id_venda IN (64, 65)");
echo "Affected: " . $conn->affected_rows . "\n";

echo "Depois:\n";
$res = $conn->query("SELECT id_venda, status FROM venda WHERE id_venda >= 64");
while($row = $res->fetch_assoc()) {
    echo "ID " . $row['id_venda'] . ": [" . $row['status'] . "]\n";
}
?>
