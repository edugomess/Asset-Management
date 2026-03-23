<?php
/**
 * CLASSIFICADOR DE PRIORIDADE IA: ajax_classificar_prioridade.php
 * Analisa o título e a descrição de um chamado via IA para
 * sugerir a criticidade (P1, P2, P3, P4).
 */
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'conexao.php';
require_once 'funcoes_ai.php';

ob_clean();
header('Content-Type: application/json');

// Verificar se o Agente de IA está ativo
$sql_config = "SELECT ia_agente_ativo FROM configuracoes_alertas LIMIT 1";
$res_config = mysqli_query($conn, $sql_config);
$ia_geral = true;
if ($res_config && mysqli_num_rows($res_config) > 0) {
    $row = mysqli_fetch_assoc($res_config);
    $ia_geral = (bool) ($row['ia_agente_ativo'] ?? 1);
}

if (!$ia_geral) {
    echo json_encode(['success' => false, 'message' => '⚠️ O Agente de IA está desabilitado.']);
    exit;
}

$titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
$descricao = isset($_POST['descricao']) ? $_POST['descricao'] : '';

if (empty($titulo)) {
    echo json_encode(['success' => false, 'message' => 'Título não fornecido.']);
    exit;
}

$prompt = "Você é um especialista em gestão de incidentes de TI (ITIL).
Analise o seguinte chamado para classificar sua criticidade/prioridade:
Título: $titulo
Descrição: $descricao

Classifique em um dos seguintes níveis:
- P1 (Crítica): Interrupção total de serviço crítico, impacto em muitos usuários ou risco de segurança.
- P2 (Alta): Degradação severa ou impacto em departamento inteiro.
- P3 (Média): Problema menor com contorno ou impacto individual moderado.
- P4 (Baixa): Dúvidas, melhorias ou pequenos ajustes.

Responda APENAS em formato JSON com os campos 'prioridade' (apenas a tag P1, P2, P3 ou P4) e 'justificativa' (uma frase curta).
Exemplo: {\"prioridade\": \"P1\", \"justificativa\": \"Servidor principal offline impactando toda a empresa.\"}";

$reply = callAI($prompt);

if ($reply) {
    // Tenta extrair o JSON da resposta (as vezes a IA coloca ```json ... ```)
    $clean_reply = preg_replace('/```json\s*|```/', '', $reply);
    $data = json_decode(trim($clean_reply), true);
    
    if ($data && isset($data['prioridade'])) {
        echo json_encode(['success' => true, 'prioridade' => $data['prioridade'], 'justificativa' => $data['justificativa']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao processar resposta da IA.', 'raw' => $reply]);
    }
} else {
    echo json_encode(['success' => false, 'message' => '⚠️ O assistente de IA está indisponível no momento.']);
}
$conn->close();
?>
