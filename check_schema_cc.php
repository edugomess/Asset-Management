<?php
include 'conexao.php';
$res = $conn->query("DESCRIBE centro_de_custo");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
} else {
    echo "Error: " . $conn->error;
}
?>