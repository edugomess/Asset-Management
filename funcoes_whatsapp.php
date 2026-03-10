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

    // Verificar se as notificações de WhatsApp e Chamados estão ativas globalmente + Prioridades e Tipos
    $sql_check = "SELECT whatsapp_ativo, chamados_ativo, whatsapp_recebe_chamados, 
                  whatsapp_prioridade_baixa, whatsapp_prioridade_media, whatsapp_prioridade_alta,
                  cat_incidente, cat_requisicao, cat_mudanca 
                  FROM configuracoes_alertas LIMIT 1";
    $res_check = $conn->query($sql_check);
    if ($res_check && $row_check = $res_check->fetch_assoc()) {
        if ($row_check['whatsapp_ativo'] != 1 || $row_check['chamados_ativo'] != 1 || $row_check['whatsapp_recebe_chamados'] != 1) {
            return false; // Notificações inativas
        }
    }

    // Buscar dados do chamado
    $sql = "SELECT c.*, u.nome as solicitante 
            FROM chamados c 
            LEFT JOIN usuarios u ON c.usuario_id = u.id_usuarios 
            WHERE c.id = $id_chamado";
    $res = $conn->query($sql);

    if (!$res || $res->num_rows == 0)
        return false;
    $chamado = $res->fetch_assoc();

    // Filtro de Prioridade
    $prioridadeOriginal = strtolower(trim($chamado['prioridade'] ?? ''));
    if ($prioridadeOriginal === 'baixa' && $row_check['whatsapp_prioridade_baixa'] != 1)
        return false;
    if (($prioridadeOriginal === 'média' || $prioridadeOriginal === 'media') && $row_check['whatsapp_prioridade_media'] != 1)
        return false;
    if ($prioridadeOriginal === 'alta' && $row_check['whatsapp_prioridade_alta'] != 1)
        return false;

    // Filtro de Categoria (Global)
    $tipoOriginal = strtolower(trim($chamado['categoria'] ?? ''));
    if (($tipoOriginal === 'incidente') && $row_check['cat_incidente'] != 1)
        return false;
    if (($tipoOriginal === 'mudança' || $tipoOriginal === 'mudanca') && $row_check['cat_mudanca'] != 1)
        return false;
    if (($tipoOriginal === 'requisição' || $tipoOriginal === 'requisicao') && $row_check['cat_requisicao'] != 1)
        return false;

    $phone = WHATSAPP_PHONE;
    $apikey = WHATSAPP_API_KEY;

    $mensagem = "\xe2\x95\x94\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x97\n";
    $mensagem .= "\xe2\x95\x91 \xf0\x9f\x86\x95 *NOVO CHAMADO ABERTO*  \xe2\x95\x91\n";
    $mensagem .= "\xe2\x95\x9a\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x9d\n\n";

    $mensagem .= "\xf0\x9f\x86\x94 *ID:* #" . $id_chamado . "\n";
    $mensagem .= "\xf0\x9f\x93\x9d *T\xc3\xadtulo:* " . $chamado['titulo'] . "\n";
    $mensagem .= "\xf0\x9f\x91\xa4 *Solicitante:* " . $chamado['solicitante'] . "\n";
    $mensagem .= "\xe2\x9a\xa1 *Prioridade:* " . strtoupper($chamado['prioridade']) . "\n";
    $mensagem .= "\xf0\x9f\x93\x84 *Descri\xc3\xa7\xc3\xa3o:* " . $chamado['descricao'] . "\n";
    $mensagem .= "\xf0\x9f\x93\x85 *Data:* " . date('d/m/Y H:i', strtotime($chamado['data_abertura'])) . "\n\n";

    $mensagem .= "------------------------------------------\n";
    $mensagem .= "\xf0\x9f\x92\xbc _Asset Management System_";

    $url = "https://api.callmebot.com/whatsapp.php?phone=" . $phone . "&text=" . rawurlencode($mensagem) . "&apikey=" . $apikey;

    // Usar context para evitar problemas de proxy/timeout
    $options = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: PHP\r\n",
            "timeout" => 10
        ]
    ];
    $context = stream_context_create($options);

    // Dispara o disparo (suprimindo erros de conexão para não travar o sistema)
    @file_get_contents($url, false, $context);

    return true;
}

function enviarWhatsAppManutencao($id_ativo, $observacoes = "")
{
    global $conn;

    // Verificar se as notificações de WhatsApp e Manutenção estão ativas globalmente
    $sql_check = "SELECT whatsapp_ativo, manutencao_ativo, whatsapp_recebe_manutencao FROM configuracoes_alertas LIMIT 1";
    $res_check = $conn->query($sql_check);
    if ($res_check && $row_check = $res_check->fetch_assoc()) {
        if ($row_check['whatsapp_ativo'] != 1 || $row_check['manutencao_ativo'] != 1 || $row_check['whatsapp_recebe_manutencao'] != 1) {
            return false; // Notificações inativas
        }
    }

    // Buscar dados do ativo
    $sql = "SELECT a.* FROM ativos a WHERE a.id_asset = $id_ativo";
    $res = $conn->query($sql);

    if (!$res || $res->num_rows == 0)
        return false;
    $ativo = $res->fetch_assoc();

    $phone = WHATSAPP_PHONE;
    $apikey = WHATSAPP_API_KEY;

    $mensagem = "\xe2\x95\x94\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x97\n";
    $mensagem .= "\xe2\x95\x91 \xf0\x9f\x9b\xa0\xef\xb8\x8f *ATIVO EM MANUTEN\xc3\x87\xc3\x83O* \xe2\x95\x91\n";
    $mensagem .= "\xe2\x95\x9a\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x90\xe2\x95\x9d\n\n";

    $mensagem .= "\xf0\x9f\x96\xa5\xef\xb8\x8f *Modelo:* " . $ativo['modelo'] . "\n";
    $mensagem .= "\xf0\x9f\x8f\xb7\xef\xb8\x8f *TAG:* " . $ativo['tag'] . "\n";
    $mensagem .= "\xf0\x9f\x93\x9d *Obs:* " . ($observacoes ?: "Nenhuma") . "\n";
    $mensagem .= "\xf0\x9f\x93\x85 *Data:* " . date('d/m/Y H:i') . "\n\n";

    $mensagem .= "------------------------------------------\n";
    $mensagem .= "\xf0\x9f\x92\xbc _Asset Management System_";

    $url = "https://api.callmebot.com/whatsapp.php?phone=" . $phone . "&text=" . rawurlencode($mensagem) . "&apikey=" . $apikey;

    $options = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: PHP\r\n",
            "timeout" => 10
        ]
    ];
    $context = stream_context_create($options);

    @file_get_contents($url, false, $context);

    return true;
}
