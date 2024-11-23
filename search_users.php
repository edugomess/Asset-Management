<?php
include 'conexao.php';

$query = $_GET['query'];
$sql = "SELECT id_usuarios, nome FROM usuarios WHERE nome LIKE ?";
$stmt = $conn->prepare($sql);
$search = "%$query%";
$stmt->bind_param('s', $search);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
?>
