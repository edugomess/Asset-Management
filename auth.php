<?php
// 1. Verifica se a sessão já foi iniciada para evitar o erro de "Notice"
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Verifique se o usuário está logado
if (!isset($_SESSION['id_usuarios'])) {
    // Redireciona e encerra a execução para segurança
    header("Location: login.php");
    exit();
}

// 3. Se o nome do usuário não estiver na sessão, busque no banco de dados
if (!isset($_SESSION['nome_usuario'])) {
    include_once 'conexao.php';

    $id_usuario_sessao = $_SESSION['id_usuarios'];

    // Preparação da query
    $stmt_nome = $conn->prepare("SELECT nome, sobrenome, foto_perfil FROM usuarios WHERE id_usuarios = ?");

    if ($stmt_nome) {
        $stmt_nome->bind_param("i", $id_usuario_sessao);
        $stmt_nome->execute();
        $result_nome = $stmt_nome->get_result();

        if ($row_nome = $result_nome->fetch_assoc()) {
            // Combina nome e sobrenome e limpa espaços extras
            $_SESSION['nome_usuario'] = trim($row_nome['nome'] . ' ' . $row_nome['sobrenome']);
            $_SESSION['foto_perfil'] = $row_nome['foto_perfil'];
        } else {
            $_SESSION['nome_usuario'] = 'Usuário';
            $_SESSION['foto_perfil'] = 'default.png'; // Recomendado ter um padrão
        }
        $stmt_nome->close();
    }
}
