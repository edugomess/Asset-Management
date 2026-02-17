<?php
include 'conexao.php';

$sql = "CREATE TABLE IF NOT EXISTS notificacoes (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chamado_id INT(11) UNSIGNED NOT NULL,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    tipo VARCHAR(50) DEFAULT 'SLA_Vencendo',
    destinatario VARCHAR(20),
    FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabela 'notificacoes' criada com sucesso ou já existe.";
}
else {
    echo "Erro ao criar tabela: " . $conn->error;
}

$conn->close();
?>
