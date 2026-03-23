<?php
require_once 'credentials.php';
$GEMINI_API_KEY = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';

function testGemini($model, $apiKey, $version = 'v1beta')
{
    $url = "https://generativelanguage.googleapis.com/$version/models/$model:generateContent?key=" . $apiKey;
    $payload = [
        'contents' => [['role' => 'user', 'parts' => [['text' => 'Hello, say test.']]]],
        'generationConfig' => ['temperature' => 0.5, 'maxOutputTokens' => 10]
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
        'response' => json_decode($response, true),
        'raw' => $response
    ];
}

$results = [];
$models = [
    ['model' => 'gemini-1.5-flash-latest', 'version' => 'v1beta'],
    ['model' => 'gemini-1.5-pro-latest', 'version' => 'v1beta'],
    ['model' => 'gemini-pro', 'version' => 'v1beta'],
];

foreach ($models as $m) {
    $results[] = testGemini($m['model'], $GEMINI_API_KEY, $m['version']);
}

echo json_encode($results, JSON_PRETTY_PRINT);
