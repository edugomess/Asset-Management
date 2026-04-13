<?php
include 'conexao.php';

// Alterando o ENUM para incluir 'arquivo'
$sql = "ALTER TABLE chat_mensagens MODIFY COLUMN tipo ENUM('texto', 'imagem', 'arquivo') DEFAULT 'texto'";

if (mysqli_query($conn, $sql)) {
    echo "Tabela chat_mensagens atualizada para suportar arquivos genéricos.\n";
} else {
    echo "Erro ao atualizar tabela: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
