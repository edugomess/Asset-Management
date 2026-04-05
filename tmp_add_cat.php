<?php
include 'conexao.php';
$categoria = "Smartphone";
$sql = "INSERT INTO categoria (categoria) VALUES ('$categoria')";
if ($conn->query($sql)) {
    echo "Sucesso: Categoria '$categoria' adicionada.\n";
} else {
    echo "Erro: " . $conn->error . "\n";
}
$conn->close();
?>
