<?php
session_start();
if (!session_start()) {
    die("Erro ao iniciar a sessão.");
}

include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Consulta ao banco de dados
    $query = "SELECT * FROM usuarios WHERE email = ? AND senha = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $email, $senha);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Usuário encontrado
        $user = $result->fetch_assoc();
        $_SESSION['id_usuarios'] = $user['id'];
        $_SESSION['email'] = $email;

        if (!isset($_SESSION['id_usuarios'])) {
            die("Erro: Sessão não armazenada.");
        }

        header("Location: inicio.php"); // Redireciona para a página inicial
        exit();
    } else {
        // Credenciais inválidas
        $_SESSION['error'] = "Usuário ou senha inválidos.";
        header("Location: login.php"); // Retorna à página de login
        exit();
    }
}
