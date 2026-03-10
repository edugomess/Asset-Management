<?php
include 'conexao.php';
$res = mysqli_query($conn, "SELECT * FROM configuracoes_alertas WHERE id = 1");
$row = mysqli_fetch_assoc($res);
print_r($row);
?>