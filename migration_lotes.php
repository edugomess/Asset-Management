<?php
include 'conexao.php';

echo "Iniciando migração...\n";

// 1. Criar tabela de lotes
$sql_lotes = "CREATE TABLE IF NOT EXISTS lotes_leilao (
    id_lote INT AUTO_INCREMENT PRIMARY KEY,
    nome_lote VARCHAR(255) NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Aberto', 'Fechado', 'Leiloado') DEFAULT 'Aberto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql_lotes)) {
    echo "Tabela 'lotes_leilao' criada ou já existente.\n";
} else {
    echo "Erro ao criar tabela 'lotes_leilao': " . $conn->error . "\n";
}

// 2. Adicionar coluna id_lote na tabela ativos
$check_col = $conn->query("SHOW COLUMNS FROM ativos LIKE 'id_lote'");
if ($check_col->num_rows == 0) {
    if ($conn->query("ALTER TABLE ativos ADD COLUMN id_lote INT DEFAULT NULL")) {
        echo "Coluna 'id_lote' adicionada à tabela 'ativos'.\n";
    } else {
        echo "Erro ao adicionar coluna 'id_lote': " . $conn->error . "\n";
    }
} else {
    echo "Coluna 'id_lote' já existe na tabela 'ativos'.\n";
}

echo "Migração concluída.\n";
?>
