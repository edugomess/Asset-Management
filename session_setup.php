<?php
session_start();
include_once 'conexao.php';

// Try to set a long idle timeout in the database for the session user
$conn->query("UPDATE configuracoes_alertas SET idle_timeout_admin = 60, idle_timeout_minutos = 60");

$_SESSION['id_usuarios'] = 1;
$_SESSION['nivelUsuario'] = 'Admin';
$_SESSION['nome_usuario'] = 'Antigravity Test';
$_SESSION['last_activity'] = time();

echo "Session setup complete. Idle timeout set to 60 minutes. <a href='equipamentos.php'>Go to Equipamentos</a>";
?>
