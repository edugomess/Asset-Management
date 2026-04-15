<?php
/**
 * PROCESSADOR DE ALERTAS EM SEGUNDO PLANO
 * Este script é chamado via CLI para disparar e-mails e WhatsApp sem bloquear a interface.
 */

if (php_sapi_name() !== 'cli') {
    die("Acesso negado. Este script deve ser executado via CLI.");
}

mb_internal_encoding("UTF-8");

require_once 'conexao.php';
require_once 'funcoes_email.php';
require_once 'funcoes_whatsapp.php';

// Argumentos: [tipo] [id_or_json] [extra]
$tipo = $argv[1] ?? '';
$arg2 = $argv[2] ?? ''; // ID (int) ou JSON (string)
$extra = $argv[3] ?? '';

if (!$tipo || !$arg2) {
    die("Parâmetros inválidos.");
}

if ($tipo === 'chamado') {
    $id = (int)$arg2;
    enviarAlertaNovoChamado($id);
    enviarWhatsAppNovoChamado($id);
} elseif ($tipo === 'manutencao') {
    $id = (int)$arg2;
    enviarAlertaManutencao($id, $extra);
    enviarWhatsAppManutencao($id, $extra);
} elseif ($tipo === 'estoque') {
    $itensCriticos = json_decode($arg2, true);
    if (!empty($itensCriticos)) {
        enviarAlertaEstoque($itensCriticos);
        enviarWhatsAppEstoque($itensCriticos);
    }
}
?>
