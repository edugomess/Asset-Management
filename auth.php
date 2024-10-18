
<?php
session_start();

// Se o usuário já está logado, redirecione-o para o dashboard
if (isset($_SESSION['id_usuarios'])) {
    header("Location: dashboard.php");
    exit();
}

// Código de autenticação do login
if ($login_valido) {
    $_SESSION['id_usuarios'] = $id_do_usuario; // Armazene o ID do usuário na sessão
    header("Location: dashboard.php"); // Redirecione para a página principal
    exit();
} else {
    echo "Login ou senha inválidos";
}
?>