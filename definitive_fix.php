<?php
require 'conexao.php';

// 1. Repair status in venda table
echo "REPAIRING VENDA STATUS...\n";
$sqlRepair = "UPDATE venda SET status = 'Doado' WHERE (status = '' OR status IS NULL OR status = 'Ativo') AND id_venda < 64";
$conn->query($sqlRepair);
echo "Registros antigos (Doado): " . $conn->affected_rows . "\n";

$sqlRepairAuction = "UPDATE venda SET status = 'Leiloado' WHERE (status = '' OR status IS NULL) AND id_venda >= 64";
$conn->query($sqlRepairAuction);
echo "Registros novos (Leiloado): " . $conn->affected_rows . "\n";

// 2. Calibrate doar_ativo.php
echo "CALIBRATING DOAR_ATIVO.PHP...\n";
$content = file_get_contents('doar_ativo.php');
// Remove imagem column and placeholder from query
$content = str_replace(
    'macAdress, imagem, status, dataAtivacao',
    'macAdress, status, dataAtivacao',
    $content
);
$content = str_replace(
    'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    $content
);
// Adjust bind_param
// Target string: $ativo['macAdress'], $ativo['imagem'], $status_doado, $ativo['dataAtivacao']
$content = str_replace(
    "\$ativo['imagem'],",
    "",
    $content
);
$content = str_replace(
    "'sssssssssssi i'",
    "'sssssssssssi'",
    $content
);
file_put_contents('doar_ativo.php', $content);
echo "doar_ativo.php calibrated.\n";

// 3. Final verification of leiloar_lote.php
echo "CHECKING LEILOAR_LOTE.PHP...\n";
$leiloar = file_get_contents('leiloar_lote.php');
if (strpos($leiloar, 'issssssssisss') !== false) {
    echo "leiloar_lote.php type string is correct.\n";
} else {
    echo "leiloar_lote.php type string MISMATCH. Attempting fix...\n";
    $leiloar = str_replace("'issssssssi sss'", "'issssssssisss'", $leiloar);
    file_put_contents('leiloar_lote.php', $leiloar);
}
?>
