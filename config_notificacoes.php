<?php
/**
 * CONFIGURAÇÕES DE NOTIFICAÇÃO
 * Lê as credenciais SMTP do banco de dados (tabela configuracoes_smtp).
 * Caso a tabela ainda não exista ou esteja vazia, usa os valores padrão abaixo como fallback.
 */

// ── Valores padrão (fallback) ──────────────────────────────────────────────
$_smtp_defaults = [
    'smtp_host'      => 'smtp.gmail.com',
    'smtp_user'      => 'david.eduardo.du@gmail.com',
    'smtp_pass'      => '',
    'smtp_port'      => 587,
    'smtp_from_name' => 'ASSET MGT - ALERTA',
    'smtp_secure'    => 'tls',
];

// ── Tentar ler do banco de dados ──────────────────────────────────────────
$_smtp_row = null;
if (isset($conn) && $conn) {
    // Garantir que a tabela existe
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

    $res_smtp = $conn->query("SELECT * FROM configuracoes_smtp LIMIT 1");
    if ($res_smtp && $res_smtp->num_rows > 0) {
        $_smtp_row = $res_smtp->fetch_assoc();
    }
}

// ── Definir constantes (banco prevalece sobre fallback) ───────────────────
$_s = $_smtp_row ?? $_smtp_defaults;

if (!defined('SMTP_HOST'))      define('SMTP_HOST',      $_s['smtp_host']);
if (!defined('SMTP_USER'))      define('SMTP_USER',      $_s['smtp_user']);
if (!defined('SMTP_PASS'))      define('SMTP_PASS',      $_s['smtp_pass']);
if (!defined('SMTP_PORT'))      define('SMTP_PORT',      (int)$_s['smtp_port']);
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', $_s['smtp_from_name']);
if (!defined('SMTP_SECURE'))    define('SMTP_SECURE',    $_s['smtp_secure'] ?? 'tls');

// ── Outras constantes de notificação ──────────────────────────────────────

// Destinatário Padrão dos Alertas
if (!defined('EMAIL_ALERTA_DESTINO')) define('EMAIL_ALERTA_DESTINO', 'edugomess@icloud.com');

// Configurações de WhatsApp (CallMeBot)
if (!defined('WHATSAPP_PHONE'))   define('WHATSAPP_PHONE',   '5511968435543');
if (!defined('WHATSAPP_API_KEY')) define('WHATSAPP_API_KEY', '9912014');
