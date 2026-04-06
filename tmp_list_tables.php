<?php
include 'conexao.php';
if (!isset($conn)) {
    die("Error: \$conn not defined in conexao.php\n");
}
$res = $conn->query("SHOW TABLES");
while($row = $res->fetch_row()) {
    echo $row[0] . "\n";
}
?>
