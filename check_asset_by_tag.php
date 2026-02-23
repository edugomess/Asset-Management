<?php
include 'conexao.php';
$res = $conn->query("SELECT id_asset, tag, modelo, status FROM ativos WHERE tag LIKE 'TAG8765%'");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>