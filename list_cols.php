<?php
include 'conexao.php';
$res = mysqli_query($conn, "SHOW COLUMNS FROM configuracoes_alertas");
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . "\n";
}
?>