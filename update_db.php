<?php
$c = new mysqli('localhost', 'root', '', 'db_asset_mgt');
if ($c->query("ALTER TABLE ativos ADD COLUMN tier VARCHAR(20) DEFAULT NULL AFTER setor")) {
    echo "Coluna 'tier' adicionada com sucesso." . PHP_EOL;
} else {
    echo "Erro ao adicionar coluna: " . $c->error . PHP_EOL;
}
