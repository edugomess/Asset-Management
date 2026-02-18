<?php
include 'conexao.php';
$result = $conn->query("SHOW CREATE TABLE ativos");
if ($result) {
    $row = $result->fetch_assoc();
    echo $row['Create Table'];
}
else {
    echo "Error: " . $conn->error;
}
?>
