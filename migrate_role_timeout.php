<?php
include 'conexao.php';

// SQL to add specific timeout columns if they don't exist
$sql = "ALTER TABLE configuracoes_alertas 
        ADD COLUMN IF NOT EXISTS idle_timeout_admin INT DEFAULT 10,
        ADD COLUMN IF NOT EXISTS idle_timeout_suporte INT DEFAULT 10";

if ($conn->query($sql)) {
    echo "SUCCESS: Database migrated correctly.\n";
} else {
    echo "ERROR: " . $conn->error . "\n";
}

$conn->close();
?>