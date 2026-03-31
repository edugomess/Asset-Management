<?php
session_start();
$_SESSION['id_usuarios'] = 1; 
$_SESSION['nivelUsuario'] = 'Admin';
$_SESSION['nome_usuario'] = 'Admin Test';
$_SESSION['last_activity'] = time();
header("Location: index.php");
exit();
?>
