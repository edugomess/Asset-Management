<?php
require_once 'credentials.php';
$apiKey = GEMINI_API_KEY;

$models = ['gemini-2.5-pro', 'gemini-2.5-flash', 'gemini-2.0-flash-lite'];

foreach ($models as $model) {
    echo "Testando $model... ";
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey";
    $payload = ['contents' => [['role' => 'user', 'parts' => [['text' => 'diga apenas: funcionando!']]]]];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $r = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($r, true);
    if ($code === 200 && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        echo "OK! -> " . trim($data['candidates'][0]['content']['parts'][0]['text']) . "\n";
    } else {
        $msg = isset($data['error']['message']) ? substr($data['error']['message'], 0, 80) : "HTTP $code";
        echo "ERRO: $msg\n";
    }
}
