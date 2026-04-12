<?php include 'performance_header.php'; ?>
<?php
/**
 * TELA DE LOGIN: index.php
 * Ponto de entrada do sistema. Gerencia a autenticação inicial e redirecionamento de sessão.
 */
session_start();
include_once 'auth.php';     // Validação de segurança obrigatória
include_once 'conexao.php';  // Conexão com a base de dados

// === Toda a sua lógica de SQL deve vir aqui, antes do HTML ===
$meses = [
    1 => __('Janeiro'),
    2 => __('Fevereiro'),
    3 => __('Março'),
    4 => __('Abril'),
    5 => __('Maio'),
    6 => __('Junho'),
    7 => __('Julho'),
    8 => __('Agosto'),
    9 => __('Setembro'),
    10 => __('Outubro'),
    11 => __('Novembro'),
    12 => __('Dezembro')
];
$ano_atual = date('Y');
$count_aberto = 0;
$count_andamento = 0;
// ... (mantenha o restante das suas queries SQL que estavam no corpo do código aqui)

// Apenas após processar tudo, começamos o HTML
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Dashboard'); ?> - Asset Mgt</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=f3d9abe8d5aa7831c01bfaa2a1563712">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=41e93b37bc495fd67938799bb3a6adaf">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet" href="/assets/css/Simple-footer-by-krissy.css?h=73316da5ae5ad6b51632cd2e5413f263">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <?php renderPerformanceHints(); ?>
    <?php include_once 'sidebar_style.php'; ?>
    <style>
        /* PREMIUM DESIGN SYSTEM */
        body.premium-bg, #content-wrapper.premium-bg {
            background-color: #f8fafc !important;
            background-image: radial-gradient(circle at top right, rgba(99,102,241,0.08), transparent 400px), radial-gradient(circle at bottom left, rgba(16,185,129,0.05), transparent 400px) !important;
            font-family: 'Inter', sans-serif !important;
        }
        .premium-card {
            background: rgba(255, 255, 255, 0.90) !important;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6) !important;
            border-radius: 16px !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .premium-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.08) !important;
        }
        .card-icon-modern {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(0,0,0,0.03);
            transition: all 0.3s ease;
        }
        .premium-card:hover .card-icon-modern {
            transform: scale(1.1);
            background: rgba(0,0,0,0.06);
        }
        .progress-premium {
            background-color: rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
            height: 12px !important;
        }
        .progress-bar-premium {
            border-radius: 8px;
            transition: width 1.5s ease-in-out;
        }
        .avatar-premium {
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .avatar-premium:hover {
            transform: scale(1.1);
        }
        .form-control-premium {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }
        .form-control-premium:focus {
            background-color: #fff;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
            outline: none;
        }
        .btn-modern {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
            letter-spacing: 0.5px;
            font-size: 0.75rem;
            transition: all 0.2s;
        }
        .btn-modern:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .font-inter { font-family: 'Inter', sans-serif !important; }
        .text-slate-800 { color: #1e293b !important; }
        .text-slate-500 { color: #64748b !important; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<?php
// === PROCESSAMENTO DE DADOS: Contagem de Chamados com base no nível de acesso ===
$count_aberto = 0;
$count_andamento = 0;
$count_pendente = 0;
$data = [];
$total_ativos = 0;

$where_chamados = "";
if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    $where_chamados = " AND usuario_id = " . $_SESSION['id_usuarios'];
}

$res = mysqli_query($conn, "SELECT status, COUNT(*) as total FROM chamados WHERE status IN ('Aberto', 'Em Andamento', 'Em Atendimento', 'Pendente') $where_chamados GROUP BY status");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$row['status']] = $row['total'];
        $total_ativos += $row['total'];
        if ($row['status'] == 'Aberto')
            $count_aberto = $row['total'];
        elseif ($row['status'] == 'Em Andamento' || $row['status'] == 'Em Atendimento')
            $count_andamento += $row['total'];
        elseif ($row['status'] == 'Pendente')
            $count_pendente = $row['total'];
    }
}
$data_string = implode(",", [
    isset($data['Aberto']) ? $data['Aberto'] : 0,
    $count_andamento,
    isset($data['Pendente']) ? $data['Pendente'] : 0
]);

// === Chamados Fechados por Mês ===
$closed_data = array_fill(1, 12, 0);
$res_closed = mysqli_query($conn, "SELECT MONTH(data_fechamento) as month, COUNT(*) as count FROM chamados WHERE status IN ('Resolvido', 'Fechado', 'Cancelado') AND YEAR(data_fechamento) = YEAR(CURRENT_DATE()) $where_chamados GROUP BY MONTH(data_fechamento)");
if ($res_closed) {
    while ($row = mysqli_fetch_assoc($res_closed)) {
        $closed_data[$row['month']] = $row['count'];
    }
}
$closed_string = implode(",", $closed_data);

// === SLA de Primeiro Atendimento (Mês Atual) ===
$sla_pr_min = 10;
$res_sla_cfg = mysqli_query($conn, "SELECT sla_primeira_resposta_minutos FROM configuracoes_sla LIMIT 1");
if ($res_sla_cfg && mysqli_num_rows($res_sla_cfg) > 0) {
    $row_sla_cfg = mysqli_fetch_assoc($res_sla_cfg);
    $sla_pr_min = (int)($row_sla_cfg['sla_primeira_resposta_minutos'] ?? 10) ?: 10;
}

$sql_sla_pr = "SELECT 
    SUM(CASE WHEN data_primeira_resposta IS NOT NULL AND TIMESTAMPDIFF(MINUTE, data_abertura, data_primeira_resposta) <= $sla_pr_min THEN 1 ELSE 0 END) as dentro,
    SUM(CASE WHEN data_primeira_resposta IS NOT NULL AND TIMESTAMPDIFF(MINUTE, data_abertura, data_primeira_resposta) > $sla_pr_min THEN 1 ELSE 0 END) as fora,
    SUM(CASE WHEN data_primeira_resposta IS NULL THEN 1 ELSE 0 END) as sem_resposta
FROM chamados 
WHERE MONTH(data_abertura) = MONTH(CURRENT_DATE()) AND YEAR(data_abertura) = YEAR(CURRENT_DATE()) $where_chamados";

$res_sla_pr = mysqli_query($conn, $sql_sla_pr);
$sla_pr_data = mysqli_fetch_assoc($res_sla_pr);
$sla_pr_string = implode(",", [
    $sla_pr_data['dentro'] ?? 0,
    $sla_pr_data['fora'] ?? 0,
    $sla_pr_data['sem_resposta'] ?? 0
]);
$total_sla_pr = ($sla_pr_data['dentro'] ?? 0) + ($sla_pr_data['fora'] ?? 0) + ($sla_pr_data['sem_resposta'] ?? 0);

// === RANKING DE SLA ===
$mes_filtro = !empty($_GET['mes_ranking']) ? intval($_GET['mes_ranking']) : date('n');
$ano_filtro = !empty($_GET['ano_ranking']) ? intval($_GET['ano_ranking']) : date('Y');

$sql_ranking = "SELECT r.nome, r.sobrenome, r.foto_perfil, COUNT(*) as total,
    SUM(CASE WHEN (TIMESTAMPDIFF(MINUTE, c.data_abertura, c.data_fechamento) - COALESCE(c.tempo_congelado_minutos, 0)) <= 
        (CASE WHEN s.tempo_sla_minutos IS NOT NULL THEN s.tempo_sla_minutos WHEN c.categoria = 'Incidente' THEN 360 WHEN c.categoria = 'Mudança' THEN 1440 WHEN c.categoria = 'Requisição' THEN 2880 ELSE 1440 END * 
         CASE WHEN c.prioridade = 'P1' THEN 1/6.0 WHEN c.prioridade = 'P2' OR c.prioridade = 'Alta' THEN 1/3.0 WHEN c.prioridade = 'P3' OR c.prioridade = 'Média' THEN 2/3.0 ELSE 1.0 END) THEN 1 ELSE 0 END) as met_sla
    FROM chamados c
    JOIN usuarios r ON c.responsavel_id = r.id_usuarios
    LEFT JOIN configuracoes_sla s ON c.categoria = s.categoria
    WHERE c.status IN ('Resolvido', 'Fechado', 'Cancelado') AND MONTH(c.data_fechamento) = $mes_filtro AND YEAR(c.data_fechamento) = $ano_filtro
    GROUP BY r.id_usuarios
    ORDER BY (SUM(CASE WHEN (TIMESTAMPDIFF(MINUTE, c.data_abertura, c.data_fechamento) - COALESCE(c.tempo_congelado_minutos, 0)) <= 
        (CASE WHEN s.tempo_sla_minutos IS NOT NULL THEN s.tempo_sla_minutos WHEN c.categoria = 'Incidente' THEN 360 WHEN c.categoria = 'Mudança' THEN 1440 WHEN c.categoria = 'Requisição' THEN 2880 ELSE 1440 END * 
         CASE WHEN c.prioridade = 'P1' THEN 1/6.0 WHEN c.prioridade = 'P2' OR c.prioridade = 'Alta' THEN 1/3.0 WHEN c.prioridade = 'P3' OR c.prioridade = 'Média' THEN 2/3.0 ELSE 1.0 END) THEN 1 ELSE 0 END) / COUNT(*)) DESC LIMIT 5";

$ranking_data = [];
$res_rank = mysqli_query($conn, $sql_ranking);
if ($res_rank && mysqli_num_rows($res_rank) > 0) {
    while ($row = mysqli_fetch_assoc($res_rank)) {
        $row['percentage'] = $row['total'] > 0 ? round(($row['met_sla'] / $row['total']) * 100) : 0;
        $ranking_data[] = $row;
    }
}

// === RANKING DE RECORRÊNCIA ===
$mes_rec_filtro = !empty($_GET['mes_recorrencia']) ? intval($_GET['mes_recorrencia']) : date('n');
$ano_rec_filtro = !empty($_GET['ano_recorrencia']) ? intval($_GET['ano_recorrencia']) : date('Y');
$sql_rec = "SELECT TRIM(titulo) as titulo, COUNT(*) as total FROM chamados WHERE MONTH(data_abertura) = $mes_rec_filtro AND YEAR(data_abertura) = $ano_rec_filtro GROUP BY TRIM(titulo) ORDER BY total DESC LIMIT 5";
$recorrencia_data = [];
$max_recorrencia = 0;
$res_rec = mysqli_query($conn, $sql_rec);
if ($res_rec) {
    while ($row = mysqli_fetch_assoc($res_rec)) {
        if ($row['total'] > $max_recorrencia)
            $max_recorrencia = $row['total'];
        $recorrencia_data[] = $row;
    }
}

// === ALERTAS (Licenças Expiradas ou perto de expirar) ===
$res_exp = mysqli_query($conn, "SELECT COUNT(*) as total FROM licencas WHERE (data_expiracao < DATE_ADD(CURRENT_DATE(), INTERVAL 60 DAY) OR status = 'Expirada') AND status != 'Cancelada'");
$count_exp = mysqli_fetch_assoc($res_exp)['total'] ?? 0;

// === CONFIGURAÇÃO DOS CARDS DO DASHBOARD ===
$res_config = mysqli_query($conn, "SELECT dashboard_cards FROM configuracoes_alertas WHERE id = 1");
$row_config = mysqli_fetch_assoc($res_config);
$selected_cards = !empty($row_config['dashboard_cards']) ? json_decode($row_config['dashboard_cards'], true) : ['cat:Desktop', 'cat:Notebook', 'cat:Monitor', 'cat:Impressoras'];

$metrics = [];
// Metrics: Categories
$sql_cats = "SELECT categoria, COUNT(*) as total, SUM(CASE WHEN (assigned_to IS NULL OR assigned_to = 0) AND id_asset NOT IN (SELECT id_asset FROM manutencao WHERE status_manutencao = 'Em Manutenção') THEN 1 ELSE 0 END) as disponiveis FROM ativos GROUP BY categoria";
$res_cats = mysqli_query($conn, $sql_cats);
if ($res_cats) {
    while ($row = mysqli_fetch_assoc($res_cats)) {
        $metrics["cat:" . $row['categoria']] = $row;
    }
}

// Metrics: Status
$res_disp = mysqli_query($conn, "SELECT COUNT(*) as total FROM ativos WHERE (assigned_to IS NULL OR assigned_to = 0) AND id_asset NOT IN (SELECT id_asset FROM manutencao WHERE status_manutencao = 'Em Manutenção')");
$metrics['st:Disponível'] = ['total' => mysqli_fetch_assoc($res_disp)['total'] ?? 0, 'disponiveis' => 0];
$res_uso = mysqli_query($conn, "SELECT COUNT(*) as total FROM ativos WHERE (assigned_to IS NOT NULL AND assigned_to != 0) AND id_asset NOT IN (SELECT id_asset FROM manutencao WHERE status_manutencao = 'Em Manutenção')");
$metrics['st:Em uso'] = ['total' => mysqli_fetch_assoc($res_uso)['total'] ?? 0, 'disponiveis' => 0];
$res_manut = mysqli_query($conn, "SELECT COUNT(*) as total FROM manutencao WHERE status_manutencao = 'Em Manutenção'");
$metrics['st:Em manutenção'] = ['total' => mysqli_fetch_assoc($res_manut)['total'] ?? 0, 'disponiveis' => 0];

// Metrics: Licenses
$sql_lics = "SELECT software, SUM(quantidade_total) as total, SUM(quantidade_total - quantidade_uso) as disponiveis FROM licencas GROUP BY software";
$res_lics = mysqli_query($conn, $sql_lics);
if ($res_lics) {
    while ($row = mysqli_fetch_assoc($res_lics)) {
        $metrics["lic:" . $row['software']] = $row;
    }
}

function getCardIcon($type, $name)
{
    if ($type === 'st') {
        switch ($name) {
            case 'Disponível':
                return 'fa-check-circle';
            case 'Em uso':
                return 'fa-user-check';
            case 'Em manutenção':
                return 'fa-tools';
            default:
                return 'fa-info-circle';
        }
    } elseif ($type === 'lic') {
        $n = strtolower($name);
        if (strpos($n, 'office') !== false || strpos($n, '365') !== false)
            return 'fa-file-word';
        if (strpos($n, 'windows') !== false)
            return 'fa-windows';
        if (strpos($n, 'adobe') !== false || strpos($n, 'photoshop') !== false)
            return 'fa-paint-brush';
        if (strpos($n, 'antivirus') !== false || strpos($n, 'kaspersky') !== false || strpos($n, 'defender') !== false)
            return 'fa-shield-alt';
        return 'fa-key';
    } else {
        $n = strtolower($name);
        if (strpos($n, 'desktop') !== false)
            return 'fa-desktop';
        if (strpos($n, 'notebook') !== false)
            return 'fa-laptop';
        if (strpos($n, 'monitor') !== false)
            return 'fa-desktop';
        if (strpos($n, 'impressora') !== false)
            return 'fa-print';
        if (strpos($n, 'servidor') !== false)
            return 'fa-server';
        if (strpos($n, 'roteador') !== false || strpos($n, 'switch') !== false)
            return 'fa-network-wired';
        return 'fa-box';
    }
}

function getCardColor($type, $name)
{
    if ($type === 'st') {
        switch ($name) {
            case 'Disponível':
                return 'success';
            case 'Em uso':
                return 'primary';
            case 'Em manutenção':
                return 'warning';
            default:
                return 'secondary';
        }
    } elseif ($type === 'lic') {
        return 'info';
    } else {
        $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary', 'dark'];
        $sum = array_sum(str_split(md5($name)));
        return $colors[$sum % count($colors)];
    }
}
?>

<body id="page-top" class="premium-bg font-inter">
    <?php startNProgress(); ?>
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include 'sidebar_brand.php'; ?>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column premium-page-fade premium-bg" id="content-wrapper">
            <div id="content">
                <?php include 'topbar.php'; ?>
                <div class="container-fluid" style="padding-left: 23px; padding-right: 23px;">
                    <div class="d-sm-flex justify-content-between align-items-center mb-4">
                        <h3 class="text-dark mb-0"><i class="fas fa-chart-pie mr-2 text-primary"></i><?php echo __('Dashboard'); ?></h3>
                        <div class="d-flex align-items-center">
                            <?php
                            $where_closed = "";
                            if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
                                $where_closed = " AND usuario_id = " . $_SESSION['id_usuarios'];
                            }
                            $sql_total_fechados = "SELECT COUNT(*) as total FROM chamados WHERE status IN ('Resolvido', 'Fechado', 'Cancelado') $where_closed";
                            $res_total_fechados = mysqli_query($conn, $sql_total_fechados);
                            $total_fechados = mysqli_fetch_assoc($res_total_fechados)['total'] ?? 0;
                            ?>
                            <div class="bg-success text-white px-2 py-1 rounded shadow-sm d-flex align-items-center mr-2"
                                style="font-size: 0.75rem; height: 31px;">
                                <i class="fas fa-check-circle mr-1"></i>
                                <span class="font-weight-bold"><?php echo __('Total de chamados fechados:'); ?>
                                    <?php echo $total_fechados; ?></span>
                            </div>
                            <a class="text-white px-2 py-1 rounded shadow-sm d-flex align-items-center text-decoration-none"
                                role="button" href="relatorio_resumo_geral.php" target="_blank"
                                onkeypress="if(event.key==='Enter')window.open('relatorio_resumo_geral.php', '_blank')"
                                style="background: #e74a3b; font-size: 0.75rem; height: 31px;">
                                <i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i>
                                <span class="font-weight-bold"><?php echo __('Gerar Relatório'); ?></span>
                            </a>
                        </div>
                    </div>
                    <div class="row px-2 flex-nowrap overflow-auto">
                        <?php
                        // Render Cards
                        foreach ($selected_cards as $idx => $card_key) {
                            if (strpos($card_key, ':') === false)
                                continue;
                            list($type, $name) = explode(':', $card_key, 2);
                            $data = $metrics[$card_key] ?? ['total' => 0, 'disponiveis' => 0];
                            $icon = getCardIcon($type, $name);
                            $color = getCardColor($type, $name);
                            $delay = ($idx + 1) * 0.1;
                            ?>
                            <div class="col-xl-1-5 col-md-3 mb-4 px-1 animate__animated animate__fadeInUp"
                                style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 155px; animation-delay: <?php echo $delay; ?>s;">
                                <div class="card premium-card border-left-<?php echo $color; ?> py-2"
                                    style="height: 100%;">
                                    <div class="card-body p-3">
                                        <div class="row align-items-center no-gutters">
                                            <div class="col mr-2">
                                                <div
                                                    class="text-uppercase text-<?php echo $color; ?> font-weight-bold text-xs mb-1" style="font-weight: 700; letter-spacing: 0.05em;">
                                                    <span><?php echo __($name); ?></span>
                                                </div>
                                                <div class="text-slate-800 font-weight-bold h4 mb-0">
                                                    <span><?php echo $data['total']; ?></span>
                                                    <div class="text-slate-500 small mt-1 font-weight-medium"
                                                        style="font-size: 0.70rem; line-height: 1;">
                                                        <?php echo $data['disponiveis']; ?> <?php echo __('Disp.'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <div class="card-icon-modern">
                                                    <i class="fas <?php echo $icon; ?> fa-lg text-<?php echo $color; ?>" style="opacity: 0.8"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                    <div class="row mb-5">
                        <div class="col-lg-6 col-xl-6">
                            <div class="card shadow mb-4 h-100">
                                <div class="card-header d-flex justify-content-between align-items-center"
                                    style="background: rgb(248, 249, 252);">
                                    <h6 class="text-primary font-weight-bold m-0">
                                        <?php echo __('Chamados Finalizados (Mês)'); ?>
                                    </h6>
                                    <div class="dropdown no-arrow"><button class="btn btn-link btn-sm dropdown-toggle"
                                            aria-expanded="false" data-toggle="dropdown" type="button"><i
                                                 class="fas fa-ellipsis-v text-gray-400"></i></button>
                                        <div class="dropdown-menu shadow dropdown-menu-right animated--fade-in">
                                            <p class="text-center dropdown-header">Opções:</p><a class="dropdown-item"
                                                href="chamados.php?filtro_status=finalizados">&nbsp;<?php echo __('Ver Finalizados'); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-center">
                                    <div class="chart-area"><canvas
                                            data-bss-chart="{&quot;type&quot;:&quot;line&quot;,&quot;data&quot;:{&quot;labels&quot;:[&quot;<?php echo __('Jan'); ?>&quot;,&quot;<?php echo __('Fev'); ?>&quot;,&quot;<?php echo __('Mar'); ?>&quot;,&quot;<?php echo __('Abr'); ?>&quot;,&quot;<?php echo __('Mai'); ?>&quot;,&quot;<?php echo __('Jun'); ?>&quot;,&quot;<?php echo __('Jul'); ?>&quot;,&quot;<?php echo __('Ago'); ?>&quot;,&quot;<?php echo __('Set'); ?>&quot;,&quot;<?php echo __('Out'); ?>&quot;,&quot;<?php echo __('Nov'); ?>&quot;,&quot;<?php echo __('Dez'); ?>&quot;],&quot;datasets&quot;:[{&quot;label&quot;:&quot;<?php echo __('Chamados'); ?>&quot;,&quot;fill&quot;:true,&quot;data&quot;:[<?php echo $closed_string; ?>],&quot;backgroundColor&quot;:&quot;rgba(78, 115, 223, 0.05)&quot;,&quot;borderColor&quot;:&quot;rgb(26, 121, 158)&quot;}]},&quot;options&quot;:{&quot;maintainAspectRatio&quot;:false,&quot;legend&quot;:{&quot;display&quot;:false,&quot;labels&quot;:{&quot;fontStyle&quot;:&quot;normal&quot;}},&quot;title&quot;:{&quot;fontStyle&quot;:&quot;normal&quot;},&quot;scales&quot;:{&quot;xAxes&quot;:[{&quot;gridLines&quot;:{&quot;color&quot;:&quot;rgb(234, 236, 244)&quot;,&quot;zeroLineColor&quot;:&quot;rgb(234, 236, 244)&quot;,&quot;drawBorder&quot;:false,&quot;drawTicks&quot;:false,&quot;borderDash&quot;:[&quot;2&quot;],&quot;zeroLineBorderDash&quot;:[&quot;2&quot;],&quot;drawOnChartArea&quot;:false},&quot;ticks&quot;:{&quot;fontColor&quot;:&quot;#858796&quot;,&quot;fontStyle&quot;:&quot;normal&quot;,&quot;padding&quot;:20}}],&quot;yAxes&quot;:[{&quot;gridLines&quot;:{&quot;color&quot;:&quot;rgb(234, 236, 244)&quot;,&quot;zeroLineColor&quot;:&quot;rgb(234, 236, 244)&quot;,&quot;drawBorder&quot;:false,&quot;drawTicks&quot;:false,&quot;borderDash&quot;:[&quot;2&quot;],&quot;zeroLineBorderDash&quot;:[&quot;2&quot;]},&quot;ticks&quot;:{&quot;fontColor&quot;:&quot;#858796&quot;,&quot;fontStyle&quot;:&quot;normal&quot;,&quot;padding&quot;:20}}]}}}"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-xl-3">
                            <div class="card shadow mb-4 h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="text-primary font-weight-bold m-0">
                                        <?php echo __('Status dos Chamados'); ?>
                                    </h6>
                                    <div class="dropdown no-arrow"><button class="btn btn-link btn-sm dropdown-toggle"
                                            aria-expanded="false" data-toggle="dropdown" type="button"><i
                                                class="fas fa-ellipsis-v text-gray-400"></i></button>
                                        <div class="dropdown-menu shadow dropdown-menu-right animated--fade-in">
                                            <p class="text-center dropdown-header">Opções:</p><a class="dropdown-item"
                                                href="chamados.php">&nbsp;<?php echo __('Ver Chamados'); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div class="chart-area" style="position: relative;">
                                        <canvas
                                            data-bss-chart="{&quot;type&quot;:&quot;doughnut&quot;,&quot;data&quot;:{&quot;labels&quot;:[&quot;<?php echo __('Aberto'); ?>&quot;,&quot;<?php echo __('Em Andamento'); ?>&quot;,&quot;<?php echo __('Pendente'); ?>&quot;],&quot;datasets&quot;:[{&quot;label&quot;:&quot;&quot;,&quot;backgroundColor&quot;:[&quot;#4e73df&quot;,&quot;#36b9cc&quot;,&quot;#f6c23e&quot;],&quot;borderColor&quot;:[&quot;#ffffff&quot;,&quot;#ffffff&quot;,&quot;#ffffff&quot;],&quot;data&quot;:[<?php echo $data_string; ?>]}]},&quot;options&quot;:{&quot;maintainAspectRatio&quot;:false,&quot;cutoutPercentage&quot;:80,&quot;legend&quot;:{&quot;display&quot;:false,&quot;labels&quot;:{&quot;fontStyle&quot;:&quot;normal&quot;}},&quot;title&quot;:{&quot;fontStyle&quot;:&quot;normal&quot;},&quot;animation&quot;:{&quot;animateRotate&quot;:true,&quot;animateScale&quot;:true,&quot;duration&quot;:2500},&quot;tooltips&quot;:{&quot;backgroundColor&quot;:&quot;#fff&quot;,&quot;bodyFontColor&quot;:&quot;#858796&quot;,&quot;borderColor&quot;:&quot;#dddfeb&quot;,&quot;borderWidth&quot;:1,&quot;xPadding&quot;:15,&quot;yPadding&quot;:15,&quot;displayColors&quot;:false,&quot;caretPadding&quot;:10}}}"></canvas>
                                        <div
                                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 3rem; font-weight: 800; color: #5a5c69; pointer-events: none;">
                                            <?php echo $total_ativos; ?>
                                        </div>
                                    </div>
                                    <div class="text-center small mt-4"><span class="mr-2"><i
                                                class="fas fa-circle text-primary"></i>
                                            <?php echo __('Aberto'); ?></span><span class="mr-2"><i
                                                class="fas fa-circle text-info"></i>
                                            <?php echo __('Em And.'); ?></span><span class="mr-2"><i
                                                class="fas fa-circle text-warning"></i>
                                            <?php echo __('Pendente'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <div class="col-lg-3 col-xl-3">
                            <div class="card shadow mb-4 h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="text-primary font-weight-bold m-0">
                                        <?php echo __('SLA 1ª Resposta'); ?>
                                    </h6>
                                    <div class="dropdown no-arrow"><button class="btn btn-link btn-sm dropdown-toggle"
                                            aria-expanded="false" data-toggle="dropdown" type="button"><i
                                                class="fas fa-ellipsis-v text-gray-400"></i></button>
                                        <div class="dropdown-menu shadow dropdown-menu-right animated--fade-in">
                                            <p class="text-center dropdown-header">Opções:</p><a class="dropdown-item"
                                                href="relatorio_chamados_sla_primeiro_atendimento.php" target="_blank">&nbsp;<?php echo __('Relatório PDF'); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div class="chart-area" style="position: relative;">
                                        <canvas
                                            data-bss-chart="{&quot;type&quot;:&quot;doughnut&quot;,&quot;data&quot;:{&quot;labels&quot;:[&quot;<?php echo __('Dentro'); ?>&quot;,&quot;<?php echo __('Fora'); ?>&quot;,&quot;<?php echo __('Sem Resposta'); ?>&quot;],&quot;datasets&quot;:[{&quot;label&quot;:&quot;&quot;,&quot;backgroundColor&quot;:[&quot;#1cc88a&quot;,&quot;#e74a3b&quot;,&quot;#858796&quot;],&quot;borderColor&quot;:[&quot;#ffffff&quot;,&quot;#ffffff&quot;,&quot;#ffffff&quot;],&quot;data&quot;:[<?php echo $sla_pr_string; ?>]}]},&quot;options&quot;:{&quot;maintainAspectRatio&quot;:false,&quot;cutoutPercentage&quot;:80,&quot;legend&quot;:{&quot;display&quot;:false,&quot;labels&quot;:{&quot;fontStyle&quot;:&quot;normal&quot;}},&quot;title&quot;:{&quot;fontStyle&quot;:&quot;normal&quot;},&quot;animation&quot;:{&quot;animateRotate&quot;:true,&quot;animateScale&quot;:true,&quot;duration&quot;:2500},&quot;tooltips&quot;:{&quot;backgroundColor&quot;:&quot;#fff&quot;,&quot;bodyFontColor&quot;:&quot;#858796&quot;,&quot;borderColor&quot;:&quot;#dddfeb&quot;,&quot;borderWidth&quot;:1,&quot;xPadding&quot;:15,&quot;yPadding&quot;:15,&quot;displayColors&quot;:false,&quot;caretPadding&quot;:10}}}"></canvas>
                                        <div
                                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 3rem; font-weight: 800; color: #5a5c69; pointer-events: none;">
                                            <?php echo $total_sla_pr; ?>
                                        </div>
                                    </div>
                                    <div class="text-center small mt-4"><span class="mr-2"><i
                                                class="fas fa-circle text-success"></i>
                                            <?php echo __('Dentro'); ?></span><span class="mr-2"><i
                                                class="fas fa-circle text-danger"></i>
                                            <?php echo __('Fora'); ?></span><span class="mr-2"><i
                                                class="fas fa-circle text-secondary"></i>
                                            <?php echo __('S. Resp.'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- End: Charts -->
                    <!-- Start: SLA Ranking -->
                    <?php if ($_SESSION['nivelUsuario'] == 'Admin' || $_SESSION['nivelUsuario'] == 'Suporte'): ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card premium-card mb-4">
                                    <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center" style="background: transparent; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                        <h6 class="m-0 font-weight-bold text-slate-800">
                                            <i class="fas fa-trophy text-warning mr-2"></i><?php echo __('Ranking de SLA - Melhores Técnicos'); ?>
                                        </h6>
                                        <form method="GET" action="index.php" class="form-inline mt-2 mt-md-0">
                                            <!-- Preservar filtro de recorrência ao filtrar SLA -->
                                            <input type="hidden" name="mes_recorrencia"
                                                value="<?php echo $mes_rec_filtro; ?>">
                                            <input type="hidden" name="ano_recorrencia"
                                                value="<?php echo $ano_rec_filtro; ?>">

                                            <select name="mes_ranking" id="mes_ranking"
                                                class="form-control form-control-premium mr-2" style="font-size: 0.8rem;">
                                                <?php
                                                foreach ($meses as $num => $nome) {
                                                    $selected = ($num == $mes_filtro) ? 'selected' : '';
                                                    echo "<option value='$num' $selected>$nome</option>";
                                                }
                                                ?>
                                            </select>
                                            <select name="ano_ranking" id="ano_ranking"
                                                class="form-control form-control-premium mr-2" style="font-size: 0.8rem;">
                                                <?php
                                                for ($i = $ano_atual; $i >= $ano_atual - 2; $i--) {
                                                    $selected = ($i == $ano_filtro) ? 'selected' : '';
                                                    echo "<option value='$i' $selected>$i</option>";
                                                }
                                                ?>
                                            </select>
                                            <button type="submit" class="btn btn-modern btn-primary text-white mr-2"
                                                style="background: #4f46e5; border: none;"><i class="fas fa-filter mr-1"></i> <?php echo __('Filtrar'); ?></button>
                                            <a href="relatorio_ranking_sla.php?mes=<?php echo $mes_filtro; ?>&ano=<?php echo $ano_filtro; ?>"
                                                id="btn_pdf_sla" target="_blank" class="btn btn-modern btn-danger text-white">
                                                <i class="fas fa-file-pdf mr-1"></i> PDF
                                            </a>
                                        </form>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo __('Responsável'); ?></th>
                                                        <th><?php echo __('Chamados Resolvidos'); ?></th>
                                                        <th><?php echo __('Dentro do Prazo'); ?></th>
                                                        <th><?php echo __('% SLA Atingido'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($ranking_data as $rank): ?>
                                                        <tr>
                                                            <td class="align-middle">
                                                                <img class="img-profile rounded-circle avatar-premium"
                                                                    style="width: 36px; height: 36px; margin-right: 12px; object-fit: cover;"
                                                                    src="<?php echo !empty($rank['foto_perfil']) ? htmlspecialchars($rank['foto_perfil']) : '/assets/img/avatars/avatar1.jpeg'; ?>"
                                                                    alt="<?php echo htmlspecialchars($rank['nome']); ?>">
                                                                <span class="font-weight-500 text-slate-800"><?php echo htmlspecialchars($rank['nome'] . ' ' . $rank['sobrenome']); ?></span>
                                                            </td>
                                                            <td class="align-middle text-center"><span class="badge badge-light p-2" style="font-size: 0.85rem"><?php echo $rank['total']; ?></span></td>
                                                            <td class="align-middle text-center"><span class="badge badge-light p-2 text-success" style="font-size: 0.85rem"><?php echo $rank['met_sla']; ?></span></td>
                                                            <td class="align-middle">
                                                                <div class="d-flex align-items-center">
                                                                    <span class="mr-2 font-weight-bold" style="font-size: 0.85rem"><?php echo $rank['percentage']; ?>%</span>
                                                                    <div class="progress progress-premium flex-grow-1">
                                                                        <?php
                                                                        $color = 'bg-danger';
                                                                        if ($rank['percentage'] >= 80)
                                                                            $color = 'bg-success';
                                                                        elseif ($rank['percentage'] >= 50)
                                                                            $color = 'bg-warning';
                                                                        ?>
                                                                        <div class="progress-bar progress-bar-premium <?php echo $color; ?>"
                                                                            role="progressbar"
                                                                            style="width: <?php echo $rank['percentage']; ?>%"
                                                                            aria-valuenow="<?php echo $rank['percentage']; ?>"
                                                                            aria-valuemin="0" aria-valuemax="100">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    endforeach; ?>
                                                    <?php if (empty($ranking_data)): ?>
                                                        <tr>
                                                            <td colspan="4" class="text-center">
                                                                <?php echo __('Nenhum chamado finalizado neste período.'); ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- End: SLA Ranking -->
                    <?php if ($_SESSION['nivelUsuario'] == 'Admin' || $_SESSION['nivelUsuario'] == 'Suporte'): ?>
                        <div class="row">
                            <div class="col-lg-12 mb-4">
                                <div class="card premium-card mb-4">
                                    <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center" style="background: transparent; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                        <h6 class="m-0 font-weight-bold text-slate-800">
                                            <i class="fas fa-fire text-danger mr-2"></i><?php echo __('Ranking de Chamados por Recorrência'); ?>
                                        </h6>
                                        <form method="GET" action="index.php" class="form-inline mt-2 mt-md-0">
                                            <!-- Preservar filtro de SLA ao filtrar Recorrência -->
                                            <input type="hidden" name="mes_ranking" value="<?php echo $mes_filtro; ?>">
                                            <input type="hidden" name="ano_ranking" value="<?php echo $ano_filtro; ?>">

                                            <select name="mes_recorrencia" id="mes_recorrencia"
                                                class="form-control form-control-premium mr-2" style="font-size: 0.8rem;">
                                                <?php
                                                foreach ($meses as $num => $nome) {
                                                    $selected = ($num == $mes_rec_filtro) ? 'selected' : '';
                                                    echo "<option value='$num' $selected>$nome</option>";
                                                }
                                                ?>
                                            </select>
                                            <select name="ano_recorrencia" id="ano_recorrencia"
                                                class="form-control form-control-premium mr-2" style="font-size: 0.8rem;">
                                                <?php
                                                for ($i = $ano_atual; $i >= $ano_atual - 2; $i--) {
                                                    $selected = ($i == $ano_rec_filtro) ? 'selected' : '';
                                                    echo "<option value='$i' $selected>$i</option>";
                                                }
                                                ?>
                                            </select>
                                            <button type="submit" class="btn btn-modern btn-primary text-white mr-2"
                                                style="background: #4f46e5; border: none;"><i class="fas fa-filter mr-1"></i> <?php echo __('Filtrar'); ?></button>
                                            <a href="relatorio_ranking_recorrencia.php?mes=<?php echo $mes_rec_filtro; ?>&ano=<?php echo $ano_rec_filtro; ?>"
                                                id="btn_pdf_recorrencia" target="_blank" class="btn btn-modern btn-danger text-white">
                                                <i class="fas fa-file-pdf mr-1"></i> PDF
                                            </a>
                                        </form>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        if (!empty($recorrencia_data)) {
                                            $cores = ['bg-danger', 'bg-warning', 'bg-info', 'bg-primary', 'bg-success'];
                                            foreach ($recorrencia_data as $i => $rec) {
                                                $pct = ($max_recorrencia > 0) ? round(($rec['total'] / $max_recorrencia) * 100) : 0;
                                                $cor = $cores[$i % count($cores)];
                                                $titulo_chamado = htmlspecialchars(mb_strimwidth($rec['titulo'], 0, 50, '...'));
                                                ?>
                                                <div class="mb-3">
                                                    <h4 class="small font-weight-bold text-slate-800 mb-1">
                                                        <?php echo $titulo_chamado; ?>
                                                        <span class="float-right badge badge-light"><?php echo $rec['total']; ?> <?php echo __('chamados'); ?></span>
                                                    </h4>
                                                    <div class="progress progress-premium">
                                                        <div class="progress-bar progress-bar-premium <?php echo $cor; ?>" role="progressbar"
                                                            aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100"
                                                            style="width: <?php echo $pct; ?>%;">
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        } else {
                                            echo '<div class="text-center p-4">
                                                    <i class="fas fa-check-circle text-success fa-3x mb-3 opacity-50"></i>
                                                    <p class="text-muted">' . __('Nenhum chamado recorrente neste período. Ótimo trabalho!') . '</p>
                                                  </div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"
        defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.min.js" defer></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6" defer></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30" defer></script>
    <script src="/assets/js/global_search.js" defer></script>
    <script>
        function updateSlaPdfLink() {
            var mes = document.getElementById('mes_ranking').value;
            var ano = document.getElementById('ano_ranking').value;
            document.getElementById('btn_pdf_sla').href = 'relatorio_ranking_sla.php?mes=' + mes + '&ano=' + ano;
        }

        function updateRecPdfLink() {
            var mes = document.getElementById('mes_recorrencia').value;
            var ano = document.getElementById('ano_recorrencia').value;
            document.getElementById('btn_pdf_recorrencia').href = 'relatorio_ranking_recorrencia.php?mes=' + mes + '&ano=' + ano;
        }
    </script>
    <script>
        // Atualizar links de PDF dinamicamente ao mudar os selects (apenas se existirem)
        if (document.getElementById('mes_ranking')) {
            document.getElementById('mes_ranking').addEventListener('change', updateSlaPdfLink);
            document.getElementById('ano_ranking').addEventListener('change', updateSlaPdfLink);
        }
        if (document.getElementById('mes_recorrencia')) {
            document.getElementById('mes_recorrencia').addEventListener('change', updateRecPdfLink);
            document.getElementById('ano_recorrencia').addEventListener('change', updateRecPdfLink);
        }
    </script>
    <?php include 'performance_footer.php'; ?>
</body>

</html>