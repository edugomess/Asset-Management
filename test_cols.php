<?php
require 'conexao.php';
$res = $conn->query("SHOW COLUMNS FROM venda");
while($row = $res->fetch_assoc()) echo $row['Field'] . "\n";
