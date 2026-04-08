<?php
require 'conexao.php';
$res = $conn->query("SELECT status, count(*) as c FROM venda GROUP BY status");
while($row = $res->fetch_assoc()) {
    echo $row['status'] . ": " . $row['c'] . "\n";
}
echo "----\nLast 5 items status:\n";
$res2 = $conn->query("SELECT status, modelo FROM venda ORDER BY id_venda DESC LIMIT 5");
while($row = $res2->fetch_assoc()) {
    echo $row['status'] . ": " . $row['modelo'] . "\n";
}
