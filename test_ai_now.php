<?php
require_once 'funcoes_ai.php';
$response = callAI('Responda apenas com a palavra "FUNCIONANDO" se estiver tudo certo.');
if ($response) {
    echo "Sucesso: " . $response;
} else {
    echo "Erro: ";
    if (session_status() === PHP_SESSION_NONE) session_start();
    echo $_SESSION['last_ai_error'] ?? 'Erro desconhecido';
}
?>
