<?php
require_once 'conexao.php';

$email = 'tester@asset.mgt';
$senha = sha1('admin');
$nome = 'AI';
$sobrenome = 'Tester';
$perfil = 'Admin';

$sql = "INSERT INTO usuarios (nome, sobrenome, email, senha, nivelUsuario, status) 
        VALUES ('$nome', '$sobrenome', '$email', '$senha', '$perfil', 'Ativo') 
        ON DUPLICATE KEY UPDATE senha='$senha', nivelUsuario='$perfil', status='Ativo'";

if (mysqli_query($conn, $sql)) {
    echo "Usuário $email criado/atualizado com sucesso.\n";
} else {
    echo "Erro: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>