<?php
/**
 * Script para processar notificações em segundo plano (CLI).
 * Este script deve ser chamado via linha de comando passando o ID do chamado.
 * Exemplo: php processar_notificacao.php 123
 */

if (php_sapi_name() !== 'cli') {
    die("Este script só pode ser executado via linha de comando.");
}

if ($argc < 2) {
    die("Uso: php processar_notificacao.php [ID_CHAMADO]");
}

$chamado_id = (int) $argv[1];

require_once 'conexao.php';
require_once 'funcoes_email.php';

// Chama a função que já contém a lógica de e-mail e WhatsApp
notificarNovoChamado($chamado_id, $conn);

$conn->close();
?>