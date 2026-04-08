<?php
require 'conexao.php';
$sql = "UPDATE venda SET status = 'Leiloado' WHERE id_venda IN (34, 64, 65)";
if ($conn->query($sql)) {
    echo "Sucesso! Afetados: " . $conn->affected_rows . "\n";
} else {
    echo "Erro SQL: " . $conn->error . "\n";
}
// Check if rows even exist
$res = $conn->query("SELECT count(*) as c FROM venda WHERE id_venda IN (64, 65)");
$row = $res->fetch_assoc();
echo "Existencia (64, 65): " . $row['c'] . "\n";
?>
