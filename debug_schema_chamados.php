<?php
include 'conexao.php';
echo "<pre>\n";
echo "DESC chamados:\n";
$res = $conn->query("DESC chamados");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
echo "</pre>";
?>
