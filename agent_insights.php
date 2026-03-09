<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Segurança: Verificar se o usuário está logado antes de processar
if (!isset($_SESSION['id_usuarios'])) {
    header('Content-Type: application/json');
    echo json_encode(['reply' => '⚠️ Acesso negado. Por favor, faça login.']);
    exit;
}

// Liberar a sessão para evitar travamento de navegação
session_write_close();

include 'conexao.php';
require_once 'credentials.php';
$GEMINI_API_KEY = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';

header('Content-Type: application/json');

if (empty($GEMINI_API_KEY)) {
    echo json_encode(['reply' => '⚠️ API Key do Gemini não configurada em credentials.php.']);
    exit;
}

// 1. Coleta de Dados para o Prompt
$contexto = "Você é um Consultor de TI Sênior analisando dados do sistema Asset MGT.\n\n";

// Dados de recorrência
$sql_rec = "SELECT titulo, COUNT(*) as total FROM chamados GROUP BY titulo HAVING total > 1 ORDER BY total DESC LIMIT 5";
$res_rec = $conn->query($sql_rec);
$contexto .= "📊 PADRÕES DE INCIDENTES (Recorrência):\n";
if ($res_rec && $res_rec->num_rows > 0) {
    while ($row = $res_rec->fetch_assoc()) {
        $contexto .= "- '" . $row['titulo'] . "' se repetiu " . $row['total'] . " vezes.\n";
    }
} else {
    $contexto .= "- Nenhuma recorrência significativa detectada.\n";
}

// Dados de ativos críticos
$sql_at = "SELECT a.hostName, a.modelo, COUNT(m.id_manutencao) as total FROM ativos a JOIN manutencao m ON a.id_asset = m.id_asset GROUP BY a.id_asset HAVING total > 1 ORDER BY total DESC LIMIT 3";
$res_at = $conn->query($sql_at);
$contexto .= "\n🔧 ATIVOS COM MAIS MANUTENÇÕES:\n";
if ($res_at && $res_at->num_rows > 0) {
    while ($row = $res_at->fetch_assoc()) {
        $contexto .= "- " . $row['hostName'] . " (" . $row['modelo'] . ") teve " . $row['total'] . " manutenções.\n";
    }
}

$prompt = $contexto . "\n\nCom base nesses dados, escreva uma análise detalhada e completa sobre a saúde tecnológica da empresa hoje. Depois, apresente 5 sugestões estratégicas e práticas para reduzir drasticamente a recorrência desses incidentes. Use um tom profissional, consultivo e inspirador. Formate com negrito para destacar pontos críticos.";

// 2. Chamada ao Gemini
function callGeminiInsights($prompt, $apiKey)
{
    $model = "gemini-flash-latest";
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=" . $apiKey;
    $payload = [
        'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
        'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 2000]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }
    return null;
}

$reply = callGeminiInsights($prompt, $GEMINI_API_KEY);

if ($reply) {
    echo json_encode(['reply' => $reply]);
} else {
    echo json_encode(['reply' => '⚠️ Não foi possível gerar a análise por IA no momento. Verifique sua conexão ou API Key.']);
}
$conn->close();