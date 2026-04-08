<?php
require 'conexao.php';
echo "VENDA COLUMNS:\n";
$res = $conn->query("DESCRIBE venda");
while($row = $res->fetch_assoc()) echo $row['Field'] . "\n";
echo "\nATIVOS COLUMNS:\n";
$res = $conn->query("DESCRIBE ativos");
while($row = $res->fetch_assoc()) echo $row['Field'] . "\n";
?>
