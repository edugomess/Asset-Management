<?php
include 'conexao.php';
$result = $conn->query("DESCRIBE usuarios");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
}
$conn->close();
?>
