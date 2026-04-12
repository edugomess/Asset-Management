<?php
include 'conexao.php';
echo "<pre>\n";
echo "DESC usuarios:\n";
$res = $conn->query("DESC usuarios");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
echo "</pre>";
?>
