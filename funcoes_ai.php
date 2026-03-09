<?php
/**
 * FUNÇÕES DE INTELIGÊNCIA ARTIFICIAL: funcoes_ai.php
 * Centraliza a lógica de comunicação com APIs de IA (Gemini e GitHub Models).
 * Inclui mecanismo de fallback automático para garantir disponibilidade.
 */

require_once 'credentials.php';

/**
 * Função unificada para chamadas de IA
 * 
 * @param string $prompt O prompt a ser enviado
 * @param string $systemContext Contexto do sistema (opcional)
 * @param array $history Histórico de conversa (opcional)
 * @return string|null Resposta da IA ou null em caso de falha total
 */
function callAI($prompt, $systemContext = '', $history = [])
{
    // Limpa erro anterior
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['last_ai_error'] = null;

    $geminiApiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
    $githubToken = defined('GITHUB_TOKEN') ? GITHUB_TOKEN : '';

    // Tenta primeiro o GitHub Models (mais estável no momento conforme testes)
    if (!empty($githubToken)) {
        $response = callGitHubModels($prompt, $systemContext, $githubToken, $history);
        if ($response)
            return $response;
    }

    // Fallback ou segunda opção: Gemini
    if (!empty($geminiApiKey)) {
        $response = callGeminiUnified($prompt, $systemContext, $geminiApiKey, $history);
        if ($response)
            return $response;
    }

    if (!$_SESSION['last_ai_error']) {
        $_SESSION['last_ai_error'] = 'Nenhuma API respondeu ou chaves não configuradas.';
    }
    return null;
}

/**
 * Comunicação com GitHub Models (GPT-4o-mini)
 */
function callGitHubModels($prompt, $systemContext, $token, $history = [])
{
    $url = "https://models.inference.ai.azure.com/chat/completions";

    $messages = [];
    if (!empty($systemContext)) {
        $messages[] = ['role' => 'system', 'content' => $systemContext];
    }

    foreach ($history as $msg) {
        $role = ($msg['role'] === 'model' || $msg['role'] === 'assistant') ? 'assistant' : 'user';
        $messages[] = ['role' => $role, 'content' => $msg['text']];
    }

    $messages[] = ['role' => 'user', 'content' => $prompt];

    $payload = [
        'messages' => $messages,
        'model' => 'gpt-4o-mini',
        'temperature' => 1.0,
        'top_p' => 1.0,
        'max_tokens' => 2048
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        $_SESSION['last_ai_error'] = "GitHub cURL Error: " . $curlError;
        return null;
    }

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? null;
    }

    $_SESSION['last_ai_error'] = "GitHub Error (HTTP $httpCode): " . $response;
    return null;
}

/**
 * Comunicação com Google Gemini (gemini-1.5-flash)
 */
function callGeminiUnified($prompt, $systemContext, $apiKey, $history = [])
{
    // Tenta v1 primeiro
    $model = "gemini-1.5-flash";
    $url = "https://generativelanguage.googleapis.com/v1/models/$model:generateContent?key=" . $apiKey;

    $payload = [];
    if (!empty($systemContext)) {
        $payload['system_instruction'] = ['parts' => [['text' => $systemContext]]];
    }

    $contents = [];
    foreach ($history as $msg) {
        $contents[] = [
            'role' => ($msg['role'] === 'assistant' || $msg['role'] === 'model') ? 'model' : 'user',
            'parts' => [['text' => $msg['text']]]
        ];
    }
    $contents[] = [
        'role' => 'user',
        'parts' => [['text' => $prompt]]
    ];

    $payload['contents'] = $contents;
    $payload['generationConfig'] = ['temperature' => 0.7, 'maxOutputTokens' => 2048];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    if ($curlError) {
        $_SESSION['last_ai_error'] = "Gemini cURL Error (v1): " . $curlError;
    } else {
        $_SESSION['last_ai_error'] = "Gemini Error (v1, HTTP $httpCode): " . $response;
    }

    // Se falhar, tenta v1beta como último recurso
    $urlBeta = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=" . $apiKey;
    $ch = curl_init($urlBeta);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErrorBeta = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    if ($curlErrorBeta) {
        $_SESSION['last_ai_error'] = "Gemini cURL Error (v1beta): " . $curlErrorBeta;
    } else {
        $_SESSION['last_ai_error'] = "Gemini Error (v1beta, HTTP $httpCode): " . $response;
    }

    return null;
}
