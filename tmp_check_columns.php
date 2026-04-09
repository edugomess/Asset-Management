<?php
include 'conexao.php';
$table = 'usuarios';
$res = mysqli_query($conn, "DESCRIBE $table");
echo "Columns in $table:\n";
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>
