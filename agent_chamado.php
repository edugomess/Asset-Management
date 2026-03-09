<?php
/**
 * ASSISTENTE DE RESOLUÇÃO DE CHAMADOS: agent_chamado.php
 * Analisa o título e a descrição de um chamado via Gemini AI para
 * sugerir causas raízes e ações imediatas de suporte técnico.
 */
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'conexao.php';
require_once 'credentials.php';
$GEMINI_API_KEY = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';

ob_clean();
header('Content-Type: application/json');

$titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
$descricao = isset($_POST['descricao']) ? $_POST['descricao'] : '';

if (empty($GEMINI_API_KEY)) {
    echo json_encode(['reply' => '⚠️ API Key do Gemini não configurada.']);
    exit;
}

if (empty($titulo)) {
    echo json_encode(['reply' => 'Título não fornecido.']);
    exit;
}

$prompt = "Você é um especialista em suporte de TI (Nível 2/3). 
Analise o seguinte chamado:
Título: $titulo
Descrição: $descricao

Com base nisso, sugira uma ação imediata de resolução (passo a passo curto) e uma possível causa raiz.
Responda de forma direta e profissional em português brasileiro. Use negritos para destacar comandos ou termos importantes.";

function callGeminiAction($prompt, $apiKey)
{
    $model = "gemini-flash-latest"; // Alias para o modelo mais estável disponível
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=" . $apiKey;

    $payload = [
        'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
        'generationConfig' => ['temperature' => 0.5, 'maxOutputTokens' => 1500]
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

$reply = callGeminiAction($prompt, $GEMINI_API_KEY);

if ($reply) {
    echo json_encode(['reply' => $reply]);
} else {
    echo json_encode(['reply' => '⚠️ O assistente de IA está indisponível para este chamado no momento.']);
}
$conn->close();
