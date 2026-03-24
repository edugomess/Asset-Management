<?php
include 'conexao.php';
$res = $conn->query("SELECT * FROM categoria");
while($row = $res->fetch_assoc()) {
    echo $row['categoria'] . "\n";
}
?>
