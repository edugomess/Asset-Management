<?php
/**
 * ENCERRAMENTO DE SESSÃO: logout.php
 * Limpa todos os dados da memória e finaliza a sessão do usuário de forma segura.
 * SCRIPT DE LOGOUT: logout.php
 * Finaliza a sessão do usuário com segurança e limpa todos os dados temporários.
 */
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>