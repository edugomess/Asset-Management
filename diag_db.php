<?php
include 'conexao.php';
$result = $conn->query("DESCRIBE usuarios");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>