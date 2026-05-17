<?php
require 'conexao.php';

// Buscar IDs das empresas
$resGrafana = $conn->query("SELECT id FROM zabbix_empresas WHERE sistema_tipo = 'grafana' LIMIT 1");
$idGrafana = $resGrafana->fetch_assoc()['id'];

$resDyna = $conn->query("SELECT id FROM zabbix_empresas WHERE sistema_tipo = 'dynatrace' LIMIT 1");
$idDyna = $resDyna->fetch_assoc()['id'];

$hoje = date('Y-m-d');

// Alertas Grafana
$alertasGrafana = [
    ['nome' => 'Prometheus Target Down', 'count' => 3, 'host' => 'srv-k8s-cluster', 'sev' => 'Critico'],
    ['nome' => 'Grafana Dashboard Latency > 2s', 'count' => 15, 'host' => 'grafana-app-01', 'sev' => 'Aviso']
];

foreach ($alertasGrafana as $a) {
    $clocks = [];
    for($i=0; $i<$a['count']; $i++) { $clocks[] = date('Y-m-d H:i:s', strtotime('-' . rand(1, 48) . ' hours')); }
    rsort($clocks);
    $json = json_encode($clocks);
    
    $stmt = $conn->prepare("INSERT INTO zabbix_historico_alertas (empresa_id, data_relatorio, alerta_nome, recorrencia, ai_sugestao, severidade, host_envolvido, ultima_ocorrencia, lista_ocorrencias) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $sugestao = "O Grafana detectou lentidão. Verifique a carga no Prometheus e os recursos do pod.";
    $stmt->bind_param("ississsss", $idGrafana, $hoje, $a['nome'], $a['count'], $sugestao, $a['sev'], $a['host'], $clocks[0], $json);
    $stmt->execute();
}

// Alertas Dynatrace
$alertasDyna = [
    ['nome' => 'Davis AI: Problem with Transaction Latency', 'count' => 1, 'host' => 'PaymentGateway-API', 'sev' => 'Emergência'],
    ['nome' => 'Process Crash: java.exe', 'count' => 8, 'host' => 'app-server-prod-02', 'sev' => 'Critico']
];

foreach ($alertasDyna as $a) {
    $clocks = [];
    for($i=0; $i<$a['count']; $i++) { $clocks[] = date('Y-m-d H:i:s', strtotime('-' . rand(1, 48) . ' hours')); }
    rsort($clocks);
    $json = json_encode($clocks);
    
    $stmt = $conn->prepare("INSERT INTO zabbix_historico_alertas (empresa_id, data_relatorio, alerta_nome, recorrencia, ai_sugestao, severidade, host_envolvido, ultima_ocorrencia, lista_ocorrencias) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $sugestao = "O Dynatrace Davis identificou uma anomalia na latência. Verifique os traces da transação afetada.";
    $stmt->bind_param("ississsss", $idDyna, $hoje, $a['nome'], $a['count'], $sugestao, $a['sev'], $a['host'], $clocks[0], $json);
    $stmt->execute();
}

echo "Alertas fictícios criados para Grafana e Dynatrace!";
$conn->close();
?>
