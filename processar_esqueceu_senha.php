<?php
/**
 * PROCESSADOR DE ESQUECEU A SENHA: processar_esqueceu_senha.php
 * Verifica e-mail, gera token e envia instruções de recuperação.
 */
include 'conexao.php';
include 'funcoes_email.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('E-mail inválido.'); window.history.back();</script>";
        exit();
    }

    // 1. Verificar se o e-mail existe
    $stmt = $conn->prepare("SELECT id_usuarios, nome FROM usuarios WHERE email = ? AND status = 'Ativo'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // 2. Gerar Token Seguro e Expiração (1h)
        $token = bin2hex(random_bytes(32));
        $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // 3. Salvar Token no Banco
        $updateStmt = $conn->prepare("UPDATE usuarios SET reset_token = ?, reset_token_expira = ? WHERE id_usuarios = ?");
        $updateStmt->bind_param("ssi", $token, $expira, $user['id_usuarios']);
        $updateStmt->execute();

        // 4. Enviar E-mail
        if (enviarEmailRecuperacao($email, $user['nome'], $token)) {
             echo "<script>alert('Instruções de recuperação enviadas para o seu e-mail.'); window.location.href = 'login.php';</script>";
        } else {
             echo "<script>alert('Erro ao enviar e-mail. Por favor, contate o administrador.'); window.location.href = 'login.php';</script>";
        }
    } else {
        // Por segurança, informar que o e-mail foi enviado mesmo se não existir (evita enumeração de usuários)
        // Mas para uso interno/familiarizado, costuma-se avisar se não existe.
        echo "<script>alert('Se este e-mail estiver cadastrado, você receberá as instruções em breve.'); window.location.href = 'login.php';</script>";
    }

    $stmt->close();
    $conn->close();
}

/**
 * Função auxiliar para enviar o e-mail de recuperação
 */
function enviarEmailRecuperacao($email, $nome, $token) {
    global $conn;
    
    // Obter protocolo e host dinamicamente para o link
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $link = "$protocol://$host/redefinir_senha.php?token=$token";

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        $mail->addAddress($email, $nome);

        $mail->isHTML(true);
        $mail->Subject = "Recuperação de Senha - Asset Management";

        $body = "<h2>Olá, $nome</h2>";
        $body .= "<p>Recebemos uma solicitação para redefinir a sua senha no sistema <strong>Asset Management</strong>.</p>";
        $body .= "<p>Clique no link abaixo para criar uma nova senha:</p>";
        $body .= "<p style='margin: 20px 0;'><a href='$link' style='background: #2c404a; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Redefinir Minha Senha</a></p>";
        $body .= "<p>Este link expirará em 1 hora.</p>";
        $body .= "<hr><p>Se você não solicitou isso, desconsidere este e-mail.</p>";

        $mail->Body = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro PHPMailer: " . $mail->ErrorInfo);
        return false;
    }
}
?>
