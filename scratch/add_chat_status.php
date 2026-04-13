<?php
include 'conexao.php';

$sql = "ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS chat_status ENUM('Disponível', 'Ausente', 'Ocupado', 'Offline') DEFAULT 'Disponível'";

if (mysqli_query($conn, $sql)) {
    echo "Coluna chat_status adicionada ou já existente com sucesso.\n";
} else {
    echo "Erro ao alterar tabela: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
