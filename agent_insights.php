<?php
/**
 * BACKEND DE INTELIGÊNCIA ARTIFICIAL: agent_insights.php
 * Este script coleta métricas críticas do sistema e as envia para o Google Gemini AI 
 * para gerar uma consultoria estratégica personalizada.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// SEGURANÇA: Garante que apenas usuários autenticados possam invocar a análise da IA
if (!isset($_SESSION['id_usuarios'])) {
    header('Content-Type: application/json');
    echo json_encode(['reply' => '⚠️ Sessão expirada ou acesso negado. Por favor, reinicie o login.']);
    exit;
}

// OTIMIZAÇÃO: Libera o lock da sessão para não bloquear outras requisições enquanto a IA processa
session_write_close();

include 'conexao.php';
require_once 'funcoes_ai.php';

// Verificar se o Agente de IA e a Prevenção estão ativos
$sql_config = "SELECT ia_agente_ativo, ia_preve_ativo FROM configuracoes_alertas LIMIT 1";
$res_config = mysqli_query($conn, $sql_config);
$ia_geral = true;
$ia_preve = true;
if ($res_config && mysqli_num_rows($res_config) > 0) {
    $row = mysqli_fetch_assoc($res_config);
    $ia_geral = (bool) ($row['ia_agente_ativo'] ?? 1);
    $ia_preve = (bool) ($row['ia_preve_ativo'] ?? 1);
}

if (!$ia_geral || !$ia_preve) {
    echo json_encode(['reply' => '⚠️ O motor de inteligência estratégica está desabilitado no momento.']);
    exit;
}

// 1. ENGENHARIA DE PROMPT (Contextualização de Negócio para a IA)
$contexto = "Você é um Consultor de TI Sênior e Analista de Dados especializado em Gestão de Ativos.\n\n";

// Coleta dados de incidentes repetitivos
$sql_rec = "SELECT titulo, COUNT(*) as total FROM chamados GROUP BY titulo HAVING total > 1 ORDER BY total DESC LIMIT 5";
$res_rec = $conn->query($sql_rec);
$contexto .= "📊 HISTÓRICO DE RECORRÊNCIA (Maiores Problemas):\n";
if ($res_rec && $res_rec->num_rows > 0) {
    while ($row = $res_rec->fetch_assoc()) {
        $contexto .= "- O chamado '" . $row['titulo'] . "' ocorreu " . $row['total'] . " vezes recentemente.\n";
    }
} else {
    $contexto .= "- Não há padrões de erro repetitivos detectados no momento.\n";
}

// Coleta dados de ativos com desgaste excessivo
$sql_at = "SELECT a.hostName, a.modelo, COUNT(m.id_manutencao) as total FROM ativos a JOIN manutencao m ON a.id_asset = m.id_asset GROUP BY a.id_asset HAVING total > 1 ORDER BY total DESC LIMIT 3";
$res_at = $conn->query($sql_at);
$contexto .= "\n🔧 ATIVOS COM ALTA FREQUÊNCIA DE MANUTENÇÃO:\n";
if ($res_at && $res_at->num_rows > 0) {
    while ($row = $res_at->fetch_assoc()) {
        $contexto .= "- O dispositivo " . $row['hostName'] . " (" . $row['modelo'] . ") exigiu " . $row['total'] . " intervenções.\n";
    }
}

// Define o objetivo final do prompt
$prompt = $contexto . "\n\nCom base nestes indicadores de infraestrutura, elabore um sumário executivo sobre a 'Saúde Operacional' atual. Em seguida, forneça 5 recomendações táticas prioritárias para mitigar falhas e otimizar custos. Adote um tom de consultoria estratégica, use negrito para destacar alertas e seja direto nos planos de ação.";

// Executa a chamada e retorna o resultado em JSON
$reply = callAI($prompt);

if ($reply) {
    echo json_encode(['reply' => $reply]);
} else {
    echo json_encode(['reply' => '⚠️ O motor de inteligência estratégica está temporariamente indisponível.']);
}

// Fecha a conexão com o banco
$conn->close();
?>