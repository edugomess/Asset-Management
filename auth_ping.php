<?php
/**
 * MONITOR DE SESSÃO: auth_ping.php
 * Endpoint leve utilizado pelo front-end para manter a sessão ativa 
 * e verificar o status da comunicação com o servidor.
 */
include 'auth.php'; // Garante que o ping também respeite e valide a sessão ativa
echo json_encode(['status' => 'success', 'timestamp' => time()]);
?>