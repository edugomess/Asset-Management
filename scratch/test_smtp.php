<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../config_notificacoes.php';

echo "--- Iniciando Teste de SMTP ---\n";
echo "Host: " . SMTP_HOST . "\n";
echo "User: " . SMTP_USER . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "Secure: " . SMTP_SECURE . "\n";
echo "Pass: " . (empty(SMTP_PASS) ? "VAZIA (ERRO PROVAVEL)" : "DEFINIDA") . "\n";
echo "-------------------------------\n\n";

$mail = new PHPMailer(true);

try {
    // Configurações do Servidor
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; 
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = (SMTP_SECURE === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    // Destinatários
    $mail->setFrom(SMTP_USER, 'Teste Asset MGT');
    $mail->addAddress(SMTP_USER); // Envia para si mesmo

    // Conteúdo
    $mail->isHTML(true);
    $mail->Subject = 'Teste de Conexao CLI';
    $mail->Body    = 'Teste realizado via script de diagnostico.';

    echo "Tentando enviar...\n";
    $mail->send();
    echo "\n[OK] E-mail enviado com sucesso!\n";
} catch (Exception $e) {
    echo "\n[ERRO] Falha ao enviar: {$mail->ErrorInfo}\n";
}
