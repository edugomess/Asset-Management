<?php
/**
 * Gerador de Relatório Semanal de Alertas do Zabbix (Top 10)
 * 
 * Este script conecta na API do Zabbix, extrai os problemas recentes,
 * identifica o Top 10 mais recorrente, solicita sugestões à IA (Google Gemini)
 * e envia um relatório por e-mail.
 */

// ==========================================
// 1. CONFIGURAÇÕES
// ==========================================

// Configurações de IA Global (Google Gemini API)
// Pode ser sobrescrita por empresa
$geminiApiKeyGlobal = 'SUA_CHAVE_API_GEMINI_AQUI';

// Configurações de E-mail (SMTP)
$smtpHost = 'smtp.seudominio.com.br';
$smtpPort = 587;
$smtpUser = 'seu_email@seudominio.com.br';
$smtpPass = 'sua_senha_smtp';
$emailDestinatario = 'gestor@seudominio.com.br';

// Período de Análise (últimos 7 dias)
$timeFrom = strtotime('-7 days');
$timeTill = time();

// ==========================================
// 2. FUNÇÕES AUXILIARES
// ==========================================

function callZabbixApi($url, $method, $params, $auth = null) {
    $payload = [
        'jsonrpc' => '2.0',
        'method'  => $method,
        'params'  => $params,
        'id'      => 1,
        'auth'    => $auth
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json-rpc']);
    
    $result = curl_exec($ch);
    if(curl_errno($ch)){
        die('Erro cURL na API do Zabbix: ' . curl_error($ch));
    }
    curl_close($ch);
    
    $response = json_decode($result, true);
    if(isset($response['error'])) {
        die("Erro na API do Zabbix: " . $response['error']['data']);
    }
    
    return $response['result'] ?? null;
}

function callGeminiAPI($apiKey, $prompt) {
    if (empty($apiKey) || $apiKey === 'SUA_CHAVE_API_GEMINI_AQUI') {
        return "Nenhuma API Key da IA configurada. Ação sugerida padrão: Investigar os logs do equipamento e verificar configurações de rede/recurso.";
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
    $data = [
        "contents" => [
            ["parts" => [["text" => $prompt]]]
        ],
        "systemInstruction" => [
            "parts" => [["text" => "Você é um especialista sênior em infraestrutura e Zabbix. Seja direto, conciso e sugira ações práticas (em bullet points) para resolver o problema relatado."]]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    $response = json_decode($result, true);
    return $response['candidates'][0]['content']['parts'][0]['text'] ?? "Não foi possível gerar uma recomendação.";
}

// ==========================================
// 3. EXECUÇÃO MULTI-EMPRESAS E GRAVAÇÃO NO BANCO
// ==========================================

require 'conexao.php';
require_once 'funcoes_email.php';

$empresas = [];
$result = $conn->query("SELECT * FROM zabbix_empresas ORDER BY nome ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $empresas[] = $row;
    }
}

if (empty($empresas)) {
    die("Nenhuma empresa cadastrada no banco de dados para gerar o relatório.");
}

$hoje = date('Y-m-d');
echo "Iniciando geração de relatórios para " . count($empresas) . " empresas...\n\n";

foreach ($empresas as $empresa) {
    echo "========================================\n";
    echo "Processando Empresa: {$empresa['nome']}\n";
    echo "========================================\n";

    $zabbixUrl = $empresa['url'];
    $zabbixUser = $empresa['user'];
    $zabbixPass = $empresa['pass'];
    $emailDestinatario = $empresa['email_destinatario'];
    
    // Usa a chave local da empresa, ou a global se a local estiver vazia
    $currentGeminiKey = !empty($empresa['gemini_api_key']) ? $empresa['gemini_api_key'] : $geminiApiKeyGlobal;

    // 3.1. Autenticação no Zabbix
    echo "Autenticando no Zabbix de {$empresa['nome']}...\n";
    $authToken = callZabbixApi($zabbixUrl, 'user.login', [
        'user' => $zabbixUser,
        'password' => $zabbixPass
    ]);

    if (!$authToken) {
        echo "[ERRO] Falha na autenticação da empresa {$empresa['nome']}. Pulando para a próxima.\n";
        continue;
    }

    // 3.2. Buscar Eventos Recentes (Problemas com Host)
    echo "Buscando problemas dos últimos 7 dias...\n";
    $problems = callZabbixApi($zabbixUrl, 'event.get', [
        'time_from' => $timeFrom,
        'time_till' => $timeTill,
        'source' => 0, // EVENT_SOURCE_TRIGGERS
        'object' => 0, // EVENT_OBJECT_TRIGGER
        'value' => 1, // PROBLEM
        'selectHosts' => ['name'],
        'sortfield' => ['eventid'],
        'sortorder' => 'DESC',
    ], $authToken);

    if (empty($problems)) {
        echo "Nenhum problema encontrado para {$empresa['nome']}. Pulando.\n";
        continue;
    }

    // 3.3. Agrupar e Contar os Alertas por Nome e Host
    $alertCounts = [];
    foreach ($problems as $problem) {
        $name = $problem['name'];
        $severity = (int) $problem['severity'];
        $host = isset($problem['hosts'][0]['name']) ? $problem['hosts'][0]['name'] : 'Host Desconhecido';
        $clock = date('Y-m-d H:i:s', $problem['clock']);
        
        $key = $name . "_" . $host;

        if (!isset($alertCounts[$key])) {
            $alertCounts[$key] = [
                'count' => 0,
                'name' => $name,
                'severity' => $severity,
                'host' => $host,
                'last_clock' => $clock,
                'clocks' => []
            ];
        }
        $alertCounts[$key]['count']++;
        $alertCounts[$key]['clocks'][] = $clock;
        if ($clock > $alertCounts[$key]['last_clock']) {
            $alertCounts[$key]['last_clock'] = $clock;
        }
    }

    usort($alertCounts, function($a, $b) {
        return $b['count'] <=> $a['count'];
    });

    $top10 = array_slice($alertCounts, 0, 10);

    // 3.4. Analisar com IA e Construir Template HTML
    echo "Analisando com Inteligência Artificial e montando relatório...\n";

    $html = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 20px; }
            .container { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .header { background-color: #0056b3; color: #fff; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .alert-card { border: 1px solid #ddd; border-left: 5px solid #d9534f; border-radius: 4px; padding: 15px; margin-bottom: 20px; background-color: #fafafa; }
            .alert-title { font-size: 18px; font-weight: bold; margin-top: 0; color: #d9534f; }
            .alert-meta { font-size: 14px; color: #666; margin-bottom: 10px; }
            .ai-recommendation { background-color: #e9f7ef; border: 1px solid #c3e6cb; padding: 10px; border-radius: 4px; font-size: 14px; color: #155724; }
            .ai-recommendation strong { color: #0f5132; }
        </style>
    </head>
    <body>
    <div class='container'>
        <div class='header'>
            <h2>Relatório Semanal Zabbix - {$empresa['nome']}</h2>
            <p>Top 10 alertas que mais demandaram atenção técnica</p>
        </div>
        <div class='content'>
    ";

    foreach ($top10 as $index => $alert) {
        $position = $index + 1;
        $name = htmlspecialchars($alert['name']);
        $count = $alert['count'];
        $host = htmlspecialchars($alert['host']);
        $lastClock = $alert['last_clock'];
        
        // Mapear Severidade
        $sevLabels = [0 => 'Não classificado', 1 => 'Informação', 2 => 'Aviso (Warning)', 3 => 'Média (Average)', 4 => 'Alta (High)', 5 => 'Desastre (Disaster)'];
        $sevColors = [0 => '#9e9e9e', 1 => '#2196f3', 2 => '#ffc107', 3 => '#ff9800', 4 => '#f44336', 5 => '#b71c1c'];
        $severityLabel = $sevLabels[$alert['severity']] ?? 'Desconhecida';
        $severityColor = $sevColors[$alert['severity']] ?? '#9e9e9e';
        
        $prompt = "No Zabbix da empresa {$empresa['nome']}, o alerta '{$name}' disparou {$count} vezes nesta semana no equipamento/host '{$host}'. A severidade configurada para esse alerta é '{$severityLabel}'. Quais as causas raiz mais prováveis nesse tipo de equipamento e o que a equipe técnica deve fazer para mitigar ou resolver definitivamente esse problema e diminuir essa recorrência?";
        
        $aiSuggestion = callGeminiAPI($currentGeminiKey, $prompt);
        $aiSuggestionHtml = nl2br(htmlspecialchars($aiSuggestion));
        $aiSuggestionHtml = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $aiSuggestionHtml);

        // Ordenar os horários do mais recente para o mais antigo e converter para JSON
        $clocksArray = $alert['clocks'];
        rsort($clocksArray);
        $listaOcorrenciasJson = json_encode($clocksArray);

        // Gravar no histórico (MySQL)
        $stmtHistory = $conn->prepare("INSERT INTO zabbix_historico_alertas (empresa_id, data_relatorio, alerta_nome, recorrencia, ai_sugestao, severidade, host_envolvido, ultima_ocorrencia, lista_ocorrencias) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $plainName = $alert['name'];
        $stmtHistory->bind_param("ississsss", $empresa['id'], $hoje, $plainName, $count, $aiSuggestion, $severityLabel, $alert['host'], $lastClock, $listaOcorrenciasJson);
        $stmtHistory->execute();
        $stmtHistory->close();

        $html .= "
            <div class='alert-card' style='border-left: 5px solid {$severityColor};'>
                <h3 class='alert-title' style='color: {$severityColor};'>#{$position} - {$name}</h3>
                <div class='alert-meta'>
                    <strong>Host:</strong> {$host} <br>
                    <strong>Severidade:</strong> {$severityLabel} <br>
                    <strong>Última Ocorrência:</strong> " . date('d/m/Y H:i', strtotime($lastClock)) . " <br>
                    <strong>Recorrência:</strong> {$count} vezes esta semana
                </div>
                <div class='ai-recommendation'>
                    <strong>Dica de Mitigação (IA):</strong><br>
                    {$aiSuggestionHtml}
                </div>
            </div>
        ";
    }

    $html .= "
        </div>
    </div>
    </body>
    </html>
    ";

    // 3.5. Enviar E-mail usando PHPMailer (funcoes_email.php)
    echo "Relatório gerado. Enviando e-mail para {$emailDestinatario}...\n";

    if (enviarRelatorioZabbix($emailDestinatario, $html, $empresa['nome'])) {
        echo "-> E-mail enviado com sucesso para {$emailDestinatario}!\n\n";
    } else {
        echo "-> [ERRO] Falha ao enviar e-mail para {$emailDestinatario}.\n\n";
    }
}

echo "Todos os processamentos foram finalizados!\n";
?>
