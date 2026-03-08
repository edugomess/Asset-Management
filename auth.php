<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar timeout de inatividade (Busca do banco de dados, padrão 10 minutos)
$idle_timeout = 600; // Default
if (file_exists('conexao.php')) {
    include_once 'conexao.php';
    // Obter o timeout configurado do banco de dados (idle_timeout_minutos, admin ou suporte)
    $res_config = $conn->query("SELECT idle_timeout_minutos, idle_timeout_admin, idle_timeout_suporte FROM configuracoes_alertas LIMIT 1");
    $alert_config = $res_config->fetch_assoc();

    // Determinar o timeout com base no nível do usuário
    // Certifique-se de que 'nivelUsuario' está definido na sessão, caso contrário, use 'Usuário' como padrão
    $nivel = $_SESSION['nivelUsuario'] ?? 'Usuário';
    $idle_timeout_minutos = $alert_config['idle_timeout_minutos'] ?? 10; // Default global do banco de dados

    if ($nivel === 'Admin' && isset($alert_config['idle_timeout_admin'])) {
        $idle_timeout_minutos = $alert_config['idle_timeout_admin'];
    } elseif ($nivel === 'Suporte' && isset($alert_config['idle_timeout_suporte'])) {
        $idle_timeout_minutos = $alert_config['idle_timeout_suporte'];
    }

    $idle_timeout = (int) $idle_timeout_minutos * 60; // Converter para segundos
}

if (isset($_SESSION['last_activity'])) {
    $session_life = time() - $_SESSION['last_activity'];
    if ($session_life > $idle_timeout) {
        // Encerra a sessão se o tempo de inatividade for maior que o configurado
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=true");
        exit();
    }
}
// Atualiza o registro da última atividade
$_SESSION['last_activity'] = time();

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