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

    // A API do CallMeBot funciona melhor com rawurlencode para caracteres PT-BR e espaços (%20)
    $mensagem_encoded = rawurlencode($mensagem);
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

        // --- BUSCAR CONFIGURAÇÕES DE ALERTA DO BANCO ---
        $res_alert = $conn->query("SELECT * FROM configuracoes_alertas LIMIT 1");
        $alert_sys = $res_alert->fetch_assoc();
        $wa_allow = $alert_sys['whatsapp_ativo'] ?? 1;
        $email_allow = $alert_sys['email_ativo'] ?? 1;
        $chamados_allow = $alert_sys['chamados_ativo'] ?? 1;
        $wa_chamados_allow = $alert_sys['whatsapp_recebe_chamados'] ?? 1;

        if (!$chamados_allow) {
            return false;
        }

        $email_destino = $alert_sys['email_destino'] ?? (defined('EMAIL_ADMIN') ? EMAIL_ADMIN : '');

        // Mapear categoria para coluna do banco (lidando com acentuação e encoding)
        $cat_col = "";
        $categoria_clean = mb_strtolower($categoria, 'UTF-8');

        if (strpos($categoria_clean, 'incidente') !== false)
            $cat_col = "cat_incidente";
        elseif (strpos($categoria_clean, 'mudança') !== false || strpos($categoria_clean, 'mudanca') !== false)
            $cat_col = "cat_mudanca";
        elseif (strpos($categoria_clean, 'requisição') !== false || strpos($categoria_clean, 'requisicao') !== false)
            $cat_col = "cat_requisicao";

        // Se a categoria está ativa globalmente
        $is_cat_allowed_global = true;
        if (!empty($cat_col) && isset($alert_sys[$cat_col]) && $alert_sys[$cat_col] == 0) {
            $is_cat_allowed_global = false;
        }

        if (!$is_cat_allowed_global) {
            // Se cair aqui, a categoria foi explicitamente desativada no mestre (global)
            return false;
        }

        // --- ENVIO DE WHATSAPP ---
        if ($wa_allow && $wa_chamados_allow && defined('WA_ATIVO') && WA_ATIVO) {
            // Mapear prioridade para coluna do banco
            $wa_priority_col = "whatsapp_prioridade_baixa";
            if ($prioridade == 'Alta')
                $wa_priority_col = "whatsapp_prioridade_alta";
            elseif ($prioridade == 'Média')
                $wa_priority_col = "whatsapp_prioridade_media";

            $should_send_wa = $alert_sys[$wa_priority_col] ?? 1;

            if ($should_send_wa) {
                $msg_wa = "🆕 *Novo Chamado Aberto (#$chamado_id)*\n\n";
                $msg_wa .= "*Título:* $titulo\n";
                $msg_wa .= "*Solicitante:* $nome_completo\n";
                $msg_wa .= "*Categoria:* $categoria\n";
                $msg_wa .= "*Prioridade:* $prioridade\n";

                $wa_sucesso = enviarWhatsApp($msg_wa);
            }
        }

        // --- ENVIO DE E-MAIL (PHPMailer) ---
        if ($email_allow) {
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

                // Mapear prioridade para coluna do banco (robusto)
                $priority_col = "prioridade_baixa";
                $pri_clean = mb_strtolower($prioridade, 'UTF-8');
                if (strpos($pri_clean, 'alta') !== false)
                    $priority_col = "prioridade_alta";
                elseif (strpos($pri_clean, 'média') !== false || strpos($pri_clean, 'media') !== false)
                    $priority_col = "prioridade_media";

                // Buscar apenas destinatários que desejam receber esta prioridade, este tipo de evento e esta categoria
                $where_cat = !empty($cat_col) ? " AND d.$cat_col = 1" : "";
                $sql_emails = "SELECT u.email 
                              FROM destinatarios_alertas d 
                              JOIN usuarios u ON d.usuario_id = u.id_usuarios
                              WHERE d.$priority_col = 1 AND d.recebe_chamados = 1 $where_cat";
                $res_emails = $conn->query($sql_emails);
                $has_recipients = false;
                $emails_list = [];
                while ($dest_email = $res_emails->fetch_assoc()) {
                    $mail->addAddress($dest_email['email']);
                    $has_recipients = true;
                    $emails_list[] = $dest_email['email'];
                }

                // Fallback para o e-mail antigo se a lista estiver vazia
                if (!$has_recipients && !empty($email_destino)) {
                    $mail->addAddress($email_destino);
                }

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
        }

        return ($email_sucesso || $wa_sucesso);
    }

    return false;
}

/**
 * Envia um alerta de manutenção de ativo (E-mail + WhatsApp).
 * 
 * @param int $id_asset ID do ativo.
 * @param mysqli $conn Conexão com o banco de dados.
 * @return bool
 */
function notificarManutencao($id_asset, $conn)
{
    // Buscar detalhes do ativo e da última manutenção aberta
    $sql = "SELECT a.hostName, a.modelo, a.categoria, m.observacoes 
            FROM ativos a 
            JOIN manutencao m ON a.id_asset = m.id_asset 
            WHERE a.id_asset = ? 
            AND m.status_manutencao = 'Em Manutenção' 
            ORDER BY m.id_manutencao DESC LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt)
        return false;

    $stmt->bind_param("i", $id_asset);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $hostName = $row['hostName'];
        $modelo = $row['modelo'];
        $categoria = $row['categoria'];
        $obs = $row['observacoes'] ?: 'Nenhuma observação informada.';

        $wa_sucesso = false;
        $email_sucesso = false;

        // --- BUSCAR CONFIGURAÇÕES DE ALERTA DO BANCO ---
        $res_alert = $conn->query("SELECT * FROM configuracoes_alertas LIMIT 1");
        $alert_sys = $res_alert->fetch_assoc();
        $wa_allow = $alert_sys['whatsapp_ativo'] ?? 1;
        $email_allow = $alert_sys['email_ativo'] ?? 1;
        $manutencao_allow = $alert_sys['manutencao_ativo'] ?? 1;
        $wa_manutencao_allow = $alert_sys['whatsapp_recebe_manutencao'] ?? 1;

        if (!$manutencao_allow) {
            return false;
        }

        $email_destino = $alert_sys['email_destino'] ?? (defined('EMAIL_ADMIN') ? EMAIL_ADMIN : '');

        // --- WHATSAPP ---
        if ($wa_allow && $wa_manutencao_allow && defined('WA_ATIVO') && WA_ATIVO) {
            $msg_wa = "🛠️ *Ativo em Manutenção*\n\n";
            $msg_wa .= "*Ativo:* $hostName ($categoria)\n";
            $msg_wa .= "*Modelo:* $modelo\n";
            $msg_wa .= "*Motivo:* $obs";
            $wa_sucesso = enviarWhatsApp($msg_wa);
        }

        // --- E-MAIL ---
        if ($email_allow) {
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

                // Buscar todos os destinatários da tabela dinâmica que recebem manutenção
                $res_emails = $conn->query("SELECT u.email 
                                          FROM destinatarios_alertas d 
                                          JOIN usuarios u ON d.usuario_id = u.id_usuarios
                                          WHERE d.recebe_manutencao = 1");
                $has_recipients = false;
                while ($dest_email = $res_emails->fetch_assoc()) {
                    $mail->addAddress($dest_email['email']);
                    $has_recipients = true;
                }

                if (!$has_recipients && !empty($email_destino)) {
                    $mail->addAddress($email_destino);
                }

                $mail->isHTML(true);
                $mail->Subject = "[ALERTA] Ativo enviado para Manutenção - $hostName";

                $mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='background: #f84c4c; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0;'>
                    <h2>🛠️ Alerta de Manutenção</h2>
                </div>
                <div style='padding: 20px; border: 1px solid #ddd; background: #fff;'>
                    <p><strong>Ativo:</strong> $hostName ($categoria)</p>
                    <p><strong>Modelo:</strong> $modelo</p>
                    <p><strong>Observações:</strong> $obs</p>
                </div>
            </body>
            </html>";

                $mail->send();
                $email_sucesso = true;
            } catch (Exception $e) {
                error_log("Erro e-mail manutenção (#$id_asset): {$mail->ErrorInfo}");
            }
        }

        return ($email_sucesso || $wa_sucesso);
    }
    return false;
}

/**
 * Dispara o processo de notificação em segundo plano.
 * 
 * @param int $id ID do registro (chamado ou ativo).
 * @param string $tipo Tipo da notificação ('chamado' ou 'manutencao').
 */
function dispararNotificacaoBackground($id, $tipo = 'chamado')
{
    // Caminho para o executável do PHP no XAMPP
    $php_path = 'c:\\xampp\\php\\php.exe';
    $script_path = 'c:\\xampp\\htdocs\\processar_notificacao.php';

    // Agora passamos o tipo como terceiro argumento
    $comando = "start /B $php_path $script_path $id $tipo";

    pclose(popen($comando, "r"));
}
?>