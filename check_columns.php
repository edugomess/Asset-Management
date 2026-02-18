<?php
include 'conexao.php';
$result = $conn->query("SHOW COLUMNS FROM chamados");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
}
else {
    echo "Error: " . $conn->error;
}
?>
