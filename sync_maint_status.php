<?php
include 'conexao.php';

// 1. Sync assets that have active maintenance to 'Inativo'
$sql1 = "UPDATE ativos a 
         JOIN manutencao m ON a.id_asset = m.id_asset 
         SET a.status = 'Inativo' 
         WHERE m.status_manutencao = 'Em Manutenção' AND a.status != 'Inativo'";
$conn->query($sql1);
echo "Assets synced to Inativo: " . $conn->affected_rows . "\n";

// 2. Clear status to 'Ativo' if they were 'Inativo' but have no active maintenance 
// (Caution: Only if they don't have other reasons to be Inativo, but in this system it seems Inativo = Maintenance)
// Let's check how many would be affected first
$res = $conn->query("SELECT a.tag FROM ativos a 
                     LEFT JOIN manutencao m ON a.id_asset = m.id_asset AND m.status_manutencao = 'Em Manutenção'
                     WHERE a.status = 'Inativo' AND m.id_manutencao IS NULL");
echo "Assets that are Inativo but have NO active maintenance: " . $res->num_rows . "\n";
?>