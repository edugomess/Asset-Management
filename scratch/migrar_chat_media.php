<?php
include 'conexao.php';

// Adicionando suporte a tipos de mensagem e URLs de arquivos
$sql1 = "ALTER TABLE chat_mensagens ADD COLUMN IF NOT EXISTS tipo ENUM('texto', 'imagem') DEFAULT 'texto' AFTER mensagem";
$sql2 = "ALTER TABLE chat_mensagens ADD COLUMN IF NOT EXISTS arquivo_url VARCHAR(255) NULL AFTER tipo";

if (mysqli_query($conn, $sql1) && mysqli_query($conn, $sql2)) {
    echo "Tabela chat_mensagens atualizada com sucesso.\n";
} else {
    echo "Erro ao atualizar tabela: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
