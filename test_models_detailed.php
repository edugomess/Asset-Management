<?php
require_once 'credentials.php';
require_once 'funcoes_ai.php';

$GEMINI_API_KEY = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';

function testModel($model, $apiKey, $version = 'v1beta')
{
    $url = "https://generativelanguage.googleapis.com/$version/models/$model:generateContent?key=" . $apiKey;
    $payload = [
        'contents' => [['parts' => [['text' => 'Hi']]]],
        'generationConfig' => ['maxOutputTokens' => 10]
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

    return [
        'model' => $model,
        'version' => $version,
        'httpCode' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

$models = ['gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-2.0-flash', 'gemini-flash-latest'];
$results = [];
foreach ($models as $m) {
    $results[] = testModel($m, $GEMINI_API_KEY, 'v1beta');
    $results[] = testModel($m, $GEMINI_API_KEY, 'v1');
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>