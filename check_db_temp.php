<?php
include 'conexao.php';

echo "Table: usuarios\n";
$result = $conn->query("DESCRIBE usuarios");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\nUsers:\n";
$result = $conn->query("SELECT * FROM usuarios LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo "ID: " . ($row['id'] ?? $row['id_usuarios'] ?? 'N/A') . " - Email: " . $row['email'] . " - Senha Hash: " . $row['senha'] . "\n";
}
?>
