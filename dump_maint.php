<?php
include 'conexao.php';
$res = $conn->query("SELECT * FROM manutencao WHERE status_manutencao = 'Em Manutenção'");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>