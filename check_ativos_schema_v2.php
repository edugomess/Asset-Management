<?php
include 'conexao.php';
$result = $conn->query("DESCRIBE ativos");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
$conn->close();
?>
