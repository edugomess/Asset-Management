<?php
include 'conexao.php';

// Adicionar colunas para controle de SLA se não existirem
$queries = [
    "ALTER TABLE chamados ADD COLUMN IF NOT EXISTS tempo_congelado_minutos INT DEFAULT 0",
    "ALTER TABLE chamados ADD COLUMN IF NOT EXISTS data_ultimo_congelamento DATETIME NULL",
    "ALTER TABLE chamados ADD COLUMN IF NOT EXISTS responsavel_id INT(11) NULL",
    "ALTER TABLE chamados ADD COLUMN IF NOT EXISTS prioridade VARCHAR(50) DEFAULT 'Média'",
    "ALTER TABLE chamados ADD COLUMN IF NOT EXISTS nota_resolucao TEXT NULL",
    "ALTER TABLE chamados ADD COLUMN IF NOT EXISTS data_fechamento DATETIME NULL",
    "ALTER TABLE chamados ADD COLUMN IF NOT EXISTS anexo VARCHAR(255) NULL"
];

foreach ($queries as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Sucesso: $sql\n";
    } else {
        echo "Nota/Erro: " . $conn->error . " (pode ser que a coluna já exista)\n";
    }
}

$conn->close();
?>