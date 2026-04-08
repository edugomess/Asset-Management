<?php
require 'conexao.php';
$res = $conn->query("SHOW COLUMNS FROM venda");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Default'] . "\n";
}
?>
