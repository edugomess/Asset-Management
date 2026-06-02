<?php
include 'conexao.php';
$res = mysqli_query($conn, "DESCRIBE chamados");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
