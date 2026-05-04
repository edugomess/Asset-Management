<?php
require_once 'funcoes_ai.php';
require_once 'credentials.php';
session_start();
$_SESSION['last_ai_error'] = null;
$res = callGeminiUnified('responda apenas OK', '', GEMINI_API_KEY, []);
var_dump($res);
echo "\nERRO: " . $_SESSION['last_ai_error'];
?>
