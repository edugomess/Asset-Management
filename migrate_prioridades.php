<?php
require_once 'conexao.php';

$sql = "ALTER TABLE destinatarios_alertas 
        ADD COLUMN prioridade_alta TINYINT(1) DEFAULT 1,
        ADD COLUMN prioridade_media TINYINT(1) DEFAULT 1,
        ADD COLUMN prioridade_baixa TINYINT(1) DEFAULT 1";

if (mysqli_query($conn, $sql)) {
    echo "Tabela destinatarios_alertas atualizada com sucesso.\n";
} else {
    echo "Erro ao atualizar tabela: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>