<?php
include 'conexao.php';
echo "DESC chamados:\n";
$res = $conn->query("DESC chamados");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\nSample users:\n";
$res = $conn->query("SELECT id_usuarios, nome, sobrenome FROM usuarios LIMIT 5");
while($row = $res->fetch_assoc()) {
    echo $row['id_usuarios'] . " - " . $row['nome'] . " " . $row['sobrenome'] . "\n";
}
?>
