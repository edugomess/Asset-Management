<?php
/**
 * Script para processar notificações em segundo plano (CLI).
 * Este script deve ser chamado via linha de comando passando o ID e o Tipo.
 * Exemplo: php processar_notificacao.php 123 chamado
 * Exemplo: php processar_notificacao.php 45 manutencao
 */

if (php_sapi_name() !== 'cli') {
    die("Este script só pode ser executado via linha de comando.");
}

if ($argc < 2) {
    die("Uso: php processar_notificacao.php [ID] [TIPO]");
}

$id = (int) $argv[1];
$tipo = isset($argv[2]) ? $argv[2] : 'chamado';

require_once 'conexao.php';
require_once 'funcoes_email.php';

if ($tipo === 'manutencao') {
    // Alerta de Ativo em Manutenção
    notificarManutencao($id, $conn);
} else {
    // Alerta de Novo Chamado (Padrão)
    notificarNovoChamado($id, $conn);
}

$conn->close();
?>