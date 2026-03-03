<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'libs/PHPMailer/Exception.php';
require 'libs/PHPMailer/PHPMailer.php';
require 'libs/PHPMailer/SMTP.php';
include_once 'config_notificacoes.php';

/**
 * Envia um e-mail de alerta para um novo chamado criado usando PHPMailer (SMTP).
 * 
 * @param int $chamado_id ID do chamado recém-criado.
 * @param mysqli $conn Conexão com o banco de dados.
 * @return bool Retorna true se o envio foi bem-sucedido.
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
        $mail = new PHPMailer(true);

        try {
            // Configurações do Servidor
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE == 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';

            // Destinatários
            $mail->setFrom(SMTP_USER, 'Sistema de Chamados Asset Mgt');
            $mail->addAddress(EMAIL_ADMIN);

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = EMAIL_ASSUNTO_NOVO_CHAMADO;

            $nome_completo = trim($row['nome'] . ' ' . $row['sobrenome']);
            $titulo = $row['titulo'];
            $categoria = $row['categoria'];
            $prioridade = $row['prioridade'];
            $descricao = $row['descricao'];

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
            return true;
        } catch (Exception $e) {
            // Em caso de erro, podemos logar em um arquivo se necessário
            error_log("Erro ao enviar e-mail: {$mail->ErrorInfo}");
            return false;
        }
    }

    return false;
}
?>