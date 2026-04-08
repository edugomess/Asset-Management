<?php
require 'conexao.php';
// Repair by ID range
$res = $conn->query("UPDATE venda SET status = 'Leiloado' WHERE id_venda >= 64");
echo 'Updated: ' . $conn->affected_rows . "\n";
?>
