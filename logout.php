<?php
/**
 * ENCERRAMENTO DE SESSÃO: logout.php
 * Limpa todos os dados da memória e finaliza a sessão do usuário de forma segura.
 * SCRIPT DE LOGOUT: logout.php
 * Finaliza a sessão do usuário com segurança e limpa todos os dados temporários.
 */
 */
session_start();
include 'conexao.php';

if (isset($_SESSION['id_usuarios'])) {
    $uid = $_SESSION['id_usuarios'];
    mysqli_query($conn, "UPDATE usuarios SET chat_status = 'Offline' WHERE id_usuarios = $uid");
}

session_unset();
session_destroy();
header("Location: login.php");
exit();
?>