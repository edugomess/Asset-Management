<?php
require_once 'c:\xampp\htdocs\conexao.php';

// Criar tabela para múltiplos destinatários
$sql = "CREATE TABLE IF NOT EXISTS destinatarios_alertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuarios) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Tabela destinatarios_alertas criada com sucesso.\n";
} else {
    echo "Erro ao criar tabela: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>