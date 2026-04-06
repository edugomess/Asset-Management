<?php
include 'conexao.php';
$res = $conn->query("SELECT DISTINCT status FROM ativos WHERE status IS NOT NULL AND status != ''");
$statuses = [];
while($row = $res->fetch_assoc()) {
    $statuses[] = $row['status'];
}
echo implode(", ", $statuses);
?>
