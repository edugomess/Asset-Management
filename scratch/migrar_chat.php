<?php
include 'conexao.php';

$sql = "CREATE TABLE IF NOT EXISTS chat_mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    remetente_id INT NOT NULL,
    destinatario_id INT NOT NULL,
    mensagem TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    lido TINYINT(1) DEFAULT 0,
    INDEX (remetente_id),
    INDEX (destinatario_id),
    INDEX (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conn, $sql)) {
    echo "Tabela chat_mensagens criada ou já existente com sucesso.\n";
} else {
    echo "Erro ao criar tabela: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
