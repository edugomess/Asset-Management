<?php
require 'conexao.php';

$res = $conn->query("SELECT id, recorrencia, ultima_ocorrencia FROM zabbix_historico_alertas");

while ($row = $res->fetch_assoc()) {
    $id = $row['id'];
    $count = $row['recorrencia'];
    $baseTime = strtotime($row['ultima_ocorrencia']);
    
    $timestamps = [];
    for ($i = 0; $i < $count; $i++) {
        // Gera horários aleatórios retroativos a partir da última ocorrência
        $offset = rand(0, 604800); // Até 7 dias atrás em segundos
        $timestamps[] = date('Y-m-d H:i:s', $baseTime - $offset);
    }
    rsort($timestamps);
    $json = $conn->real_escape_string(json_encode($timestamps));
    
    $conn->query("UPDATE zabbix_historico_alertas SET lista_ocorrencias = '$json' WHERE id = $id");
}

echo "Histórico atualizado para todos os registros!";
$conn->close();
?>
