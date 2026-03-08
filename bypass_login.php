<?php
session_start();
$_SESSION['id_usuarios'] = 1;
$_SESSION['nome_usuario'] = 'Antigravity Debug';
$_SESSION['nivelUsuario'] = 'Admin'; // Fixed to match configuracoes.php check
$_SESSION['email_usuario'] = 'alfredo.borges@mgt.com';
header("Location: configuracoes.php");
?>