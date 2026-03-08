<?php
include 'conexao.php';
echo "--- ROLES ---\n";
$res = $conn->query("SELECT DISTINCT nivelUsuario FROM usuarios");
while ($row = $res->fetch_assoc()) {
    echo $row['nivelUsuario'] . "\n";
}
echo "\n--- CONFIG ---\n";
$res = $conn->query("DESC configuracoes_alertas");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>