<?php
require 'conexao.php';
$res = $conn->query("SELECT * FROM venda ORDER BY id_venda DESC LIMIT 5");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
