<?php
include 'conexao.php';
$res = $conn->query("DESCRIBE ativos");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
