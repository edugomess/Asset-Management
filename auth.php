<?php
session_start();

// Verifique se o usuário está logado
if (!isset($_SESSION['id_usuarios'])) {
    // Se não estiver logado, redirecione para a página de login
    header("Location: login.php");
    exit();
}

// Se o nome do usuário não estiver na sessão, busque no banco de dados
if (!isset($_SESSION['nome_usuario'])) {
    include_once 'conexao.php';
    $id_usuario_sessao = $_SESSION['id_usuarios'];
    $stmt_nome = $conn->prepare("SELECT nome, sobrenome, foto_perfil FROM usuarios WHERE id_usuarios = ?");
    $stmt_nome->bind_param("i", $id_usuario_sessao);
    $stmt_nome->execute();
    $result_nome = $stmt_nome->get_result();
    if ($row_nome = $result_nome->fetch_assoc()) {
        $_SESSION['nome_usuario'] = $row_nome['nome'] . ' ' . $row_nome['sobrenome'];
        $_SESSION['foto_perfil'] = $row_nome['foto_perfil'];
    }
    else {
        $_SESSION['nome_usuario'] = 'Usuário';
    }
    $stmt_nome->close();
}
?>
