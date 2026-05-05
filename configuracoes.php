<?php
/**
 * PAINEL DE CONFIGURAÇÕES: configuracoes.php
 * Interface central para ajustes de SLA, regras de depreciação de ativos, canais de alerta e segurança de sessão.
 */
include 'auth.php';    // Validação de sessão obrigatória
include 'conexao.php'; // Conexão com o banco de dados

// Restrição de acesso: Apenas Administrador pode acessar as configurações
if ($_SESSION['nivelUsuario'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

// === MIGRAÇÃO: Garante colunas de estoque na tabela de alertas ===
$conn->query("ALTER TABLE configuracoes_alertas ADD COLUMN IF NOT EXISTS estoque_threshold_t1 INT NOT NULL DEFAULT 3");
$conn->query("ALTER TABLE configuracoes_alertas ADD COLUMN IF NOT EXISTS estoque_threshold_t2 INT NOT NULL DEFAULT 3");
$conn->query("ALTER TABLE configuracoes_alertas ADD COLUMN IF NOT EXISTS estoque_threshold_t3 INT NOT NULL DEFAULT 3");
$conn->query("ALTER TABLE alertas_usuarios ADD COLUMN IF NOT EXISTS recebe_estoque TINYINT NOT NULL DEFAULT 0");
$conn->query("ALTER TABLE alertas_usuarios ADD COLUMN IF NOT EXISTS estoque_t1 TINYINT NOT NULL DEFAULT 1");
$conn->query("ALTER TABLE alertas_usuarios ADD COLUMN IF NOT EXISTS estoque_t2 TINYINT NOT NULL DEFAULT 1");
$conn->query("ALTER TABLE alertas_usuarios ADD COLUMN IF NOT EXISTS estoque_t3 TINYINT NOT NULL DEFAULT 1");
$conn->query("ALTER TABLE alertas_usuarios ADD COLUMN IF NOT EXISTS estoque_t4 TINYINT NOT NULL DEFAULT 1");
$conn->query("ALTER TABLE alertas_usuarios ADD COLUMN IF NOT EXISTS estoque_inf TINYINT NOT NULL DEFAULT 1");
$conn->query("ALTER TABLE configuracoes_alertas ADD COLUMN IF NOT EXISTS whatsapp_recebe_estoque TINYINT NOT NULL DEFAULT 0");

// === PROCESSAMENTO SMTP: Salva as credenciais do servidor de e-mail ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['smtp_config'])) {
    // Garantir tabela
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

    $host = $conn->real_escape_string(trim($_POST['smtp_host'] ?? ''));
    $user = $conn->real_escape_string(trim($_POST['smtp_user'] ?? ''));
    $pass_raw = trim($_POST['smtp_pass'] ?? '');
    $port = (int) ($_POST['smtp_port'] ?? 587);
    $from_name = $conn->real_escape_string(trim($_POST['smtp_from_name'] ?? 'ASSET MGT - ALERTA'));
    $secure = in_array($_POST['smtp_secure'] ?? 'tls', ['tls', 'ssl']) ? $_POST['smtp_secure'] : 'tls';

    $pass_sql = '';
    if ($pass_raw !== '') {
        $pass_safe = $conn->real_escape_string($pass_raw);
        $pass_sql = ", smtp_pass = '$pass_safe'";
    }

    $check = $conn->query("SELECT id FROM configuracoes_smtp LIMIT 1");
    if ($check && $check->num_rows > 0) {
        $r = $check->fetch_assoc();
        $sql = "UPDATE configuracoes_smtp SET smtp_host='$host', smtp_user='$user' $pass_sql, smtp_port=$port, smtp_from_name='$from_name', smtp_secure='$secure' WHERE id=" . $r['id'];
    } else {
        $pass_safe = $conn->real_escape_string($pass_raw);
        $sql = "INSERT INTO configuracoes_smtp (smtp_host,smtp_user,smtp_pass,smtp_port,smtp_from_name,smtp_secure) VALUES ('$host','$user','$pass_safe',$port,'$from_name','$secure')";
    }
    $success = $conn->query($sql);

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode(['success' => $success]);
    } else {
        header("Location: configuracoes.php?msg=" . ($success ? 'smtp_success' : 'error'));
    }
    exit();
}

// === PROCESSAMENTO DE SLA: Salva ou atualiza os tempos de resposta por categoria ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sla'])) {
    $success = true;
    foreach ($_POST['sla'] as $category => $time) {
        $category = mysqli_real_escape_string($conn, $category);
        $hours = (int) $time['hours'];
        $minutes = (int) $time['minutes'];
        $total_minutes = ($hours * 60) + $minutes;

        $sql = "INSERT INTO configuracoes_sla (categoria, tempo_sla_minutos) VALUES ('$category', $total_minutes) 
                ON DUPLICATE KEY UPDATE tempo_sla_minutos = $total_minutes";
        if (!mysqli_query($conn, $sql)) {
            $success = false;
        }
    }

    // === SLA DE PRIMEIRO ATENDIMENTO: Salva o tempo alvo global ===
    if (isset($_POST['sla_primeira_resposta_minutos'])) {
        $sla_pr = max(1, (int) $_POST['sla_primeira_resposta_minutos']);
        // Garante que a coluna existe antes de tentar salvar
        $conn->query("ALTER TABLE configuracoes_sla ADD COLUMN IF NOT EXISTS sla_primeira_resposta_minutos INT NOT NULL DEFAULT 10");
        // Atualiza em todas as linhas (é um valor global)
        if (!mysqli_query($conn, "UPDATE configuracoes_sla SET sla_primeira_resposta_minutos = $sla_pr")) {
            $success = false;
        }
    }

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode(['success' => $success]);
    } else {
        header("Location: configuracoes.php?msg=" . ($success ? "sla_success" : "error"));
    }
    exit();
}

// === PROCESSAMENTO DE DEPRECIAÇÃO: Configura taxas financeiras e regras de doação de ativos ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['depreciacao'])) {
    $taxa = floatval($_POST['depreciacao']['taxa']);
    $taxa_t1 = floatval($_POST['depreciacao']['taxa_t1'] ?? 10.00);
    $taxa_t2 = floatval($_POST['depreciacao']['taxa_t2'] ?? 10.00);
    $taxa_t3 = floatval($_POST['depreciacao']['taxa_t3'] ?? 10.00);
    $taxa_t4 = floatval($_POST['depreciacao']['taxa_t4'] ?? 10.00);
    $taxa_inf = floatval($_POST['depreciacao']['taxa_inf'] ?? 10.00);
    $periodo_anos = (int) $_POST['depreciacao']['periodo_anos'];
    $periodo_meses = (int) $_POST['depreciacao']['periodo_meses'];
    $elegivel = isset($_POST['depreciacao']['elegivel_doacao']) ? 1 : 0;
    $doacao_anos = (int) $_POST['depreciacao']['tempo_doacao_anos'];
    $doacao_meses = (int) $_POST['depreciacao']['tempo_doacao_meses'];

    $dest_t1 = mysqli_real_escape_string($conn, $_POST['depreciacao']['dest_tier1'] ?? 'Doação');
    $dest_t2 = mysqli_real_escape_string($conn, $_POST['depreciacao']['dest_tier2'] ?? 'Doação');
    $dest_t3 = mysqli_real_escape_string($conn, $_POST['depreciacao']['dest_tier3'] ?? 'Doação');
    $dest_t4 = mysqli_real_escape_string($conn, $_POST['depreciacao']['dest_tier4'] ?? 'Doação');
    $dest_inf = mysqli_real_escape_string($conn, $_POST['depreciacao']['dest_infraestrutura'] ?? 'Doação');
    $elegivel_leilao = isset($_POST['depreciacao']['elegivel_leilao']) ? 1 : 0;

    // Check if record exists
    $check = mysqli_query($conn, "SELECT id FROM configuracoes_depreciacao LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        $row_dep = mysqli_fetch_assoc($check);
        $sql_dep = "UPDATE configuracoes_depreciacao SET 
            taxa_depreciacao = $taxa, 
            taxa_tier1 = $taxa_t1,
            taxa_tier2 = $taxa_t2,
            taxa_tier3 = $taxa_t3,
            taxa_tier4 = $taxa_t4,
            taxa_infraestrutura = $taxa_inf,
            periodo_anos = $periodo_anos, 
            periodo_meses = $periodo_meses, 
            elegivel_doacao = $elegivel, 
            tempo_doacao_anos = $doacao_anos, 
            tempo_doacao_meses = $doacao_meses,
            destinacao_tier1 = '$dest_t1',
            destinacao_tier2 = '$dest_t2',
            destinacao_tier3 = '$dest_t3',
            destinacao_tier4 = '$dest_t4',
            destinacao_infraestrutura = '$dest_inf',
            elegivel_leilao = $elegivel_leilao
            WHERE id = " . $row_dep['id'];
    } else {
        $sql_dep = "INSERT INTO configuracoes_depreciacao (taxa_depreciacao, taxa_tier1, taxa_tier2, taxa_tier3, taxa_tier4, taxa_infraestrutura, periodo_anos, periodo_meses, elegivel_doacao, tempo_doacao_anos, tempo_doacao_meses, destinacao_tier1, destinacao_tier2, destinacao_tier3, destinacao_tier4, destinacao_infraestrutura, elegivel_leilao) 
            VALUES ($taxa, $taxa_t1, $taxa_t2, $taxa_t3, $taxa_t4, $taxa_inf, $periodo_anos, $periodo_meses, $elegivel, $doacao_anos, $doacao_meses, '$dest_t1', '$dest_t2', '$dest_t3', '$dest_t4', '$dest_inf', $elegivel_leilao)";
    }

    $success = mysqli_query($conn, $sql_dep);

    // Salvar elegibilidade por categoria
    $result_cats = mysqli_query($conn, "SELECT categoria FROM categoria");
    while ($cat_row = mysqli_fetch_assoc($result_cats)) {
        $cat_name = mysqli_real_escape_string($conn, $cat_row['categoria']);
        $cat_elegivel = isset($_POST['cat_doacao'][$cat_name]) ? 1 : 0;
        if (!mysqli_query($conn, "INSERT INTO categoria_doacao (categoria, elegivel_doacao) VALUES ('$cat_name', $cat_elegivel) ON DUPLICATE KEY UPDATE elegivel_doacao = $cat_elegivel")) {
            $success = false;
        }
    }

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode(['success' => $success]);
    } else {
        header("Location: configuracoes.php?msg=" . ($success ? "dep_success" : "error"));
    }
    exit();
}



// === PROCESSAMENTO DE IA: Configuração detalhada do Agente de IA ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ia_config'])) {
    $ia_chat = isset($_POST['ia_chat_ativo']) ? 1 : 0;
    $ia_chamados = isset($_POST['ia_chamados_ativo']) ? 1 : 0;
    $ia_preve = isset($_POST['ia_preve_ativo']) ? 1 : 0;

    // Atualização da Chave de API do Gemini no arquivo credentials.php
    if (isset($_POST['gemini_api_key'])) {
        $new_key = trim($_POST['gemini_api_key']);
        if (!empty($new_key)) {
            $cred_file = 'credentials.php';
            if (file_exists($cred_file)) {
                $content = file_get_contents($cred_file);
                $content = preg_replace("/define\(\s*'GEMINI_API_KEY'\s*,\s*'[^']*'\s*\);/", "define('GEMINI_API_KEY', '$new_key');", $content);
                file_put_contents($cred_file, $content);
            }
        }
    }

    $sql = "UPDATE configuracoes_alertas SET 
            ia_chat_ativo = $ia_chat, 
            ia_chamados_ativo = $ia_chamados, 
            ia_preve_ativo = $ia_preve 
            WHERE id = 1";

    $success = mysqli_query($conn, $sql);

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode(['success' => $success]);
    } else {
        header("Location: configuracoes.php?msg=" . ($success ? "ia_success" : "error"));
    }
    exit();
}

// === CONFIGURAÇÃO DE SEGURANÇA: Ajusta os tempos de expiração de sessão por nível de usuário ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['session_config'])) {
    $idle_timeout = (int) $_POST['idle_timeout'];
    $idle_timeout_admin = (int) $_POST['idle_timeout_admin'];
    $idle_timeout_suporte = (int) $_POST['idle_timeout_suporte'];

    $sql = "UPDATE configuracoes_alertas SET 
            idle_timeout_minutos = $idle_timeout,
            idle_timeout_admin = $idle_timeout_admin,
            idle_timeout_suporte = $idle_timeout_suporte
            WHERE id = 1";

    $success = mysqli_query($conn, $sql);

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode(['success' => $success]);
    } else {
        header("Location: configuracoes.php?msg=" . ($success ? "session_success" : "error"));
    }
    exit();
}

// === PROCESSAMENTO DE IDIOMA: Define o idioma padrão do sistema ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['idioma_config'])) {
    $idioma = $_POST['idioma'] ?? 'pt-BR';
    $idioma = mysqli_real_escape_string($conn, $idioma);

    $sql = "UPDATE configuracoes_alertas SET idioma = '$idioma' WHERE id = 1";
    $success = mysqli_query($conn, $sql);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['idioma'] = $idioma;

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode(['success' => $success]);
    } else {
        header("Location: configuracoes.php?msg=" . ($success ? "idioma_success" : "error"));
    }
    exit();
}

// === PROCESSAMENTO DE ESTOQUE: Configura alertas de reposição por Tier ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['estoque_config'])) {
    $t1 = max(0, (int) ($_POST['threshold_t1'] ?? 3));
    $t2 = max(0, (int) ($_POST['threshold_t2'] ?? 3));
    $t3 = max(0, (int) ($_POST['threshold_t3'] ?? 3));
    $t4 = max(0, (int) ($_POST['threshold_t4'] ?? 3));
    $inf = max(0, (int) ($_POST['threshold_inf'] ?? 3));

    $sql = "UPDATE configuracoes_alertas SET 
            estoque_threshold_t1 = $t1,
            estoque_threshold_t2 = $t2,
            estoque_threshold_t3 = $t3,
            estoque_threshold_t4 = $t4,
            estoque_threshold_inf = $inf
            WHERE id = 1";

    $success = mysqli_query($conn, $sql);

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode(['success' => $success]);
    } else {
        header("Location: configuracoes.php?msg=" . ($success ? "estoque_success" : "error"));
    }
    exit();
}

// === PROCESSAMENTO DE ALERTAS: Salva as configurações de canais e tipos de alerta ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['config_alertas'])) {
    $whatsapp = isset($_POST['whatsapp_ativo']) ? 1 : 0;
    $email = isset($_POST['email_ativo']) ? 1 : 0;
    $wa_chamados = isset($_POST['whatsapp_recebe_chamados']) ? 1 : 0;
    $wa_manutencao = isset($_POST['whatsapp_recebe_manutencao']) ? 1 : 0;
    $wa_estoque = isset($_POST['whatsapp_recebe_estoque']) ? 1 : 0;
    $wa_p1 = isset($_POST['whatsapp_prioridade_p1']) ? 1 : 0;
    $wa_p2 = isset($_POST['whatsapp_prioridade_p2']) ? 1 : 0;
    $wa_p3 = isset($_POST['whatsapp_prioridade_p3']) ? 1 : 0;
    $wa_p4 = isset($_POST['whatsapp_prioridade_p4']) ? 1 : 0;
    $wa_incidente = isset($_POST['whatsapp_tipo_incidente']) ? 1 : 0;
    $wa_requisicao = isset($_POST['whatsapp_tipo_requisicao']) ? 1 : 0;
    $wa_mudanca = isset($_POST['whatsapp_tipo_mudanca']) ? 1 : 0;

    $email_incidente = isset($_POST['email_tipo_incidente']) ? 1 : 0;
    $email_requisicao = isset($_POST['email_tipo_requisicao']) ? 1 : 0;
    $email_mudanca = isset($_POST['email_tipo_mudanca']) ? 1 : 0;

    $sql = "UPDATE configuracoes_alertas SET 
            whatsapp_ativo = $whatsapp,
            email_ativo = $email,
            whatsapp_recebe_chamados = $wa_chamados,
            whatsapp_recebe_manutencao = $wa_manutencao,
            whatsapp_recebe_estoque = $wa_estoque,
            whatsapp_prioridade_p1 = $wa_p1,
            whatsapp_prioridade_p2 = $wa_p2,
            whatsapp_prioridade_p3 = $wa_p3,
            whatsapp_prioridade_p4 = $wa_p4,
            cat_incidente = $wa_incidente,
            cat_requisicao = $wa_requisicao,
            cat_mudanca = $wa_mudanca,
            email_tipo_incidente = $email_incidente,
            email_tipo_requisicao = $email_requisicao,
            email_tipo_mudanca = $email_mudanca
            WHERE id = 1";

    // Check if the request is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

    if (mysqli_query($conn, $sql)) {
        if ($isAjax) {
            echo json_encode(['success' => true]);
        } else {
            header("Location: configuracoes.php?msg=success");
        }
    } else {
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        } else {
            header("Location: configuracoes.php?msg=error&detail=" . urlencode(mysqli_error($conn)));
        }
    }
    exit();
}

// === PROCESSAMENTO DE LOGOTIPO: Salva o logo para os relatórios ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logo_upload'])) {
    $success = false;
    $message = __('Erro no upload');
    $new_path = '';

    if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === 0) {
        $allowed = ['png', 'jpg', 'jpeg', 'gif'];
        $filename = $_FILES['logo_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $upload_dir = 'assets/img/logos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_name = 'logo_report_' . time() . '.' . $ext;
            $target = $upload_dir . $new_name;

            if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $target)) {
                $sql = "UPDATE configuracoes_alertas SET logo_path = '$target' WHERE id = 1";
                if (mysqli_query($conn, $sql)) {
                    $success = true;
                    $message = __('Logo atualizado com sucesso!');
                    $new_path = $target;
                }
            }
        } else {
            $message = __('Formato não permitido');
        }
    }

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode(['success' => $success, 'message' => $message, 'path' => $new_path]);
    } else {
        header("Location: configuracoes.php?msg=" . ($success ? "logo_success" : "error"));
    }
    exit();
}

// === PROCESSAMENTO DO DASHBOARD: Configura quais cards aparecerão na página inicial ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dashboard_config'])) {
    $cards = $_POST['selected_cards'] ?? [];
    // Limite de 8 cards
    if (count($cards) > 8) {
        $cards = array_slice($cards, 0, 8);
    }
    $cards_json = mysqli_real_escape_string($conn, json_encode($cards));

    $sql = "UPDATE configuracoes_alertas SET dashboard_cards = '$cards_json' WHERE id = 1";
    $success = mysqli_query($conn, $sql);

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    if ($isAjax) {
        echo json_encode(['success' => $success]);
    } else {
        header("Location: configuracoes.php?msg=" . ($success ? "dashboard_success" : "error"));
    }
    exit();
}


// === COLETA DE DADOS ATUAIS: Busca as configurações salvas para preencher o formulário ===
$configs = [];
$sla_primeira_resposta_minutos = 10; // Padrão: 10 minutos
// Garante que a coluna existe silenciosamente
$conn->query("ALTER TABLE configuracoes_sla ADD COLUMN IF NOT EXISTS sla_primeira_resposta_minutos INT NOT NULL DEFAULT 10");
$result = mysqli_query($conn, "SELECT * FROM configuracoes_sla");
while ($row = mysqli_fetch_assoc($result)) {
    $configs[$row['categoria']] = $row['tempo_sla_minutos'];
    // Pega o valor global de primeiro atendimento (é o mesmo em todas as linhas)
    if (!empty($row['sla_primeira_resposta_minutos'])) {
        $sla_primeira_resposta_minutos = (int) $row['sla_primeira_resposta_minutos'];
    }
}

// Valores padrão corrigidos (Incidente = 6h/360min como solicitado)
$defaults = ['Incidente' => 360, 'Mudança' => 1440, 'Requisição' => 2880];
foreach ($defaults as $cat => $val) {
    if (!isset($configs[$cat])) {
        $configs[$cat] = $val;
    }
}

// Fetch depreciation settings
$dep_config = [
    'taxa_depreciacao' => 10.00,
    'taxa_tier1' => 10.00,
    'taxa_tier2' => 10.00,
    'taxa_tier3' => 10.00,
    'taxa_tier4' => 10.00,
    'taxa_infraestrutura' => 10.00,
    'periodo_anos' => 1,
    'periodo_meses' => 0,
    'elegivel_doacao' => 0,
    'tempo_doacao_anos' => 5,
    'tempo_doacao_meses' => 0,
    'destinacao_tier1' => 'Doação',
    'destinacao_tier2' => 'Doação',
    'destinacao_tier3' => 'Doação',
    'destinacao_tier4' => 'Doação',
    'destinacao_infraestrutura' => 'Doação',
    'elegivel_leilao' => 0
];
$result_dep = mysqli_query($conn, "SELECT * FROM configuracoes_depreciacao LIMIT 1");
if ($result_dep && mysqli_num_rows($result_dep) > 0) {
    $dep_config = mysqli_fetch_assoc($result_dep);
}

// Fetch alert settings
$alert_config = ['whatsapp_ativo' => 1, 'email_ativo' => 1];
$result_alert = mysqli_query($conn, "SELECT * FROM configuracoes_alertas LIMIT 1");
if ($result_alert && mysqli_num_rows($result_alert) > 0) {
    $alert_config = mysqli_fetch_assoc($result_alert);
}

// Fetch dashboard card settings
$dashboard_cards = [];
if (!empty($alert_config['dashboard_cards'])) {
    $dashboard_cards = json_decode($alert_config['dashboard_cards'], true) ?: [];
}

// Fetch categories for dashboard selection
$all_categories = [];
$res_cat = mysqli_query($conn, "SELECT categoria FROM categoria ORDER BY categoria ASC");
if ($res_cat) {
    while ($row = mysqli_fetch_assoc($res_cat)) {
        $all_categories[] = $row['categoria'];
    }
}
$all_statuses = ['Disponível', 'Em uso', 'Em manutenção'];

// Fetch available software licenses for dashboard selection
$all_licenses = [];
$res_lic = mysqli_query($conn, "SELECT DISTINCT software FROM licencas WHERE software IS NOT NULL AND software != '' ORDER BY software ASC");
if ($res_lic) {
    while ($row = mysqli_fetch_assoc($res_lic)) {
        $all_licenses[] = $row['software'];
    }
}

// Fetch per-category donation eligibility
$cat_doacao = [];
$result_cat_doacao = mysqli_query($conn, "SELECT c.categoria, COALESCE(cd.elegivel_doacao, 1) as elegivel_doacao FROM categoria c LEFT JOIN categoria_doacao cd ON c.categoria = cd.categoria ORDER BY c.categoria ASC");
if ($result_cat_doacao) {
    while ($row_cd = mysqli_fetch_assoc($result_cat_doacao)) {
        $cat_doacao[$row_cd['categoria']] = $row_cd['elegivel_doacao'];
    }
}

// Fetch SMTP settings
$smtp_config = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_user' => '',
    'smtp_pass' => '',
    'smtp_port' => 587,
    'smtp_from_name' => 'ASSET MGT - ALERTA',
    'smtp_secure' => 'tls',
];
// Garantir tabela e buscar configurações
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
    $smtp_config = $res_smtp->fetch_assoc();
} else {
    // Se ainda não existe registro, pré-popular com os valores do arquivo legado
    require_once 'config_notificacoes.php';
    $smtp_config['smtp_host'] = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
    $smtp_config['smtp_user'] = defined('SMTP_USER') ? SMTP_USER : '';
    $smtp_config['smtp_port'] = defined('SMTP_PORT') ? SMTP_PORT : 587;
    $smtp_config['smtp_from_name'] = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'ASSET MGT - ALERTA';
}

/**
 * AUXILIAR DE FORMATAÇÃO: Converte minutos totais em um array de Horas e Minutos
 */
function getHoursAndMinutes($total_minutes)
{
    if ($total_minutes === null)
        return ['h' => 0, 'm' => 0];
    return [
        'h' => floor($total_minutes / 60),
        'm' => $total_minutes % 60
    ];
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Configurações'); ?> - Asset MGT</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=f3d9abe8d5aa7831c01bfaa2a1563712">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=41e93b37bc495fd67938799bb3a6adaf">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Animated-numbers-section.css?h=f70eceb0d9266e15c95f7e63479d6265">
    <link rel="stylesheet" href="/assets/css/Bootstrap-Image-Uploader.css?h=406ba72429389f6080fdb666c60fb216">
    <link rel="stylesheet" href="/assets/css/card-image-zoom-on-hover.css?h=82e6162bc70edfde8bfd14b57fdcb3f7">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet"
        href="/assets/css/Form-Select---Full-Date---Month-Day-Year.css?h=7b6a3c2cb7894fdb77bae43c70b92224">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightpick@1.3.4/css/lightpick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="/assets/css/Map-Clean.css?h=bdd15207233b27ebc7c6fc928c71b34c">
    <link rel="stylesheet" href="/assets/css/Modern-Contact-Form.css?h=af67b929d317df499a992472a9bb8fcc">
    <link rel="stylesheet"
        href="/assets/css/Multi-Select-Dropdown-by-Jigar-Mistry.css?h=28bd9d636c700fbf60086e2bcb002efb">
    <link rel="stylesheet"
        href="/assets/css/Password-Strenght-Checker---Ambrodu-1.css?h=1af6ac373aa34a3b40f3d87a4f494eaf">
    <link rel="stylesheet"
        href="/assets/css/Password-Strenght-Checker---Ambrodu.css?h=5818638767f362b9d58a96550bd9a9a3">
    <link rel="stylesheet" href="/assets/css/Simple-footer-by-krissy.css?h=73316da5ae5ad6b51632cd2e5413f263">
    <link rel="stylesheet" href="/assets/css/TR-Form.css?h=ce0bc58b5b8027e2406229d460f4d895">
    <?php include 'sidebar_style.php'; ?>
    <style>
        /* Estilos dos Novos Cards de Notificação */
        .notification-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: none;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.1);
        }

        .notification-card:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .inactive-card {
            opacity: 0.6;
            filter: grayscale(100%);
            transition: all 0.3s ease;
        }

        .card-chamados {
            border-left: 4px solid #4e73df;
        }

        .card-manutencao {
            border-left: 4px solid #f6c23e;
        }

        .card-whatsapp,
        .card-email {
            border-top: 4px solid #25d366;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .card-email {
            border-top-color: #4e73df;
        }

        .card-whatsapp .card-body,
        .card-email .card-body {
            flex: 1;
        }

        .notif-icon-box {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            background: #f8f9fc;
            color: #5a5c69;
            font-size: 1.2rem;
        }

        .notif-info {
            flex-grow: 1;
        }

        .notif-title {
            font-weight: 700;
            color: #4e73df;
            margin-bottom: 2px;
            font-size: 0.95rem;
        }

        .card-manutencao .notif-title {
            color: #5a5c69;
        }

        .card-whatsapp .notif-title,
        .card-email .notif-title {
            color: #fff;
        }

        .notif-desc {
            font-size: 0.9rem;
            color: #858796;
            margin: 0;
        }

        .card-header-custom {
            padding: 10px 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #fff;
            border-radius: 8px 8px 0 0;
        }

        .bg-whatsapp {
            background: #25d366;
        }

        .bg-email {
            background: #4e73df;
        }

        /* Estilos dos novos Badges de Notificação */
        .badge-checkbox {
            display: none;
        }

        .badge-label {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            transition: all 0.2s;
            margin-right: 5px;
            margin-bottom: 5px;
            opacity: 0.5; /* Visível mas suave quando desmarcado */
        }

        .badge-checkbox:checked+.badge-label {
            opacity: 1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .badge-icon-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 32px;
            border-radius: 6px;
            font-size: 1.2rem;
            color: #fff;
            cursor: pointer;
            transition: all 0.2s;
            margin-right: 8px;
            opacity: 0.5;
            background-color: #4e73df;
            /* default blue */
        }

        .badge-icon-label.bg-warning {
            background-color: #f6c23e !important;
        }

        .badge-checkbox:checked+.badge-icon-label {
            opacity: 1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .badge-alta {
            background-color: #e74a3b;
        }

        .badge-media {
            background-color: #f6c23e;
            color: #fff;
        }

        .badge-baixa {
            background-color: #1cc88a;
        }

        .badge-incidente {
            background-color: #0dcaf0;
        }

        .badge-mudanca {
            background-color: #6610f2;
        }

        .badge-requisicao {
            background-color: #858796;
        }

        /* Estilos Email Recipents */
        .recipient-list {
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid #e3e6f0;
            border-radius: 6px;
            background: #fff;
        }

        .recipient-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #e3e6f0;
        }

        .recipient-item:last-child {
            border-bottom: none;
        }

        .recipient-info {
            flex-grow: 1;
            min-width: 0;
        }

        .recipient-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .recipient-badge-group {
            display: flex;
            gap: 2px;
        }

        .mini-badge-btn {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            font-weight: bold;
            color: white;
            cursor: pointer;
            opacity: 0.5; /* Cores visíveis mas suaves quando inativo */
            transition: all 0.2s;
            border: none;
            padding: 0;
            outline: none !important;
        }

        .group-disabled {
            filter: grayscale(100%);
            opacity: 0.3 !important;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .mini-badge-btn.active {
            opacity: 1;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .mini-icon-btn {
            color: #b7b9cc;
            cursor: pointer;
            transition: color 0.2s;
            font-size: 1rem;
        }

        .mini-icon-btn.active.fa-ticket-alt {
            color: #4e73df;
        }

        .mini-icon-btn.active.fa-tools {
            color: #f6c23e;
        }

        .mini-icon-btn.active.fa-boxes {
            color: #1cc88a;
        }

        .mini-icon-btn.fa-times-circle:hover {
            color: #e74a3b;
            opacity: 1;
        }


        #autoSaveStatus {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #1cc88a;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            z-index: 9999;
            display: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .badge-priority {
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.65rem;
            padding: 0.3em 0.6em;
        }

        .badge-priority:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }

        .badge-inactive {
            background-color: #f8f9fc !important;
            color: #d1d3e2 !important;
            border: 1px dashed #d1d3e2 !important;
            opacity: 0.7;
        }

        .badge-priority.active {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .border-right {
                border-right: none !important;
                border-bottom: 1px solid #e3e6f0;
                margin-bottom: 20px;
                padding-bottom: 20px;
                padding-right: 0 !important;
            }
        }

        .recipient-list::-webkit-scrollbar {
            width: 6px;
        }

        .recipient-list::-webkit-scrollbar-thumb {
            background-color: #d1d3e2;
            border-radius: 10px;
        }

        .pointer {
            cursor: pointer;
        }

        /* Melhoria de Visibilidade das Abas */
        .nav-tabs {
            border-bottom: 2px solid #e3e6f0 !important;
            gap: 10px;
            padding: 5px 10px !important;
            overflow-x: auto;
            flex-wrap: nowrap;
            scrollbar-width: none;
            -ms-overflow-style: none;
            margin-bottom: 25px !important;
        }
        .nav-tabs::-webkit-scrollbar {
            display: none;
        }

        .nav-tabs .nav-link {
            border: none !important;
            color: #6e707e !important;
            padding: 12px 22px !important;
            border-radius: 12px !important;
            font-size: 0.88rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent !important;
            display: flex;
            align-items: center;
            white-space: nowrap;
            margin-bottom: 0 !important;
        }

        .nav-tabs .nav-link i {
            font-size: 1rem;
            margin-right: 12px;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            background: rgba(44, 64, 74, 0.05) !important;
            color: rgb(44, 64, 74) !important;
            transform: translateY(-1px);
        }

        .nav-tabs .nav-link:hover i {
            opacity: 1;
            transform: scale(1.1);
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, rgb(44, 64, 74) 0%, rgb(32, 44, 50) 100%) !important;
            color: #fff !important;
            box-shadow: 0 8px 20px rgba(44, 64, 74, 0.25);
            transform: translateY(-2px);
            font-weight: 700 !important;
        }

        .nav-tabs .nav-link.active i {
            color: #fff !important;
            opacity: 1;
            transform: scale(1.1);
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include 'sidebar_brand.php'; ?>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <?php include 'topbar.php'; ?>
                <div class="container-fluid">
                    <h3 class="text-dark mb-4"><i
                            class="fas fa-cogs mr-2 text-secondary"></i><?php echo __('Configurações do Sistema'); ?>
                    </h3>

                    <div id="autoSaveStatus"><i class="fas fa-check-circle mr-2"></i> <span
                            id="autoSaveMessage"><?php echo __('Alteração salva!'); ?></span></div>

                    <!-- Navegação por Abas -->
                    <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" id="branding-tab" data-toggle="tab" href="#branding" role="tab" aria-controls="branding" aria-selected="true">
                                <i class="fas fa-image mr-1"></i> Branding
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="geral-tab" data-toggle="tab" href="#geral" role="tab" aria-controls="geral" aria-selected="false">
                                <i class="fas fa-sliders-h mr-1"></i> <?php echo __('Geral'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="dashboard-tab" data-toggle="tab" href="#dashboard" role="tab" aria-controls="dashboard" aria-selected="false">
                                <i class="fas fa-th-large mr-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="whatsapp-tab" data-toggle="tab" href="#whatsapp" role="tab" aria-controls="whatsapp" aria-selected="false">
                                <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="smtp-tab" data-toggle="tab" href="#smtp" role="tab" aria-controls="smtp" aria-selected="false">
                                <i class="fas fa-server mr-1"></i> SMTP
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="recipients-tab" data-toggle="tab" href="#recipients" role="tab" aria-controls="recipients" aria-selected="false">
                                <i class="fas fa-users mr-1"></i> Recipients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="estoque-tab" data-toggle="tab" href="#estoque" role="tab" aria-controls="estoque" aria-selected="false">
                                <i class="fas fa-boxes mr-1"></i> Estoque
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="sla-tab" data-toggle="tab" href="#sla" role="tab" aria-controls="sla" aria-selected="false">
                                <i class="fas fa-clock mr-1"></i> SLA
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="depreciacao-tab" data-toggle="tab" href="#depreciacao" role="tab" aria-controls="depreciacao" aria-selected="false">
                                <i class="fas fa-percentage mr-1"></i> Depreciação
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="ia-tab" data-toggle="tab" href="#ia" role="tab" aria-controls="ia" aria-selected="false">
                                <i class="fas fa-robot mr-1"></i> IA
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content border-0" id="settingsTabContent">
                        
                        <!-- 1. BRANDING -->
                        <div class="tab-pane fade show active" id="branding" role="tabpanel" aria-labelledby="branding-tab">
                            <div class="card shadow mb-4" id="cardLogo">
                                <div class="card-header py-3">
                                    <h6 class="text-primary m-0 font-weight-bold">
                                        <i class="fas fa-image mr-2"></i><?php echo __('Personalização de Relatórios'); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-4">
                                        <?php echo __('Escolha o logotipo que aparecerá no cabeçalho de todos os relatórios gerados pelo sistema (PDF e Excel).'); ?>
                                    </p>

                                    <div class="row align-items-center">
                                        <div class="col-md-4 text-center mb-3 mb-md-0">
                                            <div class="p-3 border rounded bg-light d-inline-block">
                                                <img id="logoPreview"
                                                    src="<?php echo htmlspecialchars($alert_config['logo_path'] ?? 'dashboard/images/favicon.png'); ?>"
                                                    alt="Logo" style="max-height: 80px; max-width: 100%; object-fit: contain;">
                                            </div>
                                            <div class="mt-2 text-muted small"><?php echo __('Prévia do Logotipo'); ?></div>
                                        </div>
                                        <div class="col-md-8">
                                            <form id="formLogoUpload" enctype="multipart/form-data">
                                                <input type="hidden" name="logo_upload" value="1">
                                                <div class="form-group mb-3">
                                                    <label
                                                        class="small font-weight-bold text-gray-700"><?php echo __('Selecione uma imagem (PNG, JPG, GIF)'); ?></label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="logo_file"
                                                            name="logo_file" accept="image/*" required>
                                                        <label class="custom-file-label"
                                                            for="logo_file"><?php echo __('Escolher arquivo...'); ?></label>
                                                    </div>
                                                </div>
                                                <div class="text-right mt-3">
                                                    <button type="submit" class="btn btn-primary btn-sm" id="btnUploadLogo"
                                                        style="background: rgb(44,64,74); border-color: rgb(44,64,74);">
                                                        <i class="fas fa-save mr-1"></i><?php echo __('Salvar Logotipo'); ?>
                                                    </button>
                                                </div>
                                            </form>
                                            <div id="uploadStatus" class="mt-2 small"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. GERAL (Idioma + Sessão) -->
                        <div class="tab-pane fade" id="geral" role="tabpanel" aria-labelledby="geral-tab">
                            <!-- IDIOMA DO SISTEMA -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary m-0 font-weight-bold">
                                        <i class="fas fa-language mr-2"></i><?php echo __('Idioma do Sistema'); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="formIdioma" method="POST">
                                        <input type="hidden" name="idioma_config" value="1">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <label
                                                    class="form-label font-weight-bold"><?php echo __('Selecione o idioma padrão:'); ?></label>
                                                <select class="form-control" name="idioma">
                                                    <option value="pt-BR" <?php echo ($alert_config['idioma'] ?? 'pt-BR') == 'pt-BR' ? 'selected' : ''; ?>>Português (Brasil)</option>
                                                    <option value="en-US" <?php echo ($alert_config['idioma'] ?? 'pt-BR') == 'en-US' ? 'selected' : ''; ?>>English (United States)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 text-right mt-3 mt-md-0">
                                                <button type="submit" class="btn btn-primary btn-save-ajax"
                                                    style="background: rgb(44,64,74);">
                                                    <i class="fas fa-save mr-2"></i><?php echo __('Salvar Idioma'); ?>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- SESSÃO E SEGURANÇA -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary m-0 font-weight-bold">
                                        <i class="fas fa-user-shield mr-2"></i><?php echo __('Sessão e Segurança'); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="formSessao" method="POST">
                                        <input type="hidden" name="session_config" value="1">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label
                                                    class="form-label font-weight-bold"><?php echo __('Tempo Geral (Padrão)'); ?></label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="idle_timeout"
                                                        value="<?php echo $alert_config['idle_timeout_minutos'] ?? 10; ?>" min="1">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><?php echo __('minutos'); ?></span>
                                                    </div>
                                                </div>
                                                <small class="text-muted"><?php echo __('Logout para usuários comuns.'); ?></small>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label
                                                    class="form-label font-weight-bold text-primary"><?php echo __('Tempo para Administradores'); ?></label>
                                                <div class="input-group border-left-primary">
                                                    <input type="number" class="form-control" name="idle_timeout_admin"
                                                        value="<?php echo $alert_config['idle_timeout_admin'] ?? 10; ?>" min="1">
                                                    <div class="input-group-append">
                                                        <span
                                                            class="input-group-text bg-primary text-white"><?php echo __('minutos'); ?></span>
                                                    </div>
                                                </div>
                                                <small class="text-muted"><?php echo __('Logout para nível Admin.'); ?></small>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label
                                                    class="form-label font-weight-bold text-info"><?php echo __('Tempo para Suporte'); ?></label>
                                                <div class="input-group border-left-info">
                                                    <input type="number" class="form-control" name="idle_timeout_suporte"
                                                        value="<?php echo $alert_config['idle_timeout_suporte'] ?? 10; ?>" min="1">
                                                    <div class="input-group-append">
                                                        <span
                                                            class="input-group-text bg-info text-white"><?php echo __('minutos'); ?></span>
                                                    </div>
                                                </div>
                                                <small class="text-muted"><?php echo __('Logout para nível Suporte.'); ?></small>
                                            </div>
                                        </div>
                                        <div class="text-right mt-3">
                                            <button type="submit" class="btn btn-primary btn-save-ajax"
                                                style="background: rgb(44,64,74);"><?php echo __('Salvar Configurações de Sessão'); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- 3. DASHBOARD -->
                        <div class="tab-pane fade" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary m-0 font-weight-bold">
                                        <i class="fas fa-th-large mr-2"></i><?php echo __('Configuração do Dashboard (Cards)'); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="formDashboard" method="POST">
                                        <input type="hidden" name="dashboard_config" value="1">
                                        <p class="text-muted small mb-4">
                                            <?php echo __('Selecione até 8 categorias, status ou licenças para exibir como cards de resumo na página inicial.'); ?>
                                        </p>

                                        <div class="row">
                                            <div class="col-md-5 border-right">
                                                <h6 class="font-weight-bold mb-3 text-primary">
                                                    <?php echo __('Categorias de Ativos'); ?></h6>
                                                <div class="row">
                                                    <?php foreach ($all_categories as $cat): ?>
                                                        <div class="col-sm-6 mb-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox"
                                                                    class="custom-control-input dashboard-card-checkbox"
                                                                    id="db_cat_<?php echo md5($cat); ?>" name="selected_cards[]"
                                                                    value="cat:<?php echo htmlspecialchars($cat); ?>" <?php echo in_array("cat:$cat", $dashboard_cards) ? 'checked' : ''; ?>>
                                                                <label class="custom-control-label small"
                                                                    for="db_cat_<?php echo md5($cat); ?>">
                                                                    <?php echo htmlspecialchars($cat); ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-3 border-right">
                                                <h6 class="font-weight-bold mb-3 text-success"><?php echo __('Status'); ?></h6>
                                                <div class="row">
                                                    <?php foreach ($all_statuses as $st): ?>
                                                        <div class="col-sm-12 mb-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox"
                                                                    class="custom-control-input dashboard-card-checkbox"
                                                                    id="db_st_<?php echo md5($st); ?>" name="selected_cards[]"
                                                                    value="st:<?php echo htmlspecialchars($st); ?>" <?php echo in_array("st:$st", $dashboard_cards) ? 'checked' : ''; ?>>
                                                                <label class="custom-control-label small"
                                                                    for="db_st_<?php echo md5($st); ?>">
                                                                    <?php echo __($st); ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <h6 class="font-weight-bold mb-3 text-info"><?php echo __('Licenças'); ?></h6>
                                                <div class="row">
                                                    <?php foreach ($all_licenses as $lic): ?>
                                                        <div class="col-sm-12 mb-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox"
                                                                    class="custom-control-input dashboard-card-checkbox"
                                                                    id="db_lic_<?php echo md5($lic); ?>" name="selected_cards[]"
                                                                    value="lic:<?php echo htmlspecialchars($lic); ?>" <?php echo in_array("lic:$lic", $dashboard_cards) ? 'checked' : ''; ?>>
                                                                <label class="custom-control-label small"
                                                                    for="db_lic_<?php echo md5($lic); ?>">
                                                                    <?php echo htmlspecialchars($lic); ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-right mt-4 border-top pt-3">
                                            <span id="cardCountInfo" class="mr-3 small text-muted">
                                                <?php echo __('Selecionados:'); ?> <span id="currentCardCount">0</span>/8
                                            </span>
                                            <button type="submit" class="btn btn-primary btn-save-ajax"
                                                style="background: rgb(44,64,74);">
                                                <i
                                                    class="fas fa-save mr-2"></i><?php echo __('Salvar Configuração do Dashboard'); ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- 4. WHATSAPP -->
                        <div class="tab-pane fade" id="whatsapp" role="tabpanel" aria-labelledby="whatsapp-tab">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary m-0 font-weight-bold">
                                        <i class="fab fa-whatsapp mr-2"></i><?php echo __('Notificações via WhatsApp'); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="formWhatsAppConfig">
                                        <input type="hidden" name="config_alertas" value="1">
                                        <div class="card shadow-sm border-0 card-whatsapp mb-0">
                                            <div class="card-header-custom bg-whatsapp">
                                                <div class="d-flex align-items-center">
                                                    <i class="fab fa-whatsapp mr-2"></i>
                                                    <span
                                                        class="font-weight-bold"><?php echo __('Configurações de Envio'); ?></span>
                                                </div>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input channel-toggle"
                                                        id="toggle_whatsapp" name="whatsapp_ativo" value="1" <?php echo (($alert_config['whatsapp_ativo'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="toggle_whatsapp"></label>
                                                </div>
                                            </div>
                                            <div class="card-body bg-light p-3 border-top pb-4" style="border-radius: 0 0 12px 12px;">
                                                <div class="mb-3">
                                                    <div class="small font-weight-bold text-success mb-2 text-uppercase">
                                                        <?php echo __('O QUE NOTIFICAR? (GLOBAL)'); ?>
                                                    </div>
                                                    <input type="checkbox" id="wa_chamados" name="whatsapp_recebe_chamados" value="1" class="badge-checkbox" <?php echo (($alert_config['whatsapp_recebe_chamados'] ?? 0) == 1) ? 'checked' : ''; ?>>
                                                    <label for="wa_chamados" class="badge-icon-label"><i class="fas fa-ticket-alt"></i></label>

                                                    <input type="checkbox" id="wa_manutencao" name="whatsapp_recebe_manutencao" value="1" class="badge-checkbox" <?php echo (($alert_config['whatsapp_recebe_manutencao'] ?? 0) == 1) ? 'checked' : ''; ?>>
                                                    <label for="wa_manutencao" class="badge-icon-label bg-warning"><i class="fas fa-tools"></i></label>

                                                    <input type="checkbox" id="wa_estoque" name="whatsapp_recebe_estoque" value="1" class="badge-checkbox" <?php echo (($alert_config['whatsapp_recebe_estoque'] ?? 0) == 1) ? 'checked' : ''; ?>>
                                                    <label for="wa_estoque" class="badge-icon-label bg-info" style="background-color: #36b9cc !important;"><i class="fas fa-boxes"></i></label>
                                                </div>

                                                <div class="mb-3 wa-priority-group">
                                                    <div class="small font-weight-bold text-success mb-2 text-uppercase">
                                                        <?php echo __('PRIORIDADES PERMITIDAS (GLOBAL)'); ?>
                                                    </div>
                                                    <input type="checkbox" id="wa_prio_p1" name="whatsapp_prioridade_p1" value="1" class="badge-checkbox" <?php echo (($alert_config['whatsapp_prioridade_p1'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                                    <label for="wa_prio_p1" class="badge-label" style="background-color: #8b0000; color: white;">P1</label>

                                                    <input type="checkbox" id="wa_prio_p2" name="whatsapp_prioridade_p2" value="1" class="badge-checkbox" <?php echo (($alert_config['whatsapp_prioridade_p2'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                                    <label for="wa_prio_p2" class="badge-label" style="background-color: #dc3545; color: white;">P2</label>

                                                    <input type="checkbox" id="wa_prio_p3" name="whatsapp_prioridade_p3" value="1" class="badge-checkbox" <?php echo (($alert_config['whatsapp_prioridade_p3'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                                    <label for="wa_prio_p3" class="badge-label" style="background-color: #f6c23e; color: white;">P3</label>

                                                    <input type="checkbox" id="wa_prio_p4" name="whatsapp_prioridade_p4" value="1" class="badge-checkbox" <?php echo (($alert_config['whatsapp_prioridade_p4'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                                    <label for="wa_prio_p4" class="badge-label" style="background-color: #1cc88a; color: white;">P4</label>
                                                </div>

                                                <div class="wa-category-group">
                                                    <div class="small font-weight-bold text-success mb-2 text-uppercase">
                                                        <?php echo __('CATEGORIAS PERMITIDAS (GLOBAL)'); ?>
                                                    </div>
                                                    <input type="checkbox" id="wa_tipo_incidente" name="whatsapp_tipo_incidente" value="1" class="badge-checkbox" <?php echo (($alert_config['cat_incidente'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                                    <label for="wa_tipo_incidente" class="badge-label badge-incidente"><?php echo __('Incidente'); ?></label>

                                                    <input type="checkbox" id="wa_tipo_mudanca" name="whatsapp_tipo_mudanca" value="1" class="badge-checkbox" <?php echo (($alert_config['cat_mudanca'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                                    <label for="wa_tipo_mudanca" class="badge-label badge-mudanca"><?php echo __('Mudança'); ?></label>

                                                    <input type="checkbox" id="wa_tipo_requisicao" name="whatsapp_tipo_requisicao" value="1" class="badge-checkbox" <?php echo (($alert_config['cat_requisicao'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                                    <label for="wa_tipo_requisicao" class="badge-label badge-requisicao"><?php echo __('Requisição'); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right mt-3">
                                            <button type="submit" class="btn btn-primary btn-save-ajax" style="background: rgb(44,64,74);"><?php echo __('Salvar WhatsApp'); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- 5. SMTP -->
                        <div class="tab-pane fade" id="smtp" role="tabpanel" aria-labelledby="smtp-tab">
                            <div class="card shadow mb-4" id="cardSmtp">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="text-primary m-0 font-weight-bold">
                                        <i class="fas fa-server mr-2"></i><?php echo __('Configuração de E-mail (SMTP)'); ?>
                                    </h6>
                                    <span class="badge badge-pill badge-light text-muted small" id="smtpUpdatedAt"
                                        title="<?php echo __('Última atualização'); ?>">
                                        <?php if (!empty($smtp_config['updated_at'])): ?>
                                            <?php echo __('Atualizado em:'); ?>
                                            <?php echo date('d/m/Y H:i', strtotime($smtp_config['updated_at'])); ?>
                                        <?php else: ?>
                                            <?php echo __('Configuração padrão'); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">
                                        <?php echo __('Configure o servidor de e-mail utilizado para envio de alertas e notificações do sistema.'); ?>
                                    </p>

                                    <!-- Modo Leitura -->
                                    <div id="smtpReadMode">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <div
                                                    style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#4e73df,#224abe);display:flex;align-items:center;justify-content:center;">
                                                    <i class="fas fa-envelope-open-text text-white"
                                                        style="font-size:1.4rem;"></i>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="font-weight-bold text-dark" style="font-size:1rem;">
                                                    <?php echo htmlspecialchars($smtp_config['smtp_from_name']); ?>
                                                </div>
                                                <div class="text-muted small">
                                                    <i
                                                        class="fas fa-at mr-1"></i><?php echo htmlspecialchars($smtp_config['smtp_user'] ?: __('Não configurado')); ?>
                                                </div>
                                                <div class="text-muted small">
                                                    <i class="fas fa-network-wired mr-1"></i>
                                                    <?php echo htmlspecialchars($smtp_config['smtp_host']); ?>
                                                    : <?php echo (int) $smtp_config['smtp_port']; ?>
                                                    &nbsp;<span
                                                        class="badge badge-<?php echo ($smtp_config['smtp_secure'] ?? 'tls') === 'ssl' ? 'warning' : 'success'; ?> badge-pill"
                                                        style="font-size:0.65rem;">
                                                        <?php echo strtoupper($smtp_config['smtp_secure'] ?? 'TLS'); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-auto d-flex" style="gap:8px;">
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnSmtpTest">
                                                    <i class="fas fa-paper-plane mr-1"></i><?php echo __('Testar Conexão'); ?>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary" id="btnSmtpEdit"
                                                    style="background: rgb(44,64,74); border-color: rgb(44,64,74);">
                                                    <i class="fas fa-pen mr-1"></i><?php echo __('Editar'); ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modo Edição (oculto por padrão) -->
                                    <div id="smtpEditMode" style="display:none;">
                                        <form id="formSmtpConfig" autocomplete="off">
                                            <input type="hidden" name="smtp_config" value="1">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label
                                                        class="small font-weight-bold text-gray-700"><?php echo __('Nome do Remetente'); ?></label>
                                                    <input type="text" class="form-control" name="smtp_from_name"
                                                        id="smtp_from_name"
                                                        value="<?php echo htmlspecialchars($smtp_config['smtp_from_name']); ?>"
                                                        placeholder="<?php echo __('Ex: ASSET MGT - ALERTA'); ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label
                                                        class="small font-weight-bold text-gray-700"><?php echo __('E-mail Remetente (usuário SMTP)'); ?></label>
                                                    <input type="email" class="form-control" name="smtp_user" id="smtp_user"
                                                        value="<?php echo htmlspecialchars($smtp_config['smtp_user']); ?>"
                                                        placeholder="remetente@dominio.com">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label
                                                        class="small font-weight-bold text-gray-700"><?php echo __('Senha / App Password'); ?></label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" name="smtp_pass"
                                                            id="smtp_pass"
                                                            placeholder="<?php echo __('Deixe em branco para manter a atual'); ?>">
                                                        <div class="input-group-append">
                                                            <button class="btn btn-outline-secondary" type="button"
                                                                id="btnTogglePass" tabindex="-1">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted"><?php echo __('Para Gmail, use uma'); ?> <a
                                                            href="https://myaccount.google.com/apppasswords"
                                                            target="_blank"><?php echo __('Senha de App'); ?></a>.</small>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label
                                                        class="small font-weight-bold text-gray-700"><?php echo __('Servidor SMTP (Host)'); ?></label>
                                                    <input type="text" class="form-control" name="smtp_host" id="smtp_host"
                                                        value="<?php echo htmlspecialchars($smtp_config['smtp_host']); ?>"
                                                        placeholder="smtp.gmail.com">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label
                                                        class="small font-weight-bold text-gray-700"><?php echo __('Porta'); ?></label>
                                                    <input type="number" class="form-control" name="smtp_port" id="smtp_port"
                                                        value="<?php echo (int) $smtp_config['smtp_port']; ?>"
                                                        placeholder="587">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label
                                                        class="small font-weight-bold text-gray-700"><?php echo __('Criptografia'); ?></label>
                                                    <select class="form-control" name="smtp_secure" id="smtp_secure">
                                                        <option value="tls" <?php echo ($smtp_config['smtp_secure'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>
                                                            <?php echo __('TLS (porta 587)'); ?></option>
                                                        <option value="ssl" <?php echo ($smtp_config['smtp_secure'] ?? 'tls') === 'ssl' ? 'selected' : ''; ?>>
                                                            <?php echo __('SSL (porta 465)'); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="d-flex" style="gap:8px; margin-top:4px;">
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    id="btnSmtpCancel">
                                                    <i class="fas fa-times mr-1"></i><?php echo __('Cancelar'); ?>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success" id="btnSmtpSave"
                                                    style="background: rgb(44,64,74); border-color: rgb(44,64,74); color: #fff;">
                                                    <i class="fas fa-save mr-1"></i><?php echo __('Salvar Configurações'); ?>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary ml-auto"
                                                    id="btnSmtpTestEdit">
                                                    <i class="fas fa-paper-plane mr-1"></i><?php echo __('Testar Conexão'); ?>
                                                </button>
                                            </div>
                                        </form>
                                        <!-- Resultado do teste -->
                                        <div id="smtpTestResult" class="mt-3" style="display:none;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 6. RECIPIENTS (Email Recipients) -->
                        <div class="tab-pane fade" id="recipients" role="tabpanel" aria-labelledby="recipients-tab">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary m-0 font-weight-bold">
                                        <i class="fas fa-users mr-2"></i><?php echo __('Destinatários de Ativos (E-mail)'); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="formEmailAlertasConfig">
                                        <input type="hidden" name="config_alertas" value="1">
                                        <div class="card shadow-sm border-0 card-email mb-0">
                                            <div class="card-header-custom bg-email">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-envelope mr-2"></i>
                                                    <span
                                                        class="font-weight-bold"><?php echo __('Gestão de Destinatários'); ?></span>
                                                </div>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input channel-toggle"
                                                        id="toggle_email" name="email_ativo" value="1" <?php echo (($alert_config['email_ativo'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="toggle_email"></label>
                                                </div>
                                            </div>
                                            <div class="card-body bg-light p-3 border-top pb-4" style="border-radius: 0 0 12px 12px;">
                                                <div class="position-relative mb-3">
                                                    <input type="text" id="emailRecipientSearch"
                                                        class="form-control form-control-sm"
                                                        placeholder="<?php echo __('Pesquisar destinatário por nome ou e-mail...'); ?>"
                                                        style="border-radius: 20px; padding-left: 15px;">
                                                    <i class="fas fa-search position-absolute text-muted"
                                                        style="right: 15px; top: 10px; font-size: 0.8rem;"></i>
                                                    <div id="emailRecipientDropdown"
                                                        class="dropdown-menu w-100 shadow-sm"
                                                        style="display:none; position:absolute; top: 100%; left:0; z-index:1000; border-radius: 8px;">
                                                    </div>
                                                </div>

                                                <div class="small font-weight-bold text-primary mb-2 text-uppercase">
                                                    <?php echo __('DESTINATÁRIOS ATIVOS'); ?>
                                                </div>
                                                <div class="recipient-list mb-3" id="activeEmailRecipients">
                                                    <div class="p-3 text-center text-muted small"><i class="fas fa-spinner fa-spin mr-1"></i><?php echo __('Carregando destinatários...'); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right mt-3">
                                            <button type="submit" class="btn btn-primary btn-save-ajax" style="background: rgb(44,64,74);"><?php echo __('Salvar Destinatários'); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- 7. ESTOQUE -->
                        <div class="tab-pane fade" id="estoque" role="tabpanel" aria-labelledby="estoque-tab">
                            <div class="card shadow mb-4" id="cardEstoque">
                                <div class="card-header py-3">
                                    <h6 class="text-primary m-0 font-weight-bold">
                                        <i class="fas fa-boxes mr-2"></i><?php echo __('Configurações de Estoque'); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="formEstoqueConfig">
                                        <input type="hidden" name="estoque_config" value="1">
                                        <div class="row">
                                            <!-- Lado Esquerdo: Quantidades (Thresholds) -->
                                            <div class="col-md-5 border-right">
                                                <h6 class="font-weight-bold mb-4 text-dark"><i
                                                        class="fas fa-sliders-h mr-2"></i><?php echo __('Ponto de Reposição'); ?>
                                                </h6>
                                                <div class="row pr-md-3">
                                                    <?php
                                                    $tiers_map = [
                                                        'estoque_threshold_t1' => 'Tier 1',
                                                        'estoque_threshold_t2' => 'Tier 2',
                                                        'estoque_threshold_t3' => 'Tier 3',
                                                        'estoque_threshold_t4' => 'Tier 4',
                                                        'estoque_threshold_inf' => 'Infraestrutura'
                                                    ];
                                                    $post_map = [
                                                        'estoque_threshold_t1' => 'threshold_t1',
                                                        'estoque_threshold_t2' => 'threshold_t2',
                                                        'estoque_threshold_t3' => 'threshold_t3',
                                                        'estoque_threshold_t4' => 'threshold_t4',
                                                        'estoque_threshold_inf' => 'threshold_inf'
                                                    ];
                                                    foreach ($tiers_map as $col => $label): ?>
                                                        <div class="col-12 mb-3">
                                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                                <label
                                                                    class="small font-weight-bold text-gray-700 m-0"><?php echo __($label); ?></label>
                                                            </div>
                                                            <div class="input-group input-group-sm">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i
                                                                            class="fas fa-exclamation-triangle text-warning"></i></span>
                                                                </div>
                                                                <input type="number" class="form-control"
                                                                    name="<?php echo $post_map[$col]; ?>"
                                                                    value="<?php echo (int) ($alert_config[$col] ?? 3); ?>" min="0">
                                                                <div class="input-group-append">
                                                                    <span
                                                                        class="input-group-text small"><?php echo __('unidades'); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>

                                            <!-- Lado Direito: Destinatários -->
                                            <div class="col-md-7 pl-md-4">
                                                <h6 class="font-weight-bold mb-3 text-primary"><i
                                                        class="fas fa-envelope-open-text mr-2"></i><?php echo __('Destinatários de Alerta'); ?>
                                                </h6>
                                                <p class="text-muted small mb-3">
                                                    <?php echo __('Usuários que receberão e-mails de alerta para os tiers selecionados.'); ?>
                                                </p>

                                                <div class="position-relative mb-3">
                                                    <input type="text" id="estoqueRecipientSearch"
                                                        class="form-control form-control-sm"
                                                        placeholder="<?php echo __('Pesquisar destinatário...'); ?>"
                                                        style="border-radius: 20px; padding-left: 15px;">
                                                    <i class="fas fa-search position-absolute text-muted"
                                                        style="right: 15px; top: 10px; font-size: 0.8rem;"></i>
                                                    <div id="estoqueRecipientDropdown" class="dropdown-menu w-100 shadow-sm"
                                                        style="display:none; position:absolute; top: 100%; left:0; z-index:1000; border-radius: 8px;">
                                                    </div>
                                                </div>

                                                <div class="recipient-list mt-3" id="activeEstoqueRecipients"
                                                    style="max-height: 350px; overflow-y: auto;">
                                                    <div class="p-3 text-center text-muted small"><i
                                                            class="fas fa-spinner fa-spin mr-1"></i>
                                                        <?php echo __('Carregando...'); ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-right mt-4 border-top pt-3">
                                            <button type="submit" class="btn btn-primary btn-sm btn-save-ajax"
                                                style="background: rgb(44,64,74); border-color: rgb(44,64,74);">
                                                <i class="fas fa-save mr-1"></i><?php echo __('Salvar Alterações'); ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- 8. SLA -->
                        <div class="tab-pane fade" id="sla" role="tabpanel" aria-labelledby="sla-tab">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary m-0 font-weight-bold">
                                        <i
                                            class="fas fa-clock mr-2"></i><?php echo __('Configuração de SLA (Service Level Agreement)'); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="formSLA" method="POST" action="configuracoes.php">
                                        <p class="mb-4">
                                            <?php echo __('Defina o tempo máximo de resolução (Horas e Minutos) para cada categoria de chamado.'); ?>
                                        </p>

                                        <?php
                                        $categories = ['Incidente', 'Mudança', 'Requisição'];
                                        foreach ($categories as $cat) {
                                            $time = getHoursAndMinutes($configs[$cat]);
                                            ?>
                                            <div class="form-group row align-items-center mb-4 sla-row"
                                                data-category="<?php echo $cat; ?>">
                                                <label for="sla_<?php echo $cat; ?>"
                                                    class="col-sm-2 col-form-label font-weight-bold"><?php echo __($cat); ?></label>
                                                <div class="col-sm-3">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control sla-hours"
                                                            name="sla[<?php echo $cat; ?>][hours]" value="<?php echo $time['h']; ?>"
                                                            required min="0" placeholder="0">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><?php echo __('Horas'); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control sla-minutes"
                                                            name="sla[<?php echo $cat; ?>][minutes]" value="<?php echo $time['m']; ?>"
                                                            required min="0" max="59" placeholder="0">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><?php echo __('Minutos'); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4 d-flex justify-content-between align-items-center pl-4 py-2"
                                                    style="background: #f8f9fc; border-radius: 12px; border: 1px solid #e3e6f0;">
                                                    <div class="text-center" style="flex: 1;">
                                                        <span class="badge mb-2 px-2 py-1"
                                                            style="border-radius: 50px; text-transform: uppercase; font-size: 0.55rem; letter-spacing: 0.5px; background-color: #8b0000; color: white;">P1</span>
                                                        <div
                                                            style="height: 6px; background: #8b0000; margin: 0 3px 8px 3px; border-radius: 10px; opacity: 0.8;">
                                                        </div>
                                                        <strong class="text-dark d-block label-p1"
                                                            style="font-size: 0.75rem;">--</strong>
                                                    </div>
                                                    <div class="text-center" style="flex: 1;">
                                                        <span class="badge badge-danger mb-2 px-2 py-1"
                                                            style="border-radius: 50px; text-transform: uppercase; font-size: 0.55rem; letter-spacing: 0.5px;">P2</span>
                                                        <div
                                                            style="height: 6px; background: #dc3545; margin: 0 3px 8px 3px; border-radius: 10px; opacity: 0.8;">
                                                        </div>
                                                        <strong class="text-dark d-block label-p2"
                                                            style="font-size: 0.75rem;">--</strong>
                                                    </div>
                                                    <div class="text-center" style="flex: 1;">
                                                        <span class="badge badge-warning mb-2 px-2 py-1"
                                                            style="border-radius: 50px; text-transform: uppercase; font-size: 0.55rem; letter-spacing: 0.5px; color: #fff; background-color: #f6c23e;">P3</span>
                                                        <div
                                                            style="height: 6px; background: #f6c23e; margin: 0 3px 8px 3px; border-radius: 10px; opacity: 0.8;">
                                                        </div>
                                                        <strong class="text-dark d-block label-p3"
                                                            style="font-size: 0.75rem;">--</strong>
                                                    </div>
                                                    <div class="text-center" style="flex: 1;">
                                                        <span class="badge badge-success mb-2 px-2 py-1"
                                                            style="border-radius: 50px; text-transform: uppercase; font-size: 0.55rem; letter-spacing: 0.5px;">P4</span>
                                                        <div
                                                            style="height: 6px; background: #1cc88a; margin: 0 3px 8px 3px; border-radius: 10px; opacity: 0.8;">
                                                        </div>
                                                        <strong class="text-dark d-block label-p4"
                                                            style="font-size: 0.75rem;">--</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        } ?>

                                        <!-- SLA DE PRIMEIRO ATENDIMENTO -->
                                        <hr class="my-4">
                                        <div class="form-group row align-items-center mb-3">
                                            <div class="col-sm-12 mb-2">
                                                <div class="d-flex align-items-center p-3 rounded"
                                                    style="background: linear-gradient(135deg,#f0f7ff,#fff); border: 1px solid #d1e7ff;">
                                                    <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#36b9cc,#1d8fa3);display:flex;align-items:center;justify-content:center;flex-shrink:0;"
                                                        class="mr-3">
                                                        <i class="fas fa-stopwatch text-white" style="font-size:1.1rem;"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="font-weight-bold text-dark" style="font-size:0.9rem;">
                                                            <?php echo __('SLA de Primeiro Atendimento'); ?></div>
                                                        <div class="text-muted small">
                                                            <?php echo __('Tempo máximo para o primeiro contato do técnico (atribuição ou comentário). Usado apenas em relatórios.'); ?>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3 d-flex align-items-center">
                                                        <div class="input-group input-group-sm" style="width:130px;">
                                                            <input type="number" class="form-control font-weight-bold text-center"
                                                                name="sla_primeira_resposta_minutos"
                                                                id="sla_primeira_resposta_minutos"
                                                                value="<?php echo (int) $sla_primeira_resposta_minutos; ?>" min="1"
                                                                max="1440" style="border-radius: 8px 0 0 8px;">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text"
                                                                    style="border-radius: 0 8px 8px 0; background:#e8f4f8; color:#1d8fa3; font-weight:700;"><?php echo __('min'); ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block mt-1 ml-1"><i
                                                        class="fas fa-info-circle mr-1 text-info"></i><?php echo __('Padrão recomendado: 10 minutos. Este valor não afeta o SLA de resolução.'); ?></small>
                                            </div>
                                        </div>

                                        <div class="form-group row mt-4">
                                            <div class="col-sm-12 text-right">
                                                <button type="submit" class="btn btn-primary btn-save-ajax"
                                                    style="background: rgb(44,64,74);"><?php echo __('Salvar SLA'); ?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- 9. DEPRECIAÇÃO -->
                        <div class="tab-pane fade" id="depreciacao" role="tabpanel" aria-labelledby="depreciacao-tab">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary m-0 font-weight-bold">
                                        <i
                                            class="fas fa-percentage mr-2"></i><?php echo __('Configuração de Depreciação e Doação de Ativos'); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="formDepreciacao" method="POST" action="configuracoes.php">
                                        <input type="hidden" name="depreciacao_config" value="1">
                                        <p class="mb-4">
                                            <?php echo __('Defina a taxa de depreciação dos ativos e as regras de elegibilidade para doação.'); ?>
                                        </p>

                                        <!-- Taxa de Depreciação -->
                                        <div class="form-group row">
                                            <label
                                                class="col-sm-3 col-form-label font-weight-bold"><?php echo __('Taxas de Depreciação (%)'); ?></label>
                                            <div class="col-sm-9">
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label
                                                            class="small text-muted font-weight-bold"><?php echo __('Tier 1'); ?></label>
                                                        <div class="input-group">
                                                            <input type="number" class="form-control border-left-primary"
                                                                name="depreciacao[taxa_t1]"
                                                                value="<?php echo $dep_config['taxa_tier1']; ?>" required min="0"
                                                                max="100" step="0.01">
                                                            <div class="input-group-append"><span class="input-group-text">%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label
                                                            class="small text-muted font-weight-bold"><?php echo __('Tier 2'); ?></label>
                                                        <div class="input-group">
                                                            <input type="number" class="form-control border-left-info"
                                                                name="depreciacao[taxa_t2]"
                                                                value="<?php echo $dep_config['taxa_tier2']; ?>" required min="0"
                                                                max="100" step="0.01">
                                                            <div class="input-group-append"><span class="input-group-text">%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label
                                                            class="small text-muted font-weight-bold"><?php echo __('Tier 3'); ?></label>
                                                        <div class="input-group">
                                                            <input type="number" class="form-control border-left-success"
                                                                name="depreciacao[taxa_t3]"
                                                                value="<?php echo $dep_config['taxa_tier3']; ?>" required min="0"
                                                                max="100" step="0.01">
                                                            <div class="input-group-append"><span class="input-group-text">%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label
                                                            class="small text-muted font-weight-bold"><?php echo __('Tier 4'); ?></label>
                                                        <div class="input-group">
                                                            <input type="number" class="form-control border-left-warning"
                                                                name="depreciacao[taxa_t4]"
                                                                value="<?php echo $dep_config['taxa_tier4']; ?>" required min="0"
                                                                max="100" step="0.01">
                                                            <div class="input-group-append"><span class="input-group-text">%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label
                                                            class="small text-muted font-weight-bold"><?php echo __('Infraestrutura'); ?></label>
                                                        <div class="input-group">
                                                            <input type="number" class="form-control border-left-danger"
                                                                name="depreciacao[taxa_inf]"
                                                                value="<?php echo $dep_config['taxa_infraestrutura']; ?>" required
                                                                min="0" max="100" step="0.01">
                                                            <div class="input-group-append"><span class="input-group-text">%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Período de Depreciação -->
                                        <div class="form-group row">
                                            <label
                                                class="col-sm-3 col-form-label font-weight-bold"><?php echo __('Período de Depreciação'); ?></label>
                                            <div class="col-sm-2">
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="depreciacao[periodo_anos]"
                                                        value="<?php echo $dep_config['periodo_anos']; ?>" required min="0"
                                                        placeholder="0">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><?php echo __('Anos'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="depreciacao[periodo_meses]"
                                                        value="<?php echo $dep_config['periodo_meses']; ?>" required min="0"
                                                        max="11" placeholder="0">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><?php echo __('Meses'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <!-- Política de Destinação de Fim de Vida -->
                                        <div class="form-group row">
                                            <label
                                                class="col-sm-3 col-form-label font-weight-bold"><?php echo __('Destinação de Fim de Vida (End-of-Life)'); ?></label>
                                            <div class="col-sm-9">
                                                <p class="small text-muted mb-3">
                                                    <?php echo __('Defina o destino padrão de um equipamento após a sua depreciação contábil chegar a 100%.'); ?>
                                                </p>
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label
                                                            class="small text-muted font-weight-bold"><?php echo __('Tier 1'); ?></label>
                                                        <select class="form-control border-left-primary"
                                                            name="depreciacao[dest_tier1]">
                                                            <option value="Doação" <?php echo ($dep_config['destinacao_tier1'] == 'Doação') ? 'selected' : ''; ?>>
                                                                <?php echo __('Doação'); ?></option>
                                                            <option value="Leilão" <?php echo ($dep_config['destinacao_tier1'] == 'Leilão') ? 'selected' : ''; ?>>
                                                                <?php echo __('Leilão'); ?></option>
                                                            <option value="Descarte" <?php echo ($dep_config['destinacao_tier1'] == 'Descarte') ? 'selected' : ''; ?>>
                                                                <?php echo __('Descarte'); ?></option>
                                                            <option value="Nenhuma" <?php echo ($dep_config['destinacao_tier1'] == 'Nenhuma') ? 'selected' : ''; ?>>
                                                                <?php echo __('Nenhuma'); ?></option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label
                                                            class="small text-muted font-weight-bold"><?php echo __('Tier 2'); ?></label>
                                                        <select class="form-control border-left-info"
                                                            name="depreciacao[dest_tier2]">
                                                            <option value="Doação" <?php echo ($dep_config['destinacao_tier2'] == 'Doação') ? 'selected' : ''; ?>>
                                                                <?php echo __('Doação'); ?></option>
                                                            <option value="Leilão" <?php echo ($dep_config['destinacao_tier2'] == 'Leilão') ? 'selected' : ''; ?>>
                                                                <?php echo __('Leilão'); ?></option>
                                                            <option value="Descarte" <?php echo ($dep_config['destinacao_tier2'] == 'Descarte') ? 'selected' : ''; ?>>
                                                                <?php echo __('Descarte'); ?></option>
                                                            <option value="Nenhuma" <?php echo ($dep_config['destinacao_tier2'] == 'Nenhuma') ? 'selected' : ''; ?>>
                                                                <?php echo __('Nenhuma'); ?></option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label
                                                            class="small text-muted font-weight-bold"><?php echo __('Tier 3'); ?></label>
                                                        <select class="form-control border-left-success"
                                                            name="depreciacao[dest_tier3]">
                                                            <option value="Doação" <?php echo ($dep_config['destinacao_tier3'] == 'Doação') ? 'selected' : ''; ?>>
                                                                <?php echo __('Doação'); ?></option>
                                                            <option value="Leilão" <?php echo ($dep_config['destinacao_tier3'] == 'Leilão') ? 'selected' : ''; ?>>
                                                                <?php echo __('Leilão'); ?></option>
                                                            <option value="Descarte" <?php echo ($dep_config['destinacao_tier3'] == 'Descarte') ? 'selected' : ''; ?>>
                                                                <?php echo __('Descarte'); ?></option>
                                                            <option value="Nenhuma" <?php echo ($dep_config['destinacao_tier3'] == 'Nenhuma') ? 'selected' : ''; ?>>
                                                                <?php echo __('Nenhuma'); ?></option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label
                                                            class="small text-muted font-weight-bold"><?php echo __('Tier 4'); ?></label>
                                                        <select class="form-control border-left-warning"
                                                            name="depreciacao[dest_tier4]">
                                                            <option value="Doação" <?php echo ($dep_config['destinacao_tier4'] == 'Doação') ? 'selected' : ''; ?>>
                                                                <?php echo __('Doação'); ?></option>
                                                            <option value="Leilão" <?php echo ($dep_config['destinacao_tier4'] == 'Leilão') ? 'selected' : ''; ?>>
                                                                <?php echo __('Leilão'); ?></option>
                                                            <option value="Descarte" <?php echo ($dep_config['destinacao_tier4'] == 'Descarte') ? 'selected' : ''; ?>>
                                                                <?php echo __('Descarte'); ?></option>
                                                            <option value="Nenhuma" <?php echo ($dep_config['destinacao_tier4'] == 'Nenhuma') ? 'selected' : ''; ?>>
                                                                <?php echo __('Nenhuma'); ?></option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label
                                                            class="small text-muted font-weight-bold"><?php echo __('Infraestrutura'); ?></label>
                                                        <select class="form-control border-left-danger"
                                                            name="depreciacao[dest_infraestrutura]">
                                                            <option value="Doação" <?php echo ($dep_config['destinacao_infraestrutura'] == 'Doação') ? 'selected' : ''; ?>><?php echo __('Doação'); ?></option>
                                                            <option value="Leilão" <?php echo ($dep_config['destinacao_infraestrutura'] == 'Leilão') ? 'selected' : ''; ?>><?php echo __('Leilão'); ?></option>
                                                            <option value="Descarte" <?php echo ($dep_config['destinacao_infraestrutura'] == 'Descarte') ? 'selected' : ''; ?>><?php echo __('Descarte'); ?></option>
                                                            <option value="Nenhuma" <?php echo ($dep_config['destinacao_infraestrutura'] == 'Nenhuma') ? 'selected' : ''; ?>><?php echo __('Nenhuma'); ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <!-- Elegível para Doação (Global) -->
                                        <div class="form-group row">
                                            <label
                                                class="col-sm-3 col-form-label font-weight-bold"><?php echo __('Elegível para Doação?'); ?></label>
                                            <div class="col-sm-4">
                                                <div class="custom-control custom-switch" style="margin-top: 7px;">
                                                    <input type="checkbox" class="custom-control-input" id="elegivelDoacao"
                                                        name="depreciacao[elegivel_doacao]" value="1" <?php echo ($dep_config['elegivel_doacao'] == 1) ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="elegivelDoacao">
                                                        <?php echo ($dep_config['elegivel_doacao'] == 1) ? __('Sim, ativos podem ser doados') : __('Não, doação desativada'); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Elegível para Leilão (Global) -->
                                        <div class="form-group row">
                                            <label
                                                class="col-sm-3 col-form-label font-weight-bold"><?php echo __('Elegível para Leilão?'); ?></label>
                                            <div class="col-sm-4">
                                                <div class="custom-control custom-switch" style="margin-top: 7px;">
                                                    <input type="checkbox" class="custom-control-input" id="elegivelLeilao"
                                                        name="depreciacao[elegivel_leilao]" value="1" <?php echo ($dep_config['elegivel_leilao'] == 1) ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="elegivelLeilao">
                                                        <?php echo ($dep_config['elegivel_leilao'] == 1) ? __('Sim, ativos no Fim-de-Vida podem ser leiloados') : __('Não, leilão desativado'); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Elegibilidade por Categoria -->
                                        <div id="categoriasDoacaoSection">
                                            <div class="form-group row">
                                                <label
                                                    class="col-sm-3 col-form-label font-weight-bold"><?php echo __('Elegibilidade por Categoria'); ?></label>
                                                <div class="col-sm-9">
                                                    <div class="row">
                                                        <?php foreach ($cat_doacao as $cat_nome => $cat_eleg): ?>
                                                            <div class="col-sm-4 mb-2">
                                                                <div class="custom-control custom-switch">
                                                                    <input type="checkbox" class="custom-control-input cat-switch"
                                                                        id="catDoacao_<?php echo md5($cat_nome); ?>"
                                                                        name="cat_doacao[<?php echo htmlspecialchars($cat_nome); ?>]"
                                                                        value="1" <?php echo ($cat_eleg == 1) ? 'checked' : ''; ?>>
                                                                    <label class="custom-control-label"
                                                                        for="catDoacao_<?php echo md5($cat_nome); ?>"><?php echo htmlspecialchars($cat_nome); ?></label>
                                                                </div>
                                                            </div>
                                                            <?php
                                                        endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tempo mínimo para Doação -->
                                        <div class="form-group row" id="tempoDoacaoRow">
                                            <label
                                                class="col-sm-3 col-form-label font-weight-bold"><?php echo __('Tempo mínimo para Doação'); ?></label>
                                            <div class="col-sm-2">
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="depreciacao[tempo_doacao_anos]"
                                                        value="<?php echo $dep_config['tempo_doacao_anos']; ?>" min="0"
                                                        placeholder="0">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><?php echo __('Anos'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="depreciacao[tempo_doacao_meses]"
                                                        value="<?php echo $dep_config['tempo_doacao_meses']; ?>" min="0" max="11"
                                                        placeholder="0">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><?php echo __('Meses'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group row mt-4">
                                            <div class="col-sm-12 text-right">
                                                <button type="submit" class="btn btn-primary btn-save-ajax"
                                                    style="background: rgb(44,64,74);"><?php echo __('Salvar Depreciação'); ?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- 10. IA -->
                        <div class="tab-pane fade" id="ia" role="tabpanel" aria-labelledby="ia-tab">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary m-0 font-weight-bold">
                                        <i class="fas fa-robot mr-2"></i> <?php echo __('Inteligência Artificial (IA)'); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="formIA" method="POST">
                                        <input type="hidden" name="ia_config" value="1">
                                        <p class="text-muted small mb-4 mt-2">
                                            <?php echo __('Configure onde o Agente de IA deve atuar no sistema, ativando ou desativando cada módulo de forma independente.'); ?>
                                        </p>
                                        
                                        <?php require_once 'credentials.php'; ?>
                                        <div class="row mb-4">
                                            <div class="col-md-12">
                                                <div class="form-group p-3 border rounded" style="background-color: #f8f9fc;">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <label for="gemini_api_key" class="font-weight-bold text-primary mb-0">
                                                            <i class="fas fa-key mr-1"></i> Chave de API do Motor de IA (Google Gemini)
                                                        </label>
                                                        <a href="https://aistudio.google.com/app/apikey" target="_blank" class="btn btn-sm btn-info text-white" title="<?php echo __('Gerar nova chave no Google AI Studio'); ?>">
                                                            <i class="fas fa-external-link-alt"></i> <?php echo __('Gerar Nova Chave'); ?>
                                                        </a>
                                                    </div>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" id="gemini_api_key" name="gemini_api_key" 
                                                            placeholder="Cole sua nova API Key aqui (ex: AIzaSy...) para substituí-la."
                                                            value="<?php echo defined('GEMINI_API_KEY') ? GEMINI_API_KEY : ''; ?>">
                                                        <div class="input-group-append">
                                                            <button class="btn btn-outline-secondary bg-white" type="button" onclick="const p = document.getElementById('gemini_api_key'); p.type = p.type === 'password' ? 'text' : 'password'; this.querySelector('i').classList.toggle('fa-eye'); this.querySelector('i').classList.toggle('fa-eye-slash');" title="<?php echo __('Mostrar/Ocultar'); ?>">
                                                                <i class="fas fa-eye text-muted"></i>
                                                            </button>
                                                            <button class="btn btn-primary text-white" type="button" id="btnTestarChave" style="background: rgb(44,64,74); border-color: rgb(44,64,74);" title="<?php echo __('Testar conexão real com o Google'); ?>">
                                                                <i class="fas fa-vial"></i> <?php echo __('Testar Chave'); ?>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted mt-2">
                                                        *O sistema usa o arquivo <code>credentials.php</code> para armazenar de forma segura as chaves do Google AI Studio. Preencha e salve apenas se desejar substituir a chave atual.
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="card border-0 bg-light h-100">
                                                    <div class="card-body d-flex flex-column justify-content-between">
                                                        <div>
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="fas fa-comments text-primary mr-2 fa-lg"></i>
                                                                <span class="font-weight-bold">Chat Agent</span>
                                                            </div>
                                                            <p class="small text-muted mb-3">
                                                                <?php echo __('Permite que usuários conversem com o Agente de IA pelo chat do sistema.'); ?>
                                                            </p>
                                                        </div>
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" id="iaChatAtivo"
                                                                name="ia_chat_ativo" value="1" <?php echo (($alert_config['ia_chat_ativo'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                                            <label class="custom-control-label"
                                                                for="iaChatAtivo"><?php echo __('Ativar no Chat'); ?></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card border-0 bg-light h-100">
                                                    <div class="card-body d-flex flex-column justify-content-between">
                                                        <div>
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="fas fa-ticket-alt text-info mr-2 fa-lg"></i>
                                                                <span class="font-weight-bold">Assistente de Chamados</span>
                                                            </div>
                                                            <p class="small text-muted mb-3">
                                                                <?php echo __('Exibe sugestões de ação automáticas ao abrir um chamado técnico.'); ?>
                                                            </p>
                                                        </div>
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" id="iaChamadosAtivo"
                                                                name="ia_chamados_ativo" value="1" <?php echo (($alert_config['ia_chamados_ativo'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                                            <label class="custom-control-label"
                                                                for="iaChamadosAtivo"><?php echo __('Ativar nos Chamados'); ?></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="card border-0 bg-light h-100">
                                                    <div class="card-body d-flex flex-column justify-content-between">
                                                        <div>
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="fas fa-shield-alt text-success mr-2 fa-lg"></i>
                                                                <span class="font-weight-bold">Previsão e Prevenção</span>
                                                            </div>
                                                            <p class="small text-muted mb-3">
                                                                <?php echo __('Gera consultoria estratégica automática com base nos dados de infraestrutura.'); ?>
                                                            </p>
                                                        </div>
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" id="iaPreveAtivo"
                                                                name="ia_preve_ativo" value="1" <?php echo (($alert_config['ia_preve_ativo'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                                            <label class="custom-control-label"
                                                                for="iaPreveAtivo"><?php echo __('Ativar Prevenções'); ?></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right mt-3">
                                            <button type="submit" class="btn btn-primary" style="background: rgb(44,64,74);"
                                                id="btnSalvarIA">
                                                <i class="fas fa-save mr-2"></i> <?php echo __('Salvar IA'); ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div> <!-- End of tab contents -->
        </div>
    </div>
    </div>
    </div>
    </div> <!-- End of content -->
    </div>
    </div> <!-- End of Page Wrapper -->
    <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightpick@1.3.4/lightpick.min.js"></script>
    <script src="/assets/js/Date-Range-Picker.js?h=1d598b35ada76eb401b3897ae4b61ccb"></script>
    <script src="/assets/js/Animated-numbers-section.js?h=a0ec092b1194013aa3c8e220b0938a52"></script>
    <script src="/assets/js/Bootstrap-Image-Uploader.js?h=2218f85124ce4687cddacceb8e123cc9"></script>
    <script src="/assets/js/DateRangePicker.js?h=e84100887465fbb69726c415c180211a"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.2.0/zxcvbn.js"></script>
    <script src="/assets/js/Multi-Select-Dropdown-by-Jigar-Mistry.js?h=45421b0ed6bd109b4f00e752ae5bf3e5"></script>
    <script src="/assets/js/Password-Strenght-Checker---Ambrodu.js?h=f40a32e3d989fd0e00bf2f0567e52e27"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
    <script>
        // Helper: mostra feedback de auto-save
        function showAutoSaved(message) {
            var $status = $('#autoSaveStatus');
            var $msg = $('#autoSaveMessage');
            if ($status.length) {
                if (message) $msg.text(message);
                else $msg.text("<?php echo __('Alteração salva!'); ?>");

                $status.stop(true, true).fadeIn(200).delay(2500).fadeOut(400);
            }
        }

        $(document).ready(function () {

            var $switch = $('#elegivelDoacao');
            var $doacaoRow = $('#tempoDoacaoRow');
            var $catSection = $('#categoriasDoacaoSection');
            var $label = $switch.next('label');

            function toggleDoacao() {
                if ($switch.is(':checked')) {
                    $catSection.slideDown(200);
                    $doacaoRow.slideDown(200);
                    $label.text("<?php echo __('Sim, ativos podem ser doados'); ?>");
                } else {
                    $catSection.slideUp(200);
                    $doacaoRow.slideUp(200);
                    $label.text("<?php echo __('Não, doação desativada'); ?>");
                }
            }

            // Initial state
            if (!$switch.is(':checked')) {
                $doacaoRow.hide();
                $catSection.hide();
            }

            // On change
            $switch.on('change', toggleDoacao);

            // SLA Real-time Calculation
            function formatTimeDisplay(totalMinutes) {
                const h = Math.floor(totalMinutes / 60);
                const m = totalMinutes % 60;
                let res = '';
                if (h > 0) res += h + 'h';
                if (m > 0) res += (res ? ' ' : '') + m + 'm';
                return res || '0m';
            }

            function updateSLABars() {
                $('.sla-row').each(function () {
                    const $row = $(this);
                    const hours = parseInt($row.find('.sla-hours').val()) || 0;
                    const minutes = parseInt($row.find('.sla-minutes').val()) || 0;
                    const total = (hours * 60) + minutes;


                    $row.find('.label-p1').text(formatTimeDisplay(Math.round(total / 6)));
                    $row.find('.label-p2').text(formatTimeDisplay(Math.round(total / 3)));
                    $row.find('.label-p3').text(formatTimeDisplay(Math.round(total * 2 / 3)));
                    $row.find('.label-p4').text(formatTimeDisplay(total));
                });
            }

            // (handler vazio removido - tratado por .channel-toggle abaixo)


            $('.sla-hours, .sla-minutes').on('input', updateSLABars);
            updateSLABars(); // Initial call 

            $('#elegivelDoacao').on('change', function () {
                $(this).next('label').text(this.checked ? "<?php echo __('Sim, ativos podem ser doados'); ?>" : "<?php echo __('Não, doação desativada'); ?>");
                if (this.checked) {
                    $('#doacaoOptions').slideDown();
                } else {
                    $('#doacaoOptions').slideUp();
                }
            });

            $('#elegivelLeilao').on('change', function () {
                $(this).next('label').text(this.checked ? "<?php echo __('Sim, ativos no Fim-de-Vida podem ser leiloados'); ?>" : "<?php echo __('Não, leilão desativado'); ?>");
            });

            // Toggle Label Updates (Real-time feedback)
            $('#iaAgenteAtivoBottom').on('change', function () {
                $(this).next('label').text(this.checked ? "<?php echo __('Ativo'); ?>" : "<?php echo __('Inativo'); ?>");
            });

            // Visual feedback for notification cards
            $('.channel-toggle').on('change', function () {
                var $card = $(this).closest('.notification-card, .card');
                if ($(this).is(':checked')) {
                    $card.removeClass('inactive-card');
                } else {
                    $card.addClass('inactive-card');
                }
            });

            // Trigger initial visual state on page load
            $('.channel-toggle').trigger('change');

            // Generic AJAX save handler for all forms
            function handleAjaxSave($form, $btn, originalHtml, successMsg) {
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> ' + "<?php echo __('Salvando...'); ?>");

                $.ajax({
                    url: 'configuracoes.php',
                    method: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function (resp) {
                        if (resp && resp.success) {
                            showAutoSaved(successMsg);
                        } else {
                            alert("<?php echo __('Erro ao salvar:'); ?> " + (resp.message || "<?php echo __('Erro desconhecido'); ?>"));
                        }
                    },
                    error: function () {
                        alert("<?php echo __('Erro na comunicação com o servidor.'); ?>");
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            }

            // Bind all forms marked for AJAX
            $('#formAlertasConfig').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btnSalvarAlertas');
                var originalHtml = '<i class="fas fa-save mr-2"></i> ' + "<?php echo __('Salvar Configurações'); ?>";
                handleAjaxSave($form, $btn, originalHtml, "<?php echo __('Canais de alerta salvos!'); ?>");
            });

            $('#formSLA').on('submit', function (e) {
                e.preventDefault();
                handleAjaxSave($(this), $(this).find('button[type="submit"]'), $(this).find('button[type="submit"]').html(), "<?php echo __('SLA atualizado com sucesso!'); ?>");
            });

            $('#formDepreciacao').on('submit', function (e) {
                e.preventDefault();
                handleAjaxSave($(this), $(this).find('button[type="submit"]'), $(this).find('button[type="submit"]').html(), "<?php echo __('Depreciação salva com sucesso!'); ?>");
            });

            $('#formSessao').on('submit', function (e) {
                e.preventDefault();
                handleAjaxSave($(this), $(this).find('button[type="submit"]'), $(this).find('button[type="submit"]').html(), "<?php echo __('Sessão e Segurança salvas!'); ?>");
            });

            $('#formDashboard').on('submit', function (e) {
                e.preventDefault();
                handleAjaxSave($(this), $(this).find('button[type="submit"]'), $(this).find('button[type="submit"]').html(), "<?php echo __('Configuração do Dashboard salva!'); ?>");
            });

            function updateCardCount() {
                var count = $('.dashboard-card-checkbox:checked').length;
                $('#currentCardCount').text(count);
                if (count > 8) {
                    $('#cardCountInfo').addClass('text-danger').removeClass('text-muted');
                } else {
                    $('#cardCountInfo').removeClass('text-danger').addClass('text-muted');
                }
            }

            $('.dashboard-card-checkbox').on('change', function () {
                if ($('.dashboard-card-checkbox:checked').length > 8) {
                    $(this).prop('checked', false);
                    Swal.fire({
                        icon: 'warning',
                        title: "<?php echo __('Limite Atingido'); ?>",
                        text: "<?php echo __('Você pode selecionar no máximo 8 cards para o dashboard.'); ?>",
                        confirmButtonColor: '#2c404a'
                    });
                }
                updateCardCount();
            });

            updateCardCount(); // Initial count

            $('#formIA').on('submit', function (e) {
                e.preventDefault();
                var $btn = $('#btnSalvarIA');
                handleAjaxSave($(this), $btn, '<i class="fas fa-save mr-2"></i> ' + "<?php echo __('Salvar Configuração de IA'); ?>", "<?php echo __('IA configurada com sucesso!'); ?>");
            });

            $('#formIdioma').on('submit', function (e) {
                e.preventDefault();
                handleAjaxSave($(this), $(this).find('button[type="submit"]'), $(this).find('button[type="submit"]').html(), "<?php echo __('Idioma atualizado!'); ?>");
            });

            // --- INÍCIO: Lógica de Destinatários de E-mail --- //

            const $searchReq = $('#emailRecipientSearch');
            const $dropdownReq = $('#emailRecipientDropdown');
            const $listReq = $('#activeEmailRecipients');

            let searchTimeoutReq = null;

            // Busca destinatários via AJAX
            $searchReq.on('keyup', function () {
                clearTimeout(searchTimeoutReq);
                const query = $(this).val();

                if (query.length < 2) {
                    $dropdownReq.hide();
                    return;
                }

                searchTimeoutReq = setTimeout(() => {
                    $.ajax({
                        url: 'ajax_alertas_usuarios.php',
                        type: 'POST',
                        data: { action: 'search', query: query },
                        success: function (resp) {
                            $dropdownReq.html(resp).show();
                        }
                    });
                }, 400);
            });

            // Oculta o dropdown quando clica fora
            $(document).on('click', function (e) {
                if (!$(e.target).closest('#emailRecipientSearch, #emailRecipientDropdown').length) {
                    $dropdownReq.hide();
                }
            });

            // Adiciona um novo destinatário
            $(document).on('click', '.select-recipient', function (e) {
                e.preventDefault();
                const uid = $(this).data('uid');

                $.ajax({
                    url: 'ajax_alertas_usuarios.php',
                    type: 'POST',
                    data: { action: 'add', user_id: uid },
                    dataType: 'json',
                    success: function (resp) {
                        if (resp.success) {
                            $searchReq.val('');
                            $dropdownReq.hide();
                            loadActiveRecipients(); // Recarrega a lista
                            showAutoSaved();
                        } else {
                            alert(resp.message || "<?php echo __('Erro ao adicionar destinatário.'); ?>");
                        }
                    }
                });
            });

            // Função para renderizar a lista
            function loadActiveRecipients() {
                $.ajax({
                    url: 'ajax_list_alertas_usuarios.php',
                    type: 'GET',
                    success: function (resp) {
                        $listReq.html(resp);
                    }
                });
            }

            // Ação de Toggle de botões mini
            $(document).on('click', '.mini-badge-btn, .mini-icon-btn', function () {
                // não é o remover
                if ($(this).hasClass('fa-times-circle')) return;

                const uid = $(this).closest('.recipient-item').data('uid');
                const pref = $(this).data('pref');
                const currentState = $(this).hasClass('active') ? 1 : 0;
                const newState = currentState === 1 ? 0 : 1;

                const $btn = $(this);

                $.ajax({
                    url: 'ajax_alertas_usuarios.php',
                    type: 'POST',
                    data: { action: 'toggle', user_id: uid, pref: pref, state: newState },
                    dataType: 'json',
                    success: function (resp) {
                        if (resp.success) {
                            if (newState) {
                                $btn.addClass('active');
                            } else {
                                $btn.removeClass('active');
                            }

                            // Sincronização de Prioridades e Tipos com o ícone de Ticket
                            if (pref === 'recebe_chamados') {
                                const $row = $btn.closest('.recipient-item');
                                const $dependentGroups = $row.find('[data-group="tickets-priority"], [data-group="tickets-type"]');
                                if (newState) {
                                    $dependentGroups.removeClass('group-disabled');
                                } else {
                                    $dependentGroups.addClass('group-disabled');
                                }
                            }
                            // Recarrega ambas as listas para manter sincronia
                            loadActiveRecipients();
                            if (typeof loadActiveEstoqueRecipients === "function") {
                                loadActiveEstoqueRecipients();
                            }
                            showAutoSaved();
                        }
                    }
                });
            });

            // Remover destinatário
            $(document).on('click', '.remove-recipient', function () {
                if (!confirm("<?php echo __('Deseja remover este destinatário da lista de E-mail globais?'); ?>")) return;

                const uid = $(this).closest('.recipient-item').data('uid');

                $.ajax({
                    url: 'ajax_alertas_usuarios.php',
                    type: 'POST',
                    data: { action: 'remove', user_id: uid },
                    dataType: 'json',
                    success: function (resp) {
                        if (resp.success) {
                            loadActiveRecipients();
                            showAutoSaved();
                        }
                    }
                });
            });

            // Carrega na inicialização
            loadActiveRecipients();

            // --- FIM: Lógica de Destinatários de E-mail --- //

            // Auto-save WhatsApp configurations when checkboxes change
            $('#formAlertasConfig .badge-checkbox, #formAlertasConfig .channel-toggle').on('change', function () {
                $('#formAlertasConfig').submit();
            });

            // ══════════════════════════════════════════════════
            // BLOCO SMTP – lógica de botões
            // ══════════════════════════════════════════════════
            function smtpShowEdit() {
                $('#smtpReadMode').slideUp(200, function () {
                    $('#smtpEditMode').slideDown(200);
                });
                $('#smtpTestResult').hide().html('');
            }

            function smtpShowRead() {
                $('#smtpEditMode').slideUp(200, function () {
                    $('#smtpReadMode').slideDown(200);
                });
                $('#smtpTestResult').hide().html('');
            }

            function smtpShowResult(success, msg) {
                const cls = success ? 'alert-success' : 'alert-danger';
                const ico = success ? 'fa-check-circle' : 'fa-exclamation-triangle';
                $('#smtpTestResult')
                    .html('<div class="alert ' + cls + ' py-2 small mb-0"><i class="fas ' + ico + ' mr-2"></i>' + msg + '</div>')
                    .show();
            }

            function smtpDoTest() {
                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>' + "<?php echo __('Testando...'); ?>");
                $('#smtpTestResult').hide();

                $.ajax({
                    url: 'ajax_smtp.php',
                    type: 'POST',
                    data: { action: 'test' },
                    dataType: 'json',
                    success: function (r) {
                        smtpShowResult(r.success, r.message);
                    },
                    error: function () {
                        smtpShowResult(false, "<?php echo __('Erro de comunicação com o servidor.'); ?>");
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i>' + "<?php echo __('Testar Conexão'); ?>");
                    }
                });
            }

            // Botões Editar / Cancelar
            // Botões Editar / Cancelar
            $('#btnSmtpEdit').on('click', smtpShowEdit);
            $('#btnSmtpCancel').on('click', smtpShowRead);

            // Mostrar / ocultar senha
            $('#btnTogglePass').on('click', function () {
                const $inp = $('#smtp_pass');
                const isPass = $inp.attr('type') === 'password';
                $inp.attr('type', isPass ? 'text' : 'password');
                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
            });

            // Botões Testar (modo leitura e modo edição)
            $('#btnSmtpTest, #btnSmtpTestEdit').on('click', smtpDoTest);

            // Salvar via AJAX
            $('#btnSmtpSave').on('click', function () {
                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>' + "<?php echo __('Salvando...'); ?>");

                $.ajax({
                    url: 'ajax_smtp.php',
                    type: 'POST',
                    data: { action: 'save', ...$('#formSmtpConfig').serializeArray().reduce((o, f) => ({ ...o, [f.name]: f.value }), {}) },
                    dataType: 'json',
                    success: function (r) {
                        if (r.success) {
                            showAutoSaved("<?php echo __('Configurações SMTP salvas!'); ?>");
                            smtpShowRead();
                            // Atualiza o display de leitura com novos valores sem recarregar
                            location.reload();
                        } else {
                            smtpShowResult(false, r.message || "<?php echo __('Erro ao salvar.'); ?>");
                        }
                    },
                    error: function () {
                        smtpShowResult(false, "<?php echo __('Erro de comunicação com o servidor.'); ?>");
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>' + "<?php echo __('Salvar Configurações'); ?>");
                    }
                });
            });
            // ══ FIM BLOCO SMTP ════════════════════════════════

            // ══════════════════════════════════════════════════
            // LÓGICA DE DESTINATÁRIOS DE ESTOQUE
            // ══════════════════════════════════════════════════
            const $searchEst = $('#estoqueRecipientSearch');
            const $dropdownEst = $('#estoqueRecipientDropdown');
            const $listEst = $('#activeEstoqueRecipients');

            function loadActiveEstoqueRecipients() {
                $.ajax({
                    url: 'ajax_list_estoque_usuarios.php',
                    type: 'GET',
                    success: function (resp) {
                        $listEst.html(resp);
                    }
                });
            }

            $searchEst.on('keyup', function () {
                const query = $(this).val();
                if (query.length < 2) { $dropdownEst.hide(); return; }

                $.ajax({
                    url: 'ajax_alertas_usuarios.php',
                    type: 'POST',
                    data: { action: 'search', query: query },
                    success: function (resp) {
                        $dropdownEst.html(resp.replace(/select-recipient/g, 'select-estoque-recipient')).show();
                    }
                });
            });

            $(document).on('click', '.select-estoque-recipient', function (e) {
                e.preventDefault();
                const uid = $(this).data('uid');
                $.ajax({
                    url: 'ajax_alertas_usuarios.php',
                    type: 'POST',
                    data: { action: 'add', user_id: uid, context: 'estoque' },
                    dataType: 'json',
                    success: function (resp) {
                        // Se falhou por já existir no sistema global, apenas tentamos o toggle
                        if (!resp.success) {
                            $.ajax({
                                url: 'ajax_alertas_usuarios.php',
                                type: 'POST',
                                data: { action: 'toggle', user_id: uid, pref: 'recebe_estoque', state: 1 },
                                dataType: 'json',
                                success: function (r2) {
                                    if (r2.success) {
                                        $searchEst.val(''); $dropdownEst.hide();
                                        loadActiveEstoqueRecipients(); loadActiveRecipients(); showAutoSaved();
                                    }
                                }
                            });
                        } else {
                            $searchEst.val(''); $dropdownEst.hide();
                            loadActiveEstoqueRecipients(); loadActiveRecipients(); showAutoSaved();
                        }
                    }
                });
            });

            $(document).on('click', '.remove-estoque-recipient', function () {
                const uid = $(this).closest('.recipient-item').data('uid');
                $.ajax({
                    url: 'ajax_alertas_usuarios.php',
                    type: 'POST',
                    data: { action: 'toggle', user_id: uid, pref: 'recebe_estoque', state: 0 },
                    dataType: 'json',
                    success: function (resp) {
                        if (resp.success) {
                            loadActiveEstoqueRecipients(); loadActiveRecipients(); showAutoSaved();
                        }
                    }
                });
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#estoqueRecipientSearch, #estoqueRecipientDropdown').length) {
                    $dropdownEst.hide();
                }
            });

            loadActiveEstoqueRecipients();

        });
    </script>
    <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script>
        $(document).ready(function () {
            // Preview do logo ao selecionar arquivo
            $('#logo_file').on('change', function () {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);

                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#logoPreview').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Teste de Chave de API
            $('#btnTestarChave').on('click', function() {
                var key = $('#gemini_api_key').val().trim();
                if (!key) {
                    alert('<?php echo __('Por favor, insira uma chave no campo antes de testar.'); ?>');
                    return;
                }
                var $btn = $(this);
                var oldHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i><?php echo __('Testando...'); ?>');

                $.ajax({
                    url: 'ajax_test_ai_key.php',
                    type: 'POST',
                    data: { api_key: key },
                    success: function(response) {
                        $btn.prop('disabled', false).html(oldHtml);
                        if (response.success) {
                            alert('✅ Sucesso: ' + response.message);
                        } else {
                            alert('❌ Erro: ' + response.message);
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).html(oldHtml);
                        alert('<?php echo __('Erro ao tentar contato com o servidor de teste.'); ?>');
                    }
                });
            });

            // Upload via AJAX
            $('#formLogoUpload').on('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                var $btn = $('#btnUploadLogo');
                var oldHtml = $btn.html();

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i><?php echo __('Enviando...'); ?>');

                $.ajax({
                    url: 'configuracoes.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        try {
                            var res = JSON.parse(response);
                            if (res.success) {
                                $('#logoPreview').attr('src', res.path + '?v=' + new Date().getTime());
                                showAutoSave('<?php echo __('Logotipo atualizado!'); ?>');
                                // Clear the file input after successful upload
                                $('#logo_file').val('');
                                $('#logo_file').next('.custom-file-label').html('<?php echo __('Escolher arquivo...'); ?>');
                            } else {
                                alert(res.message);
                            }
                        } catch (err) {
                            console.error(err);
                            alert('<?php echo __('Erro ao processar resposta do servidor'); ?>');
                        }
                    },
                    error: function () {
                        alert('<?php echo __('Erro na requisição'); ?>');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html(oldHtml);
                    }
                });
            });
        });

        // Função global para mostrar status de salvamento
        function showAutoSave(message) {
            var $toast = $('#autoSaveStatus');
            $('#autoSaveMessage').text(message);
            $toast.fadeIn().delay(3000).fadeOut();
        }
    </script>
</body>

</html>
```