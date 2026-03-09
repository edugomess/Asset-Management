<?php
require_once 'funcoes_ai.php';

echo "Testing GitHub Models...\n";
$gitRep = callGitHubModels("Say hello", "You are a test assistant", defined('GITHUB_TOKEN') ? GITHUB_TOKEN : '');
echo "GitHub Response: " . ($gitRep ?: "FAILED") . "\n";
if (!$gitRep && isset($_SESSION['last_ai_error'])) {
    echo "GitHub Error: " . $_SESSION['last_ai_error'] . "\n";
}

echo "\nTesting Gemini (v1)...\n";
$gemRep = callGeminiUnified("Say hello", "You are a test assistant", defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '');
echo "Gemini Response: " . ($gemRep ?: "FAILED") . "\n";
if (!$gemRep && isset($_SESSION['last_ai_error'])) {
    echo "Gemini Error: " . $_SESSION['last_ai_error'] . "\n";
}
