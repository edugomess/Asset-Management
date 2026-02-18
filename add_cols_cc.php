<?php
include 'conexao.php';

$sql1 = "ALTER TABLE centro_de_custo ADD COLUMN imagem VARCHAR(255) DEFAULT NULL";
$sql2 = "ALTER TABLE centro_de_custo ADD COLUMN descricao TEXT DEFAULT NULL";

if ($conn->query($sql1) === TRUE) {
    echo "Column 'imagem' added successfully.\n";
}
else {
    echo "Error adding column 'imagem': " . $conn->error . "\n";
}

if ($conn->query($sql2) === TRUE) {
    echo "Column 'descricao' added successfully.\n";
}
else {
    echo "Error adding column 'descricao': " . $conn->error . "\n";
}

$conn->close();
?>
