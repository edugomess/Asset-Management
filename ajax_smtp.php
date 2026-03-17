<?php
/**
 * AJAX Handler: Configuração SMTP
 * Ações: save (salva credenciais), test (testa conexão e envia e-mail de teste)
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'libs/PHPMailer/Exception.php';
require_once 'libs/PHPMailer/PHPMailer.php';
require_once 'libs/PHPMailer/SMTP.php';

include 'auth.php';
include 'conexao.php';

// Somente Admin pode alterar configurações SMTP
if ($_SESSION['nivelUsuario'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// ═══════════════════════════════════════════════
// Garantir que a tabela existe
// ═══════════════════════════════════════════════
$conn->query("CREATE TABLE IF NOT EXISTS configuracoes_smtp (
    id INT PRIMARY KEY AUTO_INCREMENT,
    smtp_host VARCHAR(255) NOT NULL DEFAULT 'smtp.gmail.com',
    smtp_user VARCHAR(255) NOT NULL DEFAULT '',
    smtp_pass VARCHAR(255) NOT NULL DEFAULT '',
    smtp_port INT NOT NULL DEFAULT 587,
    smtp_from_name VARCHAR(255) NOT NULL DEFAULT 'ASSET MGT - ALERTA',
    smtp_secure ENUM('tls','ssl') NOT NULL DEFAULT 'tls',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// ═══════════════════════════════════════════════
// SALVAR configurações SMTP
// ═══════════════════════════════════════════════
if ($action === 'save') {
    $host      = $conn->real_escape_string(trim($_POST['smtp_host'] ?? ''));
    $user      = $conn->real_escape_string(trim($_POST['smtp_user'] ?? ''));
    $pass_raw  = trim($_POST['smtp_pass'] ?? '');
    $port      = (int)($_POST['smtp_port'] ?? 587);
    $from_name = $conn->real_escape_string(trim($_POST['smtp_from_name'] ?? 'ASSET MGT - ALERTA'));
    $secure    = in_array($_POST['smtp_secure'] ?? 'tls', ['tls', 'ssl']) ? $_POST['smtp_secure'] : 'tls';

    // Se a senha vier vazia, manter a atual no banco
    $pass_sql = '';
    if ($pass_raw !== '') {
        $pass_safe = $conn->real_escape_string($pass_raw);
        $pass_sql  = ", smtp_pass = '$pass_safe'";
    }

    $check = $conn->query("SELECT id FROM configuracoes_smtp LIMIT 1");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $sql = "UPDATE configuracoes_smtp SET
                    smtp_host = '$host',
                    smtp_user = '$user'
                    $pass_sql,
                    smtp_port = $port,
                    smtp_from_name = '$from_name',
                    smtp_secure = '$secure'
                WHERE id = " . $row['id'];
    } else {
        $pass_safe = $conn->real_escape_string($pass_raw);
        $sql = "INSERT INTO configuracoes_smtp (smtp_host, smtp_user, smtp_pass, smtp_port, smtp_from_name, smtp_secure)
                VALUES ('$host', '$user', '$pass_safe', $port, '$from_name', '$secure')";
    }

    $ok = $conn->query($sql);
    echo json_encode(['success' => $ok, 'message' => $ok ? 'Configurações salvas com sucesso!' : $conn->error]);
    exit();
}

// ═══════════════════════════════════════════════
// TESTAR conexão SMTP
// ═══════════════════════════════════════════════
if ($action === 'test') {
    // Carregar configurações salvas (ou fallback do arquivo)
    $smtp = null;
    $res = $conn->query("SELECT * FROM configuracoes_smtp LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $smtp = $res->fetch_assoc();
    }

    if (!$smtp || empty($smtp['smtp_host']) || empty($smtp['smtp_user'])) {
        // Fallback para o arquivo
        require_once 'config_notificacoes.php';
        $smtp = [
            'smtp_host'      => SMTP_HOST,
            'smtp_user'      => SMTP_USER,
            'smtp_pass'      => SMTP_PASS,
            'smtp_port'      => SMTP_PORT,
            'smtp_from_name' => SMTP_FROM_NAME,
            'smtp_secure'    => 'tls',
        ];
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $smtp['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['smtp_user'];
        $mail->Password   = $smtp['smtp_pass'];
        $mail->SMTPSecure = ($smtp['smtp_secure'] === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)$smtp['smtp_port'];
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($smtp['smtp_user'], $smtp['smtp_from_name']);
        $mail->addAddress($smtp['smtp_user']); // Envia para o próprio remetente
        $mail->isHTML(true);
        $mail->Subject = 'Teste de Conexão SMTP - Asset MGT';
        $mail->Body    = '<h2>✅ Conexão SMTP bem-sucedida!</h2><p>Este é um e-mail de teste enviado pelo painel de configurações do <strong>Asset MGT</strong>.</p>';

        $mail->send();
        echo json_encode(['success' => true, 'message' => 'E-mail de teste enviado para ' . htmlspecialchars($smtp['smtp_user']) . '!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Falha na conexão: ' . $mail->ErrorInfo]);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
