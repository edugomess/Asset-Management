<?php
$conn = new mysqli('localhost', 'root', '', 'db_asset_mgt');
$sql = "ALTER TABLE configuracoes_depreciacao 
        ADD COLUMN destinacao_tier1 VARCHAR(20) DEFAULT 'Doação',
        ADD COLUMN destinacao_tier2 VARCHAR(20) DEFAULT 'Doação',
        ADD COLUMN destinacao_tier3 VARCHAR(20) DEFAULT 'Doação',
        ADD COLUMN destinacao_tier4 VARCHAR(20) DEFAULT 'Doação',
        ADD COLUMN destinacao_infraestrutura VARCHAR(20) DEFAULT 'Doação',
        ADD COLUMN elegivel_leilao TINYINT(1) DEFAULT 0";
if ($conn->query($sql) === TRUE) {
    echo 'Colunas adicionadas com sucesso!';
} else {
    echo 'Erro: ' . $conn->error;
}
?>
