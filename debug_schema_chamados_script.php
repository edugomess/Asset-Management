<?php
include_once 'conexao.php';
$res = mysqli_query($conn, "DESCRIBE chamados");
$cols = [];
while ($row = mysqli_fetch_assoc($res)) {
    $cols[] = $row['Field'] . " (" . $row['Type'] . ")";
}
file_put_contents('debug_schema_chamados.txt', implode("\n", $cols));
echo "Schema written to debug_schema_chamados.txt";
?>
