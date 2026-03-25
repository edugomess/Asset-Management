<?php
include 'conexao.php';
$result = $conn->query("SHOW TABLES");
while($row = $result->fetch_array()) {
    echo $row[0] . "\n";
}
$conn->close();
?>
