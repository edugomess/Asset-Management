<?php
include_once 'conexao.php';
$res = $conn->query("DESCRIBE usuarios");
while($row = $res->fetch_assoc()) echo $row['Field'] . " " . $row['Type'] . "\n";
echo "--- SAMPLE USER ---\n";
$res = $conn->query("SELECT * FROM usuarios LIMIT 1");
$user = $res->fetch_assoc();
print_r($user);
?>
