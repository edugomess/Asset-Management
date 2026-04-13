<?php
include 'conexao.php';

// Find the most recent 'Leiloado' lot
$sql_lot = "SELECT id_lote, nome_lote FROM lotes_leilao WHERE status = 'Leiloado' ORDER BY id_lote DESC LIMIT 1";
$res_lot = $conn->query($sql_lot);

if ($res_lot && $lot = $res_lot->fetch_assoc()) {
    $id_lote = $lot['id_lote'];
    $nome_lote = $lot['nome_lote'];
    echo "Found last auctioned lot: #$id_lote - $nome_lote\n";

    // Update items in 'venda' that have NULL id_lote and status 'Leiloado'
    $sql_upd = "UPDATE venda SET id_lote = $id_lote WHERE status = 'Leiloado' AND id_lote IS NULL";
    if ($conn->query($sql_upd) === TRUE) {
        $affected = $conn->affected_rows;
        echo "Backfilled $affected items with Lot #$id_lote.\n";
    } else {
        echo "Error backfilling: " . $conn->error . "\n";
    }
} else {
    echo "No 'Leiloado' lot found to backfill from.\n";
}

$conn->close();
?>
