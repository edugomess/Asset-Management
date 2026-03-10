<?php
require_once 'credentials.php';
$GEMINI_API_KEY = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
$GITHUB_TOKEN = ''; // Provided by user

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
        'provider' => 'gemini',
        'model' => $model,
        'httpCode' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

function testGitHub($model, $token)
{
    $url = "https://models.inference.ai.azure.com/chat/completions";
    $payload = [
        'messages' => [['role' => 'user', 'content' => 'Hello, say test.']],
        'model' => $model,
        'max_tokens' => 10
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
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'provider' => 'github',
        'model' => $model,
        'httpCode' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

$results = [];
$results[] = testGemini('gemini-1.5-flash', $GEMINI_API_KEY);
$results[] = testGitHub('gpt-4o-mini', $GITHUB_TOKEN);

echo json_encode($results, JSON_PRETTY_PRINT);
