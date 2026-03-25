<?php
session_start();
$_SESSION['user_id'] = 1; // MOCK
$_SESSION['user_nome'] = 'Mock';
$_SESSION['user_funcao'] = 'Mock';
$_SESSION['user_nivel'] = 'Admin';
ob_start();
include 'equipamentos.php';
$html = ob_get_clean();
file_put_contents('debug_equip.html', $html);
echo "HTML salvo em debug_equip.html";
?>
