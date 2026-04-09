<?php
include_once 'conexao.php';
$res = mysqli_query($conn, "DESCRIBE usuarios");
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
