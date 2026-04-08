<?php
require 'conexao.php';
$res = $conn->query("SELECT id_venda, status, modelo, tag FROM venda ORDER BY id_venda DESC LIMIT 50");
echo "ID | STATUS | MODELO | TAG\n";
echo "---------------------------\n";
while($row = $res->fetch_assoc()) {
    echo $row['id_venda'] . " | '" . $row['status'] . "' | " . $row['modelo'] . " | " . $row['tag'] . "\n";
}
?>
