<?php
/**
 * AJAX HANDLER: ajax_suporte.php
 * Processa o formulário de contato da página suporte.php e envia por e-mail.
 */
include 'auth.php';
include 'conexao.php';
include 'funcoes_email.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    exit;
}

// Higienização dos dados de entrada
$nome = isset($_POST['nome']) ? mysqli_real_escape_string($conn, $_POST['nome']) : '';
$email_usuario = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
$centro_custo_id = isset($_POST['centro_custo']) ? (int)$_POST['centro_custo'] : 0;
$mensagem_corpo = isset($_POST['mensagem']) ? mysqli_real_escape_string($conn, $_POST['mensagem']) : '';

if (empty($nome) || empty($email_usuario) || empty($mensagem_corpo)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, preencha todos os campos obrigatórios.']);
    exit;
}

// Busca o nome do centro de custo para o e-mail
$nome_cc = "Não informado";
if ($centro_custo_id > 0) {
    $sql_cc = "SELECT nomeSetor FROM centro_de_custo WHERE id_centro_de_custo = $centro_custo_id";
    $res_cc = mysqli_query($conn, $sql_cc);
    if ($res_cc && $row_cc = mysqli_fetch_assoc($res_cc)) {
        $nome_cc = $row_cc['nomeSetor'];
    }
}

// Configuração do PHPMailer (reutilizando a lógica de funcoes_email.php)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Configurações do Servidor
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';

    // Destinatário (Quem vai receber o pedido de suporte)
    $mail->setFrom(SMTP_USER, 'Portal de Suporte - Asset MGT');
    $mail->addAddress(EMAIL_ALERTA_DESTINO); // Definido em config_notificacoes.php
    $mail->addReplyTo($email_usuario, $nome);

    // Conteúdo do E-mail
    $mail->isHTML(true);
    $mail->Subject = "Novo Contato via Portal de Suporte: $nome";

    $body = "<h2>Nova Mensagem de Suporte</h2>";
    $body .= "<p><strong>Nome:</strong> $nome</p>";
    $body .= "<p><strong>E-mail:</strong> $email_usuario</p>";
    $body .= "<p><strong>Centro de Custo:</strong> $nome_cc</p>";
    $body .= "<p><strong>Mensagem:</strong><br>" . nl2br(htmlspecialchars($mensagem_corpo)) . "</p>";
    $body .= "<hr>";
    $body .= "<p><em>Enviado via suporte.php</em></p>";

    $mail->Body = $body;

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Mensagem enviada com sucesso!']);
} catch (Exception $e) {
    error_log("Erro ao enviar e-mail de suporte: {$mail->ErrorInfo}");
    echo json_encode(['success' => false, 'message' => 'Erro ao enviar e-mail. Por favor, tente novamente mais tarde.']);
}

mysqli_close($conn);
?>
