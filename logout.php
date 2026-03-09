<?php
/**
 * ENCERRAMENTO DE SESSÃO: logout.php
 * Limpa todos os dados da memória e finaliza a sessão do usuário de forma segura.
 */
session_start();

// 1. Limpa o array de sessão
$_SESSION = [];

// 2. Destrói fisicamente o arquivo de sessão no servidor
session_destroy();

// 3. Redireciona para a tela de login
header("Location: login.php");
exit;
?>