<?php
/**
 * MIGRATION: 2026_03_25_add_locais_and_hierarchy.php
 * Adds the 'locais' table and necessary columns to 'ativos' for hierarchical attribution.
 */
include 'conexao.php';

$queries = [
    // 1. Create Locais Table
    "CREATE TABLE IF NOT EXISTS locais (
        id_local INT AUTO_INCREMENT PRIMARY KEY,
        nome_local VARCHAR(255) NOT NULL,
        tipo_local ENUM('Prédio', 'Andar', 'Sala', 'Rack', 'Setor') NOT NULL,
        id_parent_local INT NULL,
        FOREIGN KEY (id_parent_local) REFERENCES locais(id_local) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // 2. Add columns to Ativos
    "ALTER TABLE ativos ADD COLUMN IF NOT EXISTS assigned_type ENUM('Usuario', 'Local') DEFAULT 'Usuario' AFTER assigned_to;",
    "ALTER TABLE ativos ADD COLUMN IF NOT EXISTS id_local INT NULL AFTER assigned_type;",
    "ALTER TABLE ativos ADD COLUMN IF NOT EXISTS parent_asset_id INT NULL AFTER id_local;",

    // 3. Add Foreign Keys for Ativos (assuming id_asset is the PK)
    "ALTER TABLE ativos ADD CONSTRAINT fk_ativos_local FOREIGN KEY (id_local) REFERENCES locais(id_local) ON DELETE SET NULL;",
    "ALTER TABLE ativos ADD CONSTRAINT fk_ativos_parent FOREIGN KEY (parent_asset_id) REFERENCES ativos(id_asset) ON DELETE SET NULL;",

    // 4. Update Status field to be more flexible (change from enum to varchar if needed, or expand enum)
    "ALTER TABLE ativos MODIFY COLUMN status VARCHAR(50) DEFAULT 'Disponível';",
];

echo "Iniciando Migração...\n";

foreach ($queries as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Sucesso: " . substr($sql, 0, 50) . "...\n";
    } else {
        echo "Erro: " . $conn->error . "\n";
    }
}

$conn->close();
echo "Migração Concluída.\n";
?>
