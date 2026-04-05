<?php
include 'conexao.php';

$queries = [
    "ALTER TABLE ativos ADD COLUMN imei VARCHAR(50) DEFAULT NULL AFTER macAdress",
    "ALTER TABLE ativos ADD COLUMN sim_card VARCHAR(50) DEFAULT NULL AFTER imei"
];

foreach ($queries as $sql) {
    if ($conn->query($sql)) {
        echo "Sucesso: $sql\n";
    } else {
        echo "Erro: " . $conn->error . "\n";
    }
}
?>
