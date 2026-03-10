<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'libs/PHPMailer/Exception.php';
require_once 'libs/PHPMailer/PHPMailer.php';
require_once 'libs/PHPMailer/SMTP.php';
require_once 'config_notificacoes.php';

function enviarAlertaNovoChamado($id_chamado)
{
    global $conn;

    // Verificar se as notificações de E-mail e Chamados estão ativas globalmente
    $sql_check = "SELECT email_ativo, chamados_ativo, email_recebe_chamados FROM configuracoes_alertas LIMIT 1";
    $res_check = $conn->query($sql_check);
    if ($res_check && $row_check = $res_check->fetch_assoc()) {
        // Agora o email_recebe_chamados é o novo toggle global de e-mail p/ chamados
        if ($row_check['email_ativo'] != 1 || $row_check['chamados_ativo'] != 1) {
            return false; // Notificações inativas
        }
    }

    // Buscar dados do chamado
    $sql = "SELECT c.*, u.nome as solicitante, r.nome as responsavel
FROM chamados c
LEFT JOIN usuarios u ON c.usuario_id = u.id_usuarios
LEFT JOIN usuarios r ON c.responsavel_id = r.id_usuarios
WHERE c.id = $id_chamado";
    $res = $conn->query($sql);

    if (!$res || $res->num_rows == 0)
        return false;
    $chamado = $res->fetch_assoc();

    $prioridadeOriginal = strtolower(trim($chamado['prioridade'] ?? ''));
    $tipoOriginal = strtolower(trim($chamado['tipo'] ?? ''));

    // Normalizar prioridade para consulta na tabela alertas_usuarios
    $prioCol = "";
    if ($prioridadeOriginal === 'baixa')
        $prioCol = "prioridade_baixa";
    else if ($prioridadeOriginal === 'média' || $prioridadeOriginal === 'media')
        $prioCol = "prioridade_media";
    else if ($prioridadeOriginal === 'alta')
        $prioCol = "prioridade_alta";

    // Normalizar tipo para consulta na tabela alertas_usuarios
    $tipoCol = "";
    if ($tipoOriginal === 'incidente')
        $tipoCol = "tipo_incidente";
    else if ($tipoOriginal === 'requisição' || $tipoOriginal === 'requisicao')
        $tipoCol = "tipo_requisicao";
    else if ($tipoOriginal === 'mudança' || $tipoOriginal === 'mudanca')
        $tipoCol = "tipo_mudanca";

    // Buscar usuários elegíveis na tabela alertas_usuarios
    $queryUsers = "SELECT u.email FROM alertas_usuarios au
JOIN usuarios u ON au.usuario_id = u.id_usuarios
WHERE au.recebe_chamados = 1 AND u.status = 'Ativo'";

    if ($prioCol)
        $queryUsers .= " AND au." . $prioCol . " = 1";
    if ($tipoCol)
        $queryUsers .= " AND au." . $tipoCol . " = 1";

    $resUsers = $conn->query($queryUsers);
    if (!$resUsers || $resUsers->num_rows == 0)
        return false; // Ninguém configurado para receber

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

        // Destinatários
        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);

        while ($user = $resUsers->fetch_assoc()) {
            // Usa addAddress ou addBcc para não mostrar a lista de e-mails para todos
            $mail->addBcc($user['email']);
        }

        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = "Novo Chamado Aberto: #" . $id_chamado . " - " . $chamado['titulo'];

        $body = "<h2>Novo Chamado Detectado</h2>";
        $body .= "<p><strong>ID:</strong> #" . $id_chamado . "</p>";
        $body .= "<p><strong>Título:</strong> " . htmlspecialchars($chamado['titulo']) . "</p>";
        $body .= "<p><strong>Solicitante:</strong> " . htmlspecialchars($chamado['solicitante']) . "</p>";
        $body .= "<p><strong>Prioridade:</strong> " . $chamado['prioridade'] . "</p>";
        $body .= "<p><strong>Descrição:</strong><br>" . nl2br(htmlspecialchars($chamado['descricao'])) . "</p>";
        $body .= "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i', strtotime($chamado['data_abertura'])) . "</p>";
        $body .= "
<hr>";
        $body .= "<p><em>Este é um alerta automático do Sistema de Gestão de Ativos.</em></p>";

        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail de alerta: {$mail->ErrorInfo}");
        return false;
    }
}

function enviarAlertaManutencao($id_ativo, $observacoes = "")
{
    global $conn;

    // Verificar se as notificações de E-mail e Manutenção estão ativas globalmente
    $sql_check = "SELECT email_ativo, manutencao_ativo FROM configuracoes_alertas LIMIT 1";
    $res_check = $conn->query($sql_check);
    if ($res_check && $row_check = $res_check->fetch_assoc()) {
        if ($row_check['email_ativo'] != 1 || $row_check['manutencao_ativo'] != 1) {
            return false; // Notificações inativas globais
        }
    }

    // Buscar dados do ativo
    $sql = "SELECT a.* FROM ativos a WHERE a.id_asset = $id_ativo";
    $res = $conn->query($sql);

    if (!$res || $res->num_rows == 0)
        return false;
    $ativo = $res->fetch_assoc();

    // Buscar usuários elegíveis
    $queryUsers = "SELECT u.email FROM alertas_usuarios au
JOIN usuarios u ON au.usuario_id = u.id_usuarios
WHERE au.recebe_manutencao = 1 AND u.status = 'Ativo'";
    $resUsers = $conn->query($queryUsers);

    if (!$resUsers || $resUsers->num_rows == 0)
        return false;

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

        // Destinatários
        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);

        while ($user = $resUsers->fetch_assoc()) {
            $mail->addBcc($user['email']);
        }

        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = "Ativo em Manutenção: " . $ativo['modelo'] . " (" . $ativo['tag'] . ")";

        $body = "<h2>Alerta de Manutenção</h2>";
        $body .= "<p><strong>Ativo (Modelo):</strong> " . htmlspecialchars($ativo['modelo']) . "</p>";
        $body .= "<p><strong>TAG:</strong> " . htmlspecialchars($ativo['tag']) . "</p>";
        if (!empty($observacoes)) {
            $body .= "<p><strong>Observações:</strong> " . htmlspecialchars($observacoes) . "</p>";
        }
        $body .= "
<hr>";
        $body .= "<p><em>Este é um alerta automático do Sistema de Gestão de Ativos.</em></p>";

        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail de alerta: {$mail->ErrorInfo}");
        return false;
    }
}
?>