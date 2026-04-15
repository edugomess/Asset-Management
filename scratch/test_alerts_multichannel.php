<?php
require_once 'conexao.php';
require_once 'config_notificacoes.php';
require_once 'funcoes_email.php';
require_once 'funcoes_whatsapp.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "--- INICIANDO TESTE DE ALERTAS MULTI-CANAL ---\n\n";

// 1. TESTE DE EMAIL
echo "[EMAIL] Preparando envio...\n";
$queryUsers = "SELECT u.nome, u.email FROM alertas_usuarios au
               JOIN usuarios u ON au.usuario_id = u.id_usuarios
               WHERE u.status = 'Ativo'";
$resUsers = $conn->query($queryUsers);

if ($resUsers && $resUsers->num_rows > 0) {
    $recipients = [];
    while ($u = $resUsers->fetch_assoc()) {
        $recipients[] = $u['email'];
        echo " - Agendado para: " . $u['nome'] . " (" . $u['email'] . ")\n";
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        foreach ($recipients as $email) {
            $mail->addAddress($email);
        }

        $mail->isHTML(true);
        $mail->Subject = "ALERTA DE TESTE: Sistema de Gestão de Ativos";
        $body = "<h2>Teste de Notificação Multi-canal</h2>";
        $body .= "<p>Este é um disparo de teste para validar a configuração de notificações por E-mail.</p>";
        $body .= "<p><strong>Data/Hora do Disparo:</strong> " . date('d/m/Y H:i:s') . "</p>";
        $body .= "<hr><p>Solicitado por: Antigravity/User Support</p>";
        
        $mail->Body = $body;
        $mail->send();
        echo "[EMAIL] Sucesso: E-mail enviado para " . count($recipients) . " destinatários.\n";
    } catch (Exception $e) {
        echo "[EMAIL] Erro: " . $mail->ErrorInfo . "\n";
    }
} else {
    echo "[EMAIL] Nenhum destinatário ativo encontrado na tabela alertas_usuarios.\n";
}

echo "\n";

// 2. TESTE DE WHATSAPP
echo "[WHATSAPP] Preparando envio para " . WHATSAPP_PHONE . "...\n";

$msg = "\xf0\x9f\x91\xa2 *TESTE DE SISTEMA*\n\n";
$msg .= "Olá! Este é um alerta de teste disparado pelo Sistema de Gestão de Ativos para validar o canal WhatsApp.\n\n";
$msg .= "\xf0\x9f\x93\x85 *Data:* " . date('d/m/Y H:i:s') . "\n";
$msg .= "----------------------------\n";
$msg .= "Canal: CallMeBot API\n";

$url = "https://api.callmebot.com/whatsapp.php?phone=" . WHATSAPP_PHONE . "&text=" . rawurlencode($msg) . "&apikey=" . WHATSAPP_API_KEY;

$options = ["http" => ["method" => "GET", "header" => "User-Agent: PHP\r\n", "timeout" => 15]];
$context = stream_context_create($options);
$response = @file_get_contents($url, false, $context);

if ($response !== false) {
    echo "[WHATSAPP] Sucesso: Mensagem enviada.\n";
} else {
    echo "[WHATSAPP] Erro: Falha ao conectar com a API CallMeBot.\n";
}

echo "\n--- FIM DO TESTE ---";
?>
