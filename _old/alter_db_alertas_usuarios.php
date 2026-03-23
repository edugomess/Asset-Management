<?php
require 'c:/xampp/htdocs/conexao.php';

$sql = "CREATE TABLE IF NOT EXISTS alertas_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    recebe_chamados TINYINT(1) DEFAULT 0,
    recebe_manutencao TINYINT(1) DEFAULT 0,
    prioridade_baixa TINYINT(1) DEFAULT 0,
    prioridade_media TINYINT(1) DEFAULT 0,
    prioridade_alta TINYINT(1) DEFAULT 0,
    tipo_incidente TINYINT(1) DEFAULT 0,
    tipo_requisicao TINYINT(1) DEFAULT 0,
    tipo_mudanca TINYINT(1) DEFAULT 0,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuarios) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql)) {
    echo "Table alertas_usuarios created successfully";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}
?>