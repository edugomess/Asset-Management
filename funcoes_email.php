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

    // 1. Verificar configurações GLOBAIS (Master Switches)
    $sql_check = "SELECT * FROM configuracoes_alertas LIMIT 1";
    $res_check = $conn->query($sql_check);
    if (!$res_check || !$row_check = $res_check->fetch_assoc()) return false;

    if (($row_check['email_ativo'] ?? 0) != 1) return false;

    // 2. Buscar dados do chamado
    $sql = "SELECT c.*, u.nome as solicitante, r.nome as responsavel
            FROM chamados c
            LEFT JOIN usuarios u ON c.usuario_id = u.id_usuarios
            LEFT JOIN usuarios r ON c.responsavel_id = r.id_usuarios
            WHERE c.id = $id_chamado";
    $res = $conn->query($sql);
    if (!$res || $res->num_rows == 0) return false;
    $chamado = $res->fetch_assoc();

    $prioridade = strtoupper(trim($chamado['prioridade'] ?? ''));
    $tipoOriginal = strtolower(trim($chamado['tipo'] ?? ''));

    // 3. Mapear para colunas do banco (P1-P4)
    $prioCol = "";
    if ($prioridade === 'P1') $prioCol = "prioridade_p1";
    else if ($prioridade === 'P2') $prioCol = "prioridade_p2";
    else if ($prioridade === 'P3') $prioCol = "prioridade_p3";
    else if ($prioridade === 'P4') $prioCol = "prioridade_p4";

    $tipoCol = "";
    if ($tipoOriginal === 'incidente') $tipoCol = "tipo_incidente";
    else if ($tipoOriginal === 'requisição' || $tipoOriginal === 'requisicao') $tipoCol = "tipo_requisicao";
    else if ($tipoOriginal === 'mudança' || $tipoOriginal === 'mudanca') $tipoCol = "tipo_mudanca";

    // 5. Buscar usuários que permitiram este alerta específico
    $queryUsers = "SELECT u.email FROM alertas_usuarios au
                   JOIN usuarios u ON au.usuario_id = u.id_usuarios
                   WHERE au.recebe_chamados = 1 AND u.status = 'Ativo'";

    if ($prioCol) $queryUsers .= " AND au." . $prioCol . " = 1";
    if ($tipoCol) $queryUsers .= " AND au." . $tipoCol . " = 1";

    $resUsers = $conn->query($queryUsers);
    if (!$resUsers || $resUsers->num_rows == 0) return false;

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
        while ($user = $resUsers->fetch_assoc()) {
            $mail->addBcc($user['email']);
        }

        $mail->isHTML(true);
        $mail->Subject = "Novo Chamado Aberto: #" . $id_chamado . " - " . $chamado['titulo'];

        $body = "<h2>Novo Chamado Detectado</h2>";
        $body .= "<p><strong>ID:</strong> #" . $id_chamado . "</p>";
        $body .= "<p><strong>Título:</strong> " . htmlspecialchars($chamado['titulo']) . "</p>";
        $body .= "<p><strong>Solicitante:</strong> " . htmlspecialchars($chamado['solicitante']) . "</p>";
        $body .= "<p><strong>Prioridade:</strong> " . $chamado['prioridade'] . "</p>";
        $body .= "<p><strong>Descrição:</strong><br>" . nl2br(htmlspecialchars($chamado['descricao'])) . "</p>";
        $body .= "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i', strtotime($chamado['data_abertura'])) . "</p>";
        $body .= "<hr><p><em>Este é um alerta automático do Sistema de Gestão de Ativos.</em></p>";

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

    $sql_check = "SELECT email_ativo FROM configuracoes_alertas LIMIT 1";
    $res_check = $conn->query($sql_check);
    if (!$res_check || (!$row_check = $res_check->fetch_assoc()) || $row_check['email_ativo'] != 1) return false;

    $sql = "SELECT a.* FROM ativos a WHERE a.id_asset = $id_ativo";
    $res = $conn->query($sql);
    if (!$res || $res->num_rows == 0) return false;
    $ativo = $res->fetch_assoc();

    $queryUsers = "SELECT u.email FROM alertas_usuarios au
                   JOIN usuarios u ON au.usuario_id = u.id_usuarios
                   WHERE au.recebe_manutencao = 1 AND u.status = 'Ativo'";
    $resUsers = $conn->query($queryUsers);
    if (!$resUsers || $resUsers->num_rows == 0) return false;

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
        while ($user = $resUsers->fetch_assoc()) {
            $mail->addBcc($user['email']);
        }

        $mail->isHTML(true);
        $mail->Subject = "Ativo em Manutenção: " . $ativo['modelo'] . " (" . $ativo['tag'] . ")";

        $body = "<h2>Alerta de Manutenção</h2>";
        $body .= "<p><strong>Ativo (Modelo):</strong> " . htmlspecialchars($ativo['modelo']) . "</p>";
        $body .= "<p><strong>TAG:</strong> " . htmlspecialchars($ativo['tag']) . "</p>";
        if (!empty($observacoes)) {
            $body .= "<p><strong>Observações:</strong> " . htmlspecialchars($observacoes) . "</p>";
        }
        $body .= "<hr><p><em>Este é um alerta automático do Sistema de Gestão de Ativos.</em></p>";

        $mail->Body = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail de alerta: {$mail->ErrorInfo}");
        return false;
    }
}

function enviarAlertaEstoque($itensCriticos)
{
    global $conn;
    if (empty($itensCriticos)) return false;

    $sql_check = "SELECT email_ativo FROM configuracoes_alertas LIMIT 1";
    $res_check = $conn->query($sql_check);
    if (!$res_check || (!$row_check = $res_check->fetch_assoc()) || $row_check['email_ativo'] != 1) return false;

    $queryUsers = "SELECT au.*, u.email FROM alertas_usuarios au
                   JOIN usuarios u ON au.usuario_id = u.id_usuarios
                   WHERE au.recebe_estoque = 1 AND u.status = 'Ativo'";
    $resUsers = $conn->query($queryUsers);
    if (!$resUsers || $resUsers->num_rows == 0) return false;

    $sucessoGeral = false;
    while ($user = $resUsers->fetch_assoc()) {
        $corpoItens = "";
        foreach ($itensCriticos as $item) {
            $t = $item['tier'];
            $tierKey = ($t === 'Tier 1') ? 'estoque_t1' : (($t === 'Tier 2') ? 'estoque_t2' : (($t === 'Tier 3') ? 'estoque_t3' : (($t === 'Tier 4') ? 'estoque_t4' : (($t === 'Infraestrutura') ? 'estoque_inf' : ''))));
            if (!$tierKey || ($user[$tierKey] ?? 1) == 1) {
                $corpoItens .= "<tr><td style='padding:8px; border-bottom:1px solid #eee;'>{$item['categoria']}</td><td style='padding:8px; border-bottom:1px solid #eee;'>{$item['tier']}</td><td style='padding:8px; border-bottom:1px solid #eee; color:red; font-weight:bold;'>{$item['qtd']}</td></tr>";
            }
        }
        if (empty($corpoItens)) continue;

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
            $mail->addAddress($user['email']);
            $mail->isHTML(true);
            $mail->Subject = "ALERTA CRÍTICO: Reposição de Estoque Necessária";
            $mail->Body = "<h2>Monitoramento de Estoque</h2><p>Os seguintes itens atingiram o limite mínimo:</p><table style='width:100%; border-collapse:collapse;'><tr style='background:#f8f9fc;'><th style='text-align:left; padding:8px;'>Categoria</th><th style='text-align:left; padding:8px;'>Tier</th><th style='text-align:left; padding:8px;'>Disponível</th></tr>$corpoItens</table><hr><p><em>Alerta automático do Sistema.</em></p>";
            $mail->send();
            $sucessoGeral = true;
        } catch (Exception $e) {}
    }
    return $sucessoGeral;
}
?>