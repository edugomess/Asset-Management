<?php
include 'conexao.php';
$result = $conn->query("DESCRIBE centro_de_custo");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
}
$conn->close();
?>
