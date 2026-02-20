<?php
include 'conexao.php';
$res = $conn->query("DESCRIBE licencas");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
} else {
    echo "Error: " . $conn->error;
}
?>