<?php
include 'conexao.php';

echo "TABLES:\n";
$res = $conn->query("SHOW TABLES");
while($row = $res->fetch_array()) {
    echo $row[0] . "\n";
}

echo "\nDESC ativos:\n";
$res = $conn->query("DESC ativos");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\nDESC venda:\n";
$res = $conn->query("DESC venda");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
