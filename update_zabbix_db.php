<?php
require 'conexao.php';

$queries = [
    "ALTER TABLE zabbix_historico_alertas ADD COLUMN severidade VARCHAR(50) AFTER recorrencia;",
    "ALTER TABLE zabbix_historico_alertas ADD COLUMN host_envolvido VARCHAR(255) AFTER severidade;",
    "ALTER TABLE zabbix_historico_alertas ADD COLUMN ultima_ocorrencia DATETIME AFTER host_envolvido;",
    "ALTER TABLE zabbix_historico_alertas ADD COLUMN comentario_usuario TEXT AFTER ultima_ocorrencia;",
    "ALTER TABLE zabbix_historico_alertas ADD COLUMN falso_positivo TINYINT(1) DEFAULT 0 AFTER comentario_usuario;"
];

foreach ($queries as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Sucesso na query: $sql\n";
    } else {
        echo "Ignorado (ou erro) na query $sql: " . $conn->error . "\n";
    }
}

$conn->close();
?>
