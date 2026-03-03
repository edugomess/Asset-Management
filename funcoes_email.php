<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'libs/PHPMailer/Exception.php';
require 'libs/PHPMailer/PHPMailer.php';
require 'libs/PHPMailer/SMTP.php';
include_once 'config_notificacoes.php';

/**
 * Envia uma mensagem via WhatsApp usando a API CallMeBot.
 * 
 * @param string $mensagem Texto da mensagem (suporta markdown básico do WA).
 * @return bool Retorna true se a mensagem foi enfileirada com sucesso.
 */
function enviarWhatsApp($mensagem)
{
    if (!defined('WA_ATIVO') || !WA_ATIVO || WA_APIKEY === 'COLOQUE_SUA_API_KEY_AQUI') {
        return false;
    }

    // Garantir que a mensagem esteja em UTF-8 para evitar caracteres quebrados
    if (mb_detect_encoding($mensagem, 'UTF-8', true) === false) {
        $mensagem = mb_convert_encoding($mensagem, 'UTF-8');
    }

    $mensagem_encoded = urlencode($mensagem);
    $url = "https://api.callmebot.com/whatsapp.php?phone=" . str_replace("+", "", WA_TELEFONE) . "&text=" . $mensagem_encoded . "&apikey=" . WA_APIKEY;

    $context = stream_context_create([
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: PHPScript",
            "timeout" => 5 // Timeout de 5 segundos para não travar o sistema
        ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        error_log("Erro de conexão com a API do CallMeBot.");
        return false;
    }

    return (strpos($response, 'Message queued') !== false || strpos($response, 'Success') !== false);
}

/**
 * Envia um e-mail de alerta e um WhatsApp para um novo chamado criado.
 * 
 * @param int $chamado_id ID do chamado recém-criado.
 * @param mysqli $conn Conexão com o banco de dados.
 * @return bool Retorna true se ao menos uma notificação foi disparada com sucesso.
 */
function notificarNovoChamado($chamado_id, $conn)
{
    // Buscar detalhes do chamado e o nome do usuário
    $sql = "SELECT c.titulo, c.categoria, c.prioridade, c.descricao, u.nome, u.sobrenome 
            FROM chamados c 
            JOIN usuarios u ON c.usuario_id = u.id_usuarios 
            WHERE c.id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $chamado_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $nome_completo = trim($row['nome'] . ' ' . $row['sobrenome']);
        $titulo = $row['titulo'];
        $categoria = $row['categoria'];
        $prioridade = $row['prioridade'];
        $descricao = $row['descricao'];

        $email_sucesso = false;
        $wa_sucesso = false;

        // --- ENVIO DE WHATSAPP ---
        if (defined('WA_ATIVO') && WA_ATIVO) {
            $msg_wa = "🆕 *Novo Chamado Aberto (#$chamado_id)*\n\n";
            $msg_wa .= "*Título:* $titulo\n";
            $msg_wa .= "*Solicitante:* $nome_completo\n";
            $msg_wa .= "*Categoria:* $categoria\n";
            $msg_wa .= "*Prioridade:* $prioridade\n";

            $wa_sucesso = enviarWhatsApp($msg_wa);
        }

        // --- ENVIO DE E-MAIL (PHPMailer) ---
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE == 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom(SMTP_USER, 'Sistema Asset Mgt');
            $mail->addAddress(EMAIL_ADMIN);

            $mail->isHTML(true);
            $mail->Subject = EMAIL_ASSUNTO_NOVO_CHAMADO;

            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { width: 80%; margin: 20px auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px; background-color: #f9f9f9; }
                    .header { background-color: #2c404a; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
                    .content { padding: 20px; }
                    .field { margin-bottom: 10px; }
                    .label { font-weight: bold; color: #555; }
                    .value { color: #000; }
                    .footer { font-size: 12px; color: #777; margin-top: 20px; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Novo Chamado Registrado #$chamado_id</h2>
                    </div>
                    <div class='content'>
                        <div class='field'><span class='label'>Título:</span> <span class='value'>$titulo</span></div>
                        <div class='field'><span class='label'>Solicitante:</span> <span class='value'>$nome_completo</span></div>
                        <div class='field'><span class='label'>Categoria:</span> <span class='value'>$categoria</span></div>
                        <div class='field'><span class='label'>Prioridade:</span> <span class='value'>$prioridade</span></div>
                        <hr>
                        <div class='field'><span class='label'>Descrição:</span></div>
                        <div class='value' style='white-space: pre-wrap; background: #fff; padding: 10px; border: 1px solid #eee;'>$descricao</div>
                    </div>
                    <div class='footer'>
                        Este é um e-mail automático gerado pelo sistema Asset Mgt.
                    </div>
                </div>
            </body>
            </html>";

            $mail->send();
            $email_sucesso = true;
        } catch (Exception $e) {
            error_log("Erro e-mail (#$chamado_id): {$mail->ErrorInfo}");
        }

        return ($email_sucesso || $wa_sucesso);
    }

    return false;
}

/**
 * Dispara o processo de notificação em segundo plano (Assíncrono).
 * 
 * @param int $chamado_id ID do chamado.
 */
function dispararNotificacaoBackground($chamado_id)
{
    // Caminho para o executável do PHP no XAMPP
    $php_path = 'c:\\xampp\\php\\php.exe';
    $script_path = 'c:\\xampp\\htdocs\\processar_notificacao.php';

    // Comando para rodar em background no Windows (sem abrir janela)
    // start /B abre o processo em segundo plano
    $comando = "start /B $php_path $script_path $chamado_id";

    pclose(popen($comando, "r"));
}
?>