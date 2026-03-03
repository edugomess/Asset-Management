<?php
include 'conexao.php';
require_once 'credentials.php';
$apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';

$model = "gemini-2.0-flash";
$url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=" . $apiKey;

$payload = [
    'contents' => [['role' => 'user', 'parts' => [['text' => 'Olá, responda apenas OK.']]]]
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

echo "HTTP Code: $httpCode\n";
echo "Resposta: $response\n";
?>