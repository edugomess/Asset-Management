<?php
require 'conexao.php';
// Check what exactly is in status for those rows
$res = $conn->query("SELECT id_venda, HEX(status) as h FROM venda WHERE status = '' OR status IS NULL");
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id_venda'] . " | HEX: [" . $row['h'] . "]\n";
}
// Run the update
$conn->query("UPDATE venda SET status = 'Leiloado' WHERE status = '' OR status IS NULL OR status = 'Ativo' AND id_venda >= 64");
echo 'Updated rows: ' . $conn->affected_rows . "\n";
?>
