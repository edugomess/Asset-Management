<?php
// Simular uma requisição POST para o backend
$_POST['message'] = 'Sugira 3 melhorias para o sistema de gestão de ativos';
// Mock de sessão para evitar erros
$_SESSION = [
    'id_usuarios' => 1,
    'nome_usuario' => 'Admin Teste'
];

include 'agent_backend.php';
?>