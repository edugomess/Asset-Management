<?php
include 'conexao.php';
$result = $conn->query("DESCRIBE ativos");
if (!$result) {
    echo "Error: " . $conn->error . "\n";
} else {
    while($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
}
$conn->close();
?>
