<?php
require 'conexao.php';

// 1. Inserir uma empresa fictícia
$nomeEmpresa = "TechCorp Solutions (Fictício)";
$stmtEmp = $conn->prepare("INSERT INTO zabbix_empresas (nome, url, user, pass, email_destinatario) VALUES (?, 'http://fake.zabbix.local', 'admin', 'zabbix', 'admin@techcorp.local')");
$stmtEmp->bind_param("s", $nomeEmpresa);
$stmtEmp->execute();
$empresa_id = $stmtEmp->insert_id;
$stmtEmp->close();

// 2. Inserir alertas fictícios
$hoje = date('Y-m-d');
$ontem = date('Y-m-d', strtotime('-1 day'));
$semana_passada = date('Y-m-d', strtotime('-7 days'));

$fakeAlerts = [
    [
        'data' => $hoje,
        'alerta' => 'Zabbix server is not running: the information displayed may not be current',
        'recorrencia' => 1,
        'sugestao' => '**Causa Raiz:** O serviço do Zabbix Server pode ter parado por falta de memória (OOM Killer) ou falha na conexão com o banco de dados. \n**Mitigação:** Acesse o SRV-MONITOR-01, verifique os logs no /var/log/zabbix/zabbix_server.log e ajuste o CacheSize se for necessário.',
        'severidade' => 'Alta (High)',
        'host' => 'SRV-MONITOR-01',
        'clock' => date('Y-m-d H:i:s', strtotime('-2 hours'))
    ],
    [
        'data' => $hoje,
        'alerta' => 'High CPU utilization (over 90% for 5m)',
        'recorrencia' => 12,
        'sugestao' => '**Causa Raiz:** Processo de backup ou query pesada no MySQL rodando fora do horário.\n**Mitigação:** Investigar os processos com comando "top". Se for backup agendado, ajustar a trigger do Zabbix para ignorar o alerta nesse período para evitar Falsos Positivos.',
        'severidade' => 'Aviso (Warning)',
        'host' => 'SRV-BD-02',
        'clock' => date('Y-m-d H:i:s', strtotime('-5 hours'))
    ],
    [
        'data' => $hoje,
        'alerta' => 'Unavailable by ICMP ping',
        'recorrencia' => 3,
        'sugestao' => '**Causa Raiz:** Queda de link principal do provedor ou falha no switch core.\n**Mitigação:** Verificar no Firewall se a rota de failover (SD-WAN) assumiu corretamente e abrir chamado junto a operadora de internet.',
        'severidade' => 'Desastre (Disaster)',
        'host' => 'Roteador-Borda-SP',
        'clock' => date('Y-m-d H:i:s', strtotime('-12 hours'))
    ],
    [
        'data' => $ontem,
        'alerta' => 'Disk space is critically low (less than 5% free)',
        'recorrencia' => 5,
        'sugestao' => '**Causa Raiz:** Acúmulo de arquivos de log da aplicação IIS.\n**Mitigação:** A equipe de infra deve expandir o volume no VMware e configurar um script de logrotate para limpar arquivos .log antigos automaticamente.',
        'severidade' => 'Alta (High)',
        'host' => 'APP-WEB-PROD',
        'clock' => date('Y-m-d H:i:s', strtotime('-1 day -4 hours'))
    ],
    [
        'data' => $semana_passada,
        'alerta' => 'Too many processes on Zabbix server',
        'recorrencia' => 8,
        'sugestao' => '**Causa Raiz:** Muitos pollers rodando ou retenção alta.\n**Mitigação:** Aumentar os pollers no zabbix_server.conf ou revisar os intervalos de checagem dos itens.',
        'severidade' => 'Média (Average)',
        'host' => 'SRV-MONITOR-01',
        'clock' => date('Y-m-d H:i:s', strtotime('-7 days'))
    ]
];

$stmt = $conn->prepare("INSERT INTO zabbix_historico_alertas (empresa_id, data_relatorio, alerta_nome, recorrencia, ai_sugestao, severidade, host_envolvido, ultima_ocorrencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($fakeAlerts as $a) {
    $stmt->bind_param("ississss", $empresa_id, $a['data'], $a['alerta'], $a['recorrencia'], $a['sugestao'], $a['severidade'], $a['host'], $a['clock']);
    $stmt->execute();
}
$stmt->close();

echo "Dados fictícios inseridos com sucesso!";
?>
