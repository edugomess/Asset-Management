  <?php
session_start();

// Verifique se o usuário está logado
if (!isset($_SESSION['id_usuarios'])) {
    // Se não estiver logado, redirecione para a página de login
    header("Location: login.php");
    exit();
}
?>
