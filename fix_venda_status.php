<?php
require 'conexao.php';
$res = $conn->query("UPDATE venda SET status = 'Leiloado' WHERE status = '' OR status IS NULL");
echo 'Fixed rows: ' . $conn->affected_rows . "\n";
?>
