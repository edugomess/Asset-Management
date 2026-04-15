<?php
require 'conexao.php';

echo "USERS IN DATABASE:\n";
$res = $conn->query("SELECT id_usuarios, nome, sobrenome, email, status FROM usuarios");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo "[ID: {$row['id_usuarios']}] {$row['nome']} {$row['sobrenome']} - {$row['email']} (Status: {$row['status']})\n";
    }
} else {
    echo "Error fetching users: " . $conn->error . "\n";
}

echo "\nALERT RECIPIENTS:\n";
$res = $conn->query("SELECT usuario_id FROM alertas_usuarios");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo "User ID: {$row['usuario_id']} is already a recipient\n";
    }
} else {
    echo "Error fetching recipients: " . $conn->error . "\n";
}
?>
