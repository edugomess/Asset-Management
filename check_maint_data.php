<?php
include 'conexao.php';
echo "--- Inconsistencies (Active maintenance record but Asset status is NOT 'Inativo') ---\n";
$res = $conn->query("SELECT m.id_asset, a.tag, a.status, m.status_manutencao 
                    FROM manutencao m 
                    JOIN ativos a ON m.id_asset = a.id_asset 
                    WHERE m.status_manutencao = 'Em Manutenção' AND a.status != 'Inativo'");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "--- Gaps (Asset status is 'Inativo' but no active maintenance record) ---\n";
$res2 = $conn->query("SELECT a.id_asset, a.tag, a.status 
                     FROM ativos a 
                     LEFT JOIN manutencao m ON a.id_asset = m.id_asset AND m.status_manutencao = 'Em Manutenção'
                     WHERE a.status = 'Inativo' AND m.id_manutencao IS NULL");
while ($row = $res2->fetch_assoc()) {
    print_r($row);
}
?>