<?php
session_start();
$_SESSION['id_usuarios'] = 1; // Assuming id 1 is admin
$_SESSION['nome_usuario'] = 'Admin Test';
$_SESSION['nivelUsuario'] = 'Admin';
$_SESSION['email'] = 'marcos.morais@mgt.com';
header("Location: configuracoes.php");
exit();
