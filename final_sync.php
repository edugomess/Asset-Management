<?php
include 'conexao.php';
$res = $conn->query("SELECT id_asset FROM manutencao WHERE status_manutencao = 'Em Manutenção'");
$count = 0;
while ($row = $res->fetch_assoc()) {
    $id = $row['id_asset'];
    $conn->query("UPDATE ativos SET status = 'Inativo' WHERE id_asset = $id");
    $count += $conn->affected_rows;
}
echo "Total updated: $count\n";
?>