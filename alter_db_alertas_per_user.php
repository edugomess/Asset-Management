<?php
require_once 'conexao.php';

$sql = "ALTER TABLE alertas_usuarios 
        ADD COLUMN IF NOT EXISTS prioridade_p1 TINYINT(1) DEFAULT 1,
        ADD COLUMN IF NOT EXISTS prioridade_p2 TINYINT(1) DEFAULT 1,
        ADD COLUMN IF NOT EXISTS prioridade_p3 TINYINT(1) DEFAULT 1,
        ADD COLUMN IF NOT EXISTS prioridade_p4 TINYINT(1) DEFAULT 1";

if ($conn->query($sql)) {
    echo "Tabela alertas_usuarios atualizada com sucesso!\n";
} else {
    echo "Erro ao atualizar tabela: " . $conn->error . "\n";
}
?>
