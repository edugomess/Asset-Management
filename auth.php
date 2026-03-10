<?php
/**
 * CONTROLE DE SESSÃO: auth.php
 * Validação de login, permissões de usuário e gerenciamento de timeout por inatividade.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. GESTÃO DE TIMEOUT (Inatividade)
$idle_timeout = 600; // Padrão: 10 minutos (em segundos)

if (file_exists('conexao.php')) {
    include_once 'conexao.php';

    // Busca as configurações de expiração customizadas no banco de dados
    $res_config = $conn->query("SELECT idle_timeout_minutos, idle_timeout_admin, idle_timeout_suporte FROM configuracoes_alertas LIMIT 1");
    $idle_config = $res_config->fetch_assoc();

    // Define o tempo limite dinâmico de acordo com o nível de privilégio do usuário
    $nivel = $_SESSION['nivelUsuario'] ?? 'Usuário';
    $idle_timeout_minutos = $idle_config['idle_timeout_minutos'] ?? 10;

    if ($nivel === 'Admin' && isset($idle_config['idle_timeout_admin'])) {
        $idle_timeout_minutos = $idle_config['idle_timeout_admin'];
    } elseif ($nivel === 'Suporte' && isset($idle_config['idle_timeout_suporte'])) {
        $idle_timeout_minutos = $idle_config['idle_timeout_suporte'];
    }

    $idle_timeout = (int) $idle_timeout_minutos * 60; // Converte para segundos para comparação
}

// Verifica se houve inatividade prolongada desde a última ação registrada
if (isset($_SESSION['last_activity'])) {
    $session_life = time() - $_SESSION['last_activity'];
    if ($session_life > $idle_timeout) {
        // Encerra a sessão e apaga os dados se o tempo expirou
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=true");
        exit();
    }
}

// Registra o horário atual como a última atividade realizada pelo usuário
$_SESSION['last_activity'] = time();

// 2. VERIFICAÇÃO DE LOGIN: Bloqueia o acesso a usuários não autenticados
if (!isset($_SESSION['id_usuarios'])) {
    header("Location: login.php");
    exit();
}

// 3. CARREGAMENTO DE PERFIL: Sincroniza nome e foto do banco se não estiverem na sessão
if (!isset($_SESSION['nome_usuario'])) {
    include_once 'conexao.php';

    $id_usuario_sessao = $_SESSION['id_usuarios'];

    // Recupera dados atualizados do usuário logado
    $stmt_nome = $conn->prepare("SELECT nome, sobrenome, foto_perfil FROM usuarios WHERE id_usuarios = ?");

    if ($stmt_nome) {
        $stmt_nome->bind_param("i", $id_usuario_sessao);
        $stmt_nome->execute();
        $result_nome = $stmt_nome->get_result();

        if ($row_nome = $result_nome->fetch_assoc()) {
            // Consolida o nome completo e armazena o caminho da foto de perfil
            $_SESSION['nome_usuario'] = trim($row_nome['nome'] . ' ' . $row_nome['sobrenome']);
            $_SESSION['foto_perfil'] = $row_nome['foto_perfil'];
        } else {
            // Caso falhe na busca, define valores genéricos de segurança
            $_SESSION['nome_usuario'] = 'Usuário';
            $_SESSION['foto_perfil'] = 'default.png';
        }
        $stmt_nome->close();
    }
}
?>