<?php
/**
 * TESTE DE CHAVE DE API DA IA: ajax_test_ai_key.php
 * Testa a validade de uma chave de API do Gemini antes de salvar.
 */
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'auth.php';

if ($_SESSION['nivelUsuario'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => __('Acesso negado.')]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['api_key'])) {
    echo json_encode(['success' => false, 'message' => __('Chave não fornecida.')]);
    exit;
}

$apiKey = trim($_POST['api_key']);

// Inclui funcoes_ai.php para usar callGeminiUnified
require_once 'funcoes_ai.php';

// Reseta o erro anterior
$_SESSION['last_ai_error'] = null;

// Envia um prompt simples para testar a conectividade e validade da chave
$response = callGeminiUnified("Por favor, responda apenas com a palavra OK.", "", $apiKey, []);

if ($response && stripos($response, 'OK') !== false) {
    echo json_encode([
        'success' => true, 
        'message' => __('Chave válida e comunicando perfeitamente com o Google Gemini!')
    ]);
} else {
    // Busca o erro capturado na sessão
    $errorDetails = $_SESSION['last_ai_error'] ?? __('Erro desconhecido ao testar a chave.');
    
    // Simplificar mensagens de erro mais comuns para o usuário final
    if (strpos($errorDetails, 'API_KEY_INVALID') !== false || strpos($errorDetails, 'expired') !== false) {
        $msg = __('A chave inserida expirou ou é inválida. Por favor, gere uma nova chave no Google AI Studio.');
    } elseif (strpos($errorDetails, 'leaked') !== false) {
        $msg = __('Esta chave foi bloqueada pelo Google por ter vazado publicamente. Gere uma nova.');
    } elseif (strpos($errorDetails, '429') !== false || strpos($errorDetails, 'quota') !== false) {
        $msg = __('A chave é VÁLIDA, mas a sua conta do Google excedeu o limite de requisições (Quota Excedida HTTP 429). Aguarde alguns minutos ou verifique seu plano no Google AI Studio.');
    } else {
        $msg = __('Falha na comunicação: ') . $errorDetails;
    }

    echo json_encode([
        'success' => false, 
        'message' => $msg,
        'details' => $errorDetails
    ]);
}
exit;
?>
