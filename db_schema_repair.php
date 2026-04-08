<?php
require 'conexao.php';

// 1. Alter table structure
echo "ALTERING TABLE VENDA STRUCTURE...\n";
$sqlAlter = "ALTER TABLE venda MODIFY COLUMN status VARCHAR(50) DEFAULT 'Doado'";
if ($conn->query($sqlAlter)) {
    echo "Sucesso: Coluna 'status' alterada para VARCHAR(50).\n";
} else {
    echo "Erro SQL ao alterar tabela: " . $conn->error . "\n";
}

// 2. Repair data
echo "REPAIRING DATA...\n";
// Tudo o que estava como vazio ou 'Ativo' nos registros antigos (id < 64) vira 'Doado'
$sqlDoado = "UPDATE venda SET status = 'Doado' WHERE (status = '' OR status IS NULL OR status = 'Ativo') AND id_venda < 64";
$conn->query($sqlDoado);
echo "Registros normalizados para 'Doado': " . $conn->affected_rows . "\n";

// Tudo o que era novo (id >= 64, provindo do leiloar_lote) vira 'Leiloado'
$sqlLeiloado = "UPDATE venda SET status = 'Leiloado' WHERE (status = '' OR status IS NULL) AND id_venda >= 64";
$conn->query($sqlLeiloado);
echo "Registros normalizados para 'Leiloado': " . $conn->affected_rows . "\n";

?>
