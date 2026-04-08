<?php
require 'conexao.php';
$res = $conn->query("SELECT id_venda, modelo, status FROM venda ORDER BY id_venda DESC LIMIT 10");
while($row = $res->fetch_assoc()) {
    echo $row['id_venda'] . ": " . $row['modelo'] . " | STATUS: [" . $row['status'] . "]\n";
}
?>
