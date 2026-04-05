<?php
include 'conexao.php';
$res = $conn->query("DESCRIBE categoria");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
