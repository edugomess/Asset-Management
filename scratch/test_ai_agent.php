<?php
/**
 * DIAGNÓSTICO DO AGENTE DE IA: scratch/test_ai_agent.php
 */
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../funcoes_ai.php';

echo "--- RE-DIAGNÓSTICO DO AGENTE DE IA ---\n\n";

// 1. Testar Google Gemini (NOVA CHAVE)
echo "1. Testando Google Gemini (v2.0-flash)...\n";
$start = microtime(true);
$apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
if (empty($apiKey)) {
    echo "[ERRO] GEMINI_API_KEY não definida em credentials.php.\n\n";
} else {
    $res_gemini = callGeminiUnified("Responda apenas 'ATIVO'", "Você é um testador.", $apiKey);
    $time = round(microtime(true) - $start, 2);
    if ($res_gemini) {
        echo "[SUCESSO] Gemini RESPONDEU em {$time}s: $res_gemini\n\n";
    } else {
        echo "[FALHA] Gemini ainda não respondeu. Erro: " . ($_SESSION['last_ai_error'] ?? 'Indefinido') . "\n\n";
    }
}

// 2. Testar Fluxo Unificado com Fallback
echo "2. Testando Fluxo Unificado (callAI)...\n";
$res_unified = callAI("Qual o próximo número na sequência: 2, 4, 8, 16?");
if ($res_unified) {
    echo "[SUCESSO] IA Unificada Funcional: $res_unified\n\n";
} else {
    echo "[CRÍTICO] Nenhuma IA funcionou, mesmo com a nova chave.\n\n";
}

// 3. Testar Classificação de Prioridade (Simulação)
echo "3. Testando Classificação Funcional...\n";
$_POST['titulo'] = "Problema na folha de pagamento";
$_POST['descricao'] = "O sistema de RH travou e ninguém consegue emitir os holerites hoje.";
ob_start();
include __DIR__ . '/../ajax_classificar_prioridade.php';
$output = ob_get_clean();
$data = json_decode($output, true);

if ($data && isset($data['success']) && $data['success']) {
    echo "[SUCESSO] Classificação operando corretamente.\n";
    echo "Prioridade Sugerida: " . $data['prioridade'] . "\n";
    echo "Justificativa: " . $data['justificativa'] . "\n\n";
} else {
    echo "[FALHA] Falha no teste funcional: " . ($data['message'] ?? 'Erro desconhecido') . "\n\n";
}

echo "--- FIM DO TESTE ---\n";
