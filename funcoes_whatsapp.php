<?php
/**
 * FUNÇÕES DE WHATSAPP (SIMPLIFICADO)
 * Envio de mensagens automáticas via CallMeBot.
 */

require_once 'config_notificacoes.php';

// Garantir que o PHP utilize UTF-8 internamente
mb_internal_encoding("UTF-8");

function enviarWhatsAppNovoChamado($id_chamado)
{
    global $conn;

    // 1. Verificar as configurações GLOBAIS do canal WhatsApp
    $sql_check = "SELECT * FROM configuracoes_alertas LIMIT 1";
    $res_check = $conn->query($sql_check);
    if (!$res_check || !$row_check = $res_check->fetch_assoc()) {
        return false;
    }

    if ($row_check['whatsapp_ativo'] != 1 || $row_check['whatsapp_recebe_chamados'] != 1) {
        return false; // Notificações inativas ou desabilitadas para chamados
    }

    // 2. Buscar dados do chamado
    $sql = "SELECT c.*, u.nome as solicitante 
            FROM chamados c 
            LEFT JOIN usuarios u ON c.usuario_id = u.id_usuarios 
            WHERE c.id = $id_chamado";
    $res = $conn->query($sql);

    if (!$res || $res->num_rows == 0) return false;
    $chamado = $res->fetch_assoc();

    $prioridade = strtoupper(trim($chamado['prioridade'] ?? ''));
    $tipoOriginal = strtolower(trim($chamado['categoria'] ?? ''));

    // 3. Filtro GERAL de Prioridade
    $prioEnabled = false;
    if ($prioridade === 'P1' && ($row_check['whatsapp_prioridade_p1'] ?? 1) == 1) $prioEnabled = true;
    else if ($prioridade === 'P2' && ($row_check['whatsapp_prioridade_p2'] ?? 1) == 1) $prioEnabled = true;
    else if ($prioridade === 'P3' && ($row_check['whatsapp_prioridade_p3'] ?? 1) == 1) $prioEnabled = true;
    else if ($prioridade === 'P4' && ($row_check['whatsapp_prioridade_p4'] ?? 1) == 1) $prioEnabled = true;

    if (!$prioEnabled) return false; // Prioridade bloqueada globalmente por canal

    // 4. Filtro GERAL de Categoria (Global)
    $catEnabled = false;
    if (($tipoOriginal === 'incidente') && ($row_check['cat_incidente'] ?? 1) == 1) $catEnabled = true;
    else if (($tipoOriginal === 'mudança' || $tipoOriginal === 'mudanca') && ($row_check['cat_mudanca'] ?? 1) == 1) $catEnabled = true;
    else if (($tipoOriginal === 'requisição' || $tipoOriginal === 'requisicao') && ($row_check['cat_requisicao'] ?? 1) == 1) $catEnabled = true;

    if (!$catEnabled) return false; // Categoria bloqueada globalmente por canal

    $phone = WHATSAPP_PHONE;
    $apikey = WHATSAPP_API_KEY;

    $mensagem = "🆕 *NOVO CHAMADO ABERTO*\n\n";
    $mensagem .= "🆔 *ID:* #" . $id_chamado . "\n";
    $mensagem .= "📝 *Título:* " . $chamado['titulo'] . "\n";
    $mensagem .= "👤 *Solicitante:* " . $chamado['solicitante'] . "\n";
    $mensagem .= "⚡ *Prioridade:* " . $prioridade . "\n";
    $mensagem .= "🗒️ *Descrição:* " . $chamado['descricao'] . "\n";
    $mensagem .= "📅 *Data:* " . date('d/m/Y H:i', strtotime($chamado['data_abertura'])) . "\n\n";
    $mensagem .= "------------------------------------------\n";
    $mensagem .= "💼 _Asset Management System_";

    $url = "https://api.callmebot.com/whatsapp.php?phone=" . $phone . "&text=" . rawurlencode($mensagem) . "&apikey=" . $apikey;

    $options = ["http" => ["method" => "GET", "header" => "User-Agent: PHP\r\n", "timeout" => 10]];
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);

    return true;
}

function enviarWhatsAppManutencao($id_ativo, $observacoes = "")
{
    global $conn;

    $sql_check = "SELECT whatsapp_ativo, whatsapp_recebe_manutencao FROM configuracoes_alertas LIMIT 1";
    $res_check = $conn->query($sql_check);
    if ($res_check && $row_check = $res_check->fetch_assoc()) {
        if ($row_check['whatsapp_ativo'] != 1 || $row_check['whatsapp_recebe_manutencao'] != 1) {
            return false;
        }
    }

    $sql = "SELECT a.* FROM ativos a WHERE a.id_asset = $id_ativo";
    $res = $conn->query($sql);
    if (!$res || $res->num_rows == 0) return false;
    $ativo = $res->fetch_assoc();

    $phone = WHATSAPP_PHONE;
    $apikey = WHATSAPP_API_KEY;

    $mensagem = "🛠️ *ATIVO EM MANUTENÇÃO*\n\n";
    $mensagem .= "🖥️ *Modelo:* " . $ativo['modelo'] . "\n";
    $mensagem .= "🏷️ *TAG:* " . $ativo['tag'] . "\n";
    $mensagem .= "📝 *Obs:* " . ($observacoes ?: "Nenhuma") . "\n";
    $mensagem .= "📅 *Data:* " . date('d/m/Y H:i') . "\n\n";
    $mensagem .= "------------------------------------------\n";
    $mensagem .= "💼 _Asset Management System_";

    $url = "https://api.callmebot.com/whatsapp.php?phone=" . $phone . "&text=" . rawurlencode($mensagem) . "&apikey=" . $apikey;
    $options = ["http" => ["method" => "GET", "header" => "User-Agent: PHP\r\n", "timeout" => 10]];
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);

    return true;
}

function enviarWhatsAppEstoque($itensCriticos)
{
    global $conn;
    if (empty($itensCriticos)) return false;

    // 1. Verificar configuração GLOBAL do WhatsApp para Estoque
    $sql_check = "SELECT whatsapp_ativo, whatsapp_recebe_estoque FROM configuracoes_alertas LIMIT 1";
    $res_check = $conn->query($sql_check);
    if (!$res_check || !$row_check = $res_check->fetch_assoc()) return false;
    if ($row_check['whatsapp_ativo'] != 1 || $row_check['whatsapp_recebe_estoque'] != 1) return false;

    $phone = WHATSAPP_PHONE;
    $apikey = WHATSAPP_API_KEY;

    $msg = "📦 *ALERTA DE ESTOQUE CRÍTICO*\n\n";
    foreach ($itensCriticos as $item) {
        $msg .= "• *" . $item['categoria'] . "* (" . $item['tier'] . "): " . $item['qtd'] . " unid.\n";
    }
    $msg .= "\n📅 *Data:* " . date('d/m/Y H:i') . "\n";
    $msg .= "------------------------------------------\n";
    $msg .= "💼 _Asset Management System_";

    $url = "https://api.callmebot.com/whatsapp.php?phone=" . $phone . "&text=" . rawurlencode($msg) . "&apikey=" . $apikey;
    $options = ["http" => ["method" => "GET", "header" => "User-Agent: PHP\r\n", "timeout" => 15]];
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);

    return true;
}
?>
