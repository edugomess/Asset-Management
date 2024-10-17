<?php
session_start();
include 'conexao.php'; // Arquivo onde você conecta ao banco de dados

// Se o usuário já está logado, redirecione-o para o dashboard
if (isset($_SESSION['id_usuarios'])) {
    header("Location: dashboard.php");
    exit();
}

// Verifique se o formulário de login foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture os dados do formulário
    $usuario = $_POST['email'];
    $senha = $_POST['senha'];

    // Proteja contra SQL Injection e verifique as credenciais no banco de dados
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND senha = ?");
    $stmt->bind_param("ss", $usuario, $senha);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifique se o usuário existe e as credenciais estão corretas
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $id_do_usuario = $user['id'];

        // Se as credenciais são válidas, defina a sessão e redirecione
        $_SESSION['id_usuarios'] = $id_do_usuario; // Armazene o ID do usuário na sessão
        header("Location: dashboard.php"); // Redirecione para a página principal
        exit();
    } else {
        echo "Login ou senha inválidos";
    }

    $stmt->close();
    $conn->close();
}
?>
