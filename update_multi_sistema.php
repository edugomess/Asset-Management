<?php
require 'conexao.php';

$sql = "ALTER TABLE zabbix_empresas ADD COLUMN sistema_tipo VARCHAR(50) DEFAULT 'zabbix' AFTER nome;";
if ($conn->query($sql) === TRUE) {
    echo "Coluna sistema_tipo adicionada com sucesso!\n";
} else {
    echo "Erro ou coluna já existe: " . $conn->error . "\n";
}

$conn->close();
?>
