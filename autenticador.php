<?php
/**
 * PROCESSADOR DE LOGIN: autenticador.php
 * Recebe as credenciais do formulário de login, valida contra o banco e inicia a sessão.
 */
session_start();
include 'conexao.php'; // Conexão com o banco

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // PREPARAÇÃO: Busca o usuário pelo e-mail (Prepara para evitar SQL Injection)
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        // VALIDAÇÃO: Compara a senha informada com o hash SHA-1 armazenado no banco
        if ($usuario['senha'] === sha1($senha)) {
            // SESSÃO: Armazena os dados básicos para uso em todo o sistema
            $_SESSION['id_usuarios'] = $usuario['id_usuarios'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['nome_usuario'] = $usuario['nome'] . ' ' . $usuario['sobrenome'];
            $_SESSION['foto_perfil'] = $usuario['foto_perfil'];
            $_SESSION['nivelUsuario'] = $usuario['nivelUsuario']; // Controle de Acesso (ACL)

            // STATUS CHAT: Definir como 'Disponível' ao logar
            mysqli_query($conn, "UPDATE usuarios SET chat_status = 'Disponível' WHERE id_usuarios = " . $usuario['id_usuarios']);

            // Redirecionamento bem-sucedido: Dashboard
            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('" . __('Senha incorreta. Tente novamente.') . "');
             window.location.href = 'login.php';</script>";
        }
    } else {
        echo "<script>alert('" . __('Email não encontrado.') . "');
        window.location.href = 'login.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>