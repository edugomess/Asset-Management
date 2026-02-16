<?php
include 'conexao.php';

$sql = "CREATE TABLE IF NOT EXISTS chamados (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    categoria ENUM('Incidente', 'Mudança', 'Requisição') NOT NULL,
    descricao TEXT,
    status VARCHAR(50) DEFAULT 'Aberto',
    data_abertura DATETIME DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT(11) NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuarios) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabela 'chamados' criada com sucesso ou já existe.";
}
else {
    echo "Erro ao criar tabela: " . $conn->error;
}

$conn->close();
?>
