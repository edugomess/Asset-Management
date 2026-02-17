<?php
include 'conexao.php';
$sql = "SELECT DISTINCT status FROM ativos";
$result = $conn->query($sql);
echo "Statuses in DB:<br>";
while ($row = $result->fetch_assoc()) {
    echo "'" . $row['status'] . "'<br>";
}
echo "<br>Checking 'Disponivel':<br>";
$sql2 = "SELECT count(*) as c FROM ativos WHERE status = 'Disponivel'";
$res2 = $conn->query($sql2);
echo "Count 'Disponivel': " . $res2->fetch_assoc()['c'] . "<br>";

echo "<br>Checking 'Available' logic (Status=Ativo AND assigned_to IS NULL/0):<br>";
$sql4 = "SELECT count(*) as c FROM ativos WHERE status = 'Ativo' AND (assigned_to IS NULL OR assigned_to = 0)";
$res4 = $conn->query($sql4);
echo "Count 'Available': " . $res4->fetch_assoc()['c'] . "<br>";
?>
