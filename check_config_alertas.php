<?php
require_once 'conexao.php';
$res = $conn->query("DESCRIBE configuracoes_alertas");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
