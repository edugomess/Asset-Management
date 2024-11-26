<?php
// Ativa a exibição de erros para depuração (remova em produção)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inicia a sessão
session_start();

// Inclui o arquivo de conexão com o banco de dados
include 'conexao.php';

// Verifica se o método da requisição é POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtém os dados enviados pelo formulário
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    // Verifica se os campos foram preenchidos
    if (empty($email) || empty($senha)) {
        $_SESSION['error'] = "Por favor, preencha todos os campos.";
        header("Location: login.php");
        exit();
    }

    try {
        // Consulta ao banco de dados
        $query = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email); // Evita SQL Injection
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verifica a senha (assumindo que está usando hashing no banco)
            if (password_verify($senha, $user['senha'])) {
                // Configura as variáveis de sessão
                $_SESSION['id_usuarios'] = $user['id'];
                $_SESSION['email'] = $user['email'];

                // Redireciona para a página inicial
                header("Location: inicio.php");
                exit();
            } else {
                // Senha inválida
                $_SESSION['error'] = "Usuário ou senha inválidos.";
                header("Location: login.php");
                exit();
            }
        } else {
            // E-mail não encontrado
            $_SESSION['error'] = "Usuário ou senha inválidos.";
            header("Location: login.php");
            exit();
        }
    } catch (Exception $e) {
        // Exibe mensagem de erro em caso de falha
        $_SESSION['error'] = "Erro ao processar o login: " . $e->getMessage();
        header("Location: login.php");
        exit();
    }
} else {
    // Redireciona para o login caso a página seja acessada diretamente
    header("Location: login.php");
    exit();
}
?>
