<?php
/**
 * ASSISTENTE DE RESOLUÇÃO DE CHAMADOS: agent_chamado.php
 * Analisa o título e a descrição de um chamado via Gemini AI para
 * sugerir causas raízes e ações imediatas de suporte técnico.
 */
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'conexao.php';
require_once 'funcoes_ai.php';

ob_clean();
header('Content-Type: application/json');

// Verificar se o Agente de IA e o Assistente de Chamados estão ativos
$sql_config = "SELECT ia_agente_ativo, ia_chamados_ativo FROM configuracoes_alertas LIMIT 1";
$res_config = mysqli_query($conn, $sql_config);
$ia_geral = true;
$ia_chamados = true;
if ($res_config && mysqli_num_rows($res_config) > 0) {
    $row = mysqli_fetch_assoc($res_config);
    $ia_geral = (bool) ($row['ia_agente_ativo'] ?? 1);
    $ia_chamados = (bool) ($row['ia_chamados_ativo'] ?? 1);
}

if (!$ia_geral || !$ia_chamados) {
    $reason = !$ia_geral ? 'Agente de IA' : 'Assistente de Chamados';
    echo json_encode(['reply' => "⚠️ O $reason está desabilitado no momento."]);
    exit;
}

$titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
$descricao = isset($_POST['descricao']) ? $_POST['descricao'] : '';

if (empty($titulo)) {
    echo json_encode(['reply' => 'Título não fornecido.']);
    exit;
}

$prompt = "Você é um especialista em suporte de TI (Nível 2/3). 
Analise o seguinte chamado:
Título: $titulo
Descrição: $descricao

Com base nisso, sugira uma ação imediata de resolução (passo a passo curto) e uma possível causa raiz.
Responda de forma direta e profissional em português brasileiro. Use negritos para destacar comandos ou termos importantes.";

$reply = callAI($prompt);

if ($reply) {
    echo json_encode(['reply' => $reply]);
} else {
    echo json_encode(['reply' => '⚠️ O assistente de IA está indisponível para este chamado no momento.']);
}
$conn->close();
