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

// Argumentos: [tipo] [id] [extra]
$tipo = $argv[1] ?? '';
$id = (int) ($argv[2] ?? 0);
$extra = $argv[3] ?? '';

if (!$tipo || !$id) {
    die("Parâmetros inválidos.");
}

if ($tipo === 'chamado') {
    enviarAlertaNovoChamado($id);
    enviarWhatsAppNovoChamado($id);
} elseif ($tipo === 'manutencao') {
    enviarAlertaManutencao($id, $extra);
    enviarWhatsAppManutencao($id, $extra);
}
