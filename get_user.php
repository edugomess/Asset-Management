<?php
include 'conexao.php';
$res = $conn->query("SELECT email FROM usuarios LIMIT 1");
if ($row = $res->fetch_assoc()) {
    echo $row['email'];
} else {
    echo "No users found.";
}
?>