<?php
session_start();
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Prepara a consulta para evitar SQL Injection
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        
        // Verifica a senha usando SHA-1
        if ($usuario['senha'] === sha1($senha)) {
            // Armazena informações na sessão
            $_SESSION['id_usuarios'] = $usuario['id'];
            $_SESSION['email'] = $usuario['email'];
            // Redireciona para a página inicial ou dashboard
            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('Senha incorreta. Tente novamente.');
             window.location.href = 'login.php';</script>";
        }
    } else {
        echo "<script>alert('Email não encontrado.');
        window.location.href = 'login.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
