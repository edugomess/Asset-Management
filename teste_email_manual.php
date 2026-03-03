<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'libs/PHPMailer/Exception.php';
require 'libs/PHPMailer/PHPMailer.php';
require 'libs/PHPMailer/SMTP.php';
include_once 'config_notificacoes.php';
include_once 'conexao.php';

echo "<h2>Teste de Envio de E-mail via SMTP (Google/Gmail)</h2>";

if (SMTP_PASS === 'COLOQUE_SUA_SENHA_DE_APP_AQUI' || empty(SMTP_PASS)) {
    echo "<b style='color:orange'>Atenção:</b> Você precisa configurar sua <b>Senha de App do Google</b> no arquivo <code>config_notificacoes.php</code>.";
    exit;
}

// Pegar o último ID de chamado para o teste
$result = $conn->query("SELECT id FROM chamados ORDER BY id DESC LIMIT 1");
if ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    echo "Iniciando tentativa de envio via <b>" . SMTP_HOST . "</b>...<br>";

    $mail = new PHPMailer(true);

    try {
        // Habilitar debug detalhado para vermos o erro exato do Google
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = 'html';

        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_USER, 'Alerta Asset Mgt');
        $mail->addAddress(EMAIL_ADMIN);

        $mail->isHTML(true);
        $mail->Subject = "Teste de Autenticacao Gmail - Asset Mgt";
        $mail->Body = "Se você recebeu este e-mail, a configuração do Gmail está 100% correta!";

        echo "<pre style='background: #eee; padding: 10px; border: 1px solid #ccc; max-height: 300px; overflow: auto;'>";
        $mail->send();
        echo "</pre>";
        echo "<b style='color:green; font-size: 1.2em;'>✅ SUCESSO!</b> O e-mail foi enviado corretamente.";
    } catch (Exception $e) {
        echo "</pre>";
        echo "<br><b style='color:red; font-size: 1.2em;'>❌ FALHA NO ENVIO</b><br>";
        echo "<b>Erro Técnico:</b> {$mail->ErrorInfo}<br>";
        echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeeba; margin-top: 20px;'>";
        echo "<h4>Como resolver:</h4>";
        echo "1. <b>Senha de App:</b> O Google não aceita sua senha normal. Você deve gerar uma de 16 letras em <a href='https://myaccount.google.com/apppasswords' target='_blank'>myaccount.google.com/apppasswords</a>.<br>";
        echo "2. <b>2FA:</b> Certifique-se que a 'Verificação em duas etapas' está ativada na sua conta Google.<br>";
        echo "</div>";
    }
} else {
    echo "Nenhum chamado encontrado no banco de dados para testar.";
}

$conn->close();
?>