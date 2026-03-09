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

// === PROCESSAMENTO DE SLA: Salva ou atualiza os tempos de resposta por categoria ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sla'])) {
    foreach ($_POST['sla'] as $category => $time) {
        $category = mysqli_real_escape_string($conn, $category);
        $hours = (int) $time['hours'];
        $minutes = (int) $time['minutes'];

        $total_minutes = ($hours * 60) + $minutes;

        $sql = "INSERT INTO configuracoes_sla (categoria, tempo_sla_minutos) VALUES ('$category', $total_minutes) 
                ON DUPLICATE KEY UPDATE tempo_sla_minutos = $total_minutes";
        mysqli_query($conn, $sql);
    }
    header("Location: configuracoes.php?msg=sla_success");
    exit();
}

// === PROCESSAMENTO DE DEPRECIAÇÃO: Configura taxas financeiras e regras de doação de ativos ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['depreciacao'])) {
    $taxa = floatval($_POST['depreciacao']['taxa']);
    $periodo_anos = (int) $_POST['depreciacao']['periodo_anos'];
    $periodo_meses = (int) $_POST['depreciacao']['periodo_meses'];
    $elegivel = isset($_POST['depreciacao']['elegivel_doacao']) ? 1 : 0;
    $doacao_anos = (int) $_POST['depreciacao']['tempo_doacao_anos'];
    $doacao_meses = (int) $_POST['depreciacao']['tempo_doacao_meses'];

    // Check if record exists
    $check = mysqli_query($conn, "SELECT id FROM configuracoes_depreciacao LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        $row_dep = mysqli_fetch_assoc($check);
        $sql_dep = "UPDATE configuracoes_depreciacao SET 
            taxa_depreciacao = $taxa, 
            periodo_anos = $periodo_anos, 
            periodo_meses = $periodo_meses, 
            elegivel_doacao = $elegivel, 
            tempo_doacao_anos = $doacao_anos, 
            tempo_doacao_meses = $doacao_meses 
            WHERE id = " . $row_dep['id'];
    } else {
        $sql_dep = "INSERT INTO configuracoes_depreciacao (taxa_depreciacao, periodo_anos, periodo_meses, elegivel_doacao, tempo_doacao_anos, tempo_doacao_meses) 
            VALUES ($taxa, $periodo_anos, $periodo_meses, $elegivel, $doacao_anos, $doacao_meses)";
    }
    mysqli_query($conn, $sql_dep);
    // Salvar elegibilidade por categoria
    $result_cats = mysqli_query($conn, "SELECT categoria FROM categoria");
    while ($cat_row = mysqli_fetch_assoc($result_cats)) {
        $cat_name = mysqli_real_escape_string($conn, $cat_row['categoria']);
        $cat_elegivel = isset($_POST['cat_doacao'][$cat_name]) ? 1 : 0;
        mysqli_query($conn, "INSERT INTO categoria_doacao (categoria, elegivel_doacao) VALUES ('$cat_name', $cat_elegivel) ON DUPLICATE KEY UPDATE elegivel_doacao = $cat_elegivel");
    }

    header("Location: configuracoes.php?msg=dep_success");
    exit();
}

// === PROCESSAMENTO DE CANAIS DE ALERTA: Ativa/Desativa notificações por e-mail e WhatsApp ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['alertas'])) {
    $wa_ativo = isset($_POST['alertas']['whatsapp']) ? 1 : 0;
    $email_ativo = isset($_POST['alertas']['email']) ? 1 : 0;
    $ia_ativo = isset($_POST['alertas']['ia_agente']) ? 1 : 0;
    mysqli_query($conn, "UPDATE configuracoes_alertas SET whatsapp_ativo = $wa_ativo, email_ativo = $email_ativo, ia_agente_ativo = $ia_ativo WHERE id = 1");
    header("Location: configuracoes.php?msg=success");
    exit();
}

// === PROCESSAMENTO DE IA: Ativa/Desativa o Agente de IA ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ia_config'])) {
    $ia_ativo = isset($_POST['ia_agente_ativo']) ? 1 : 0;
    mysqli_query($conn, "UPDATE configuracoes_alertas SET ia_agente_ativo = $ia_ativo WHERE id = 1");
    header("Location: configuracoes.php?msg=ia_success");
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
    mysqli_query($conn, $sql);
    header("Location: configuracoes.php?msg=session_success");
    exit();
}


// === COLETA DE DADOS ATUAIS: Busca as configurações salvas para preencher o formulário ===
$configs = [];
// Check column name first to be safe or assume rename worked. 
// We know we ran: ALTER TABLE configuracoes_sla CHANGE tempo_sla_horas tempo_sla_minutos INT...
$result = mysqli_query($conn, "SELECT * FROM configuracoes_sla");
while ($row = mysqli_fetch_assoc($result)) {
    $configs[$row['categoria']] = $row['tempo_sla_minutos'];
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
    'periodo_anos' => 1,
    'periodo_meses' => 0,
    'elegivel_doacao' => 0,
    'tempo_doacao_anos' => 5,
    'tempo_doacao_meses' => 0
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

// Fetch per-category donation eligibility
$cat_doacao = [];
$result_cat_doacao = mysqli_query($conn, "SELECT c.categoria, COALESCE(cd.elegivel_doacao, 1) as elegivel_doacao FROM categoria c LEFT JOIN categoria_doacao cd ON c.categoria = cd.categoria ORDER BY c.categoria ASC");
if ($result_cat_doacao) {
    while ($row_cd = mysqli_fetch_assoc($result_cat_doacao)) {
        $cat_doacao[$row_cd['categoria']] = $row_cd['elegivel_doacao'];
    }
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
    <title>Configurações - Asset MGT</title>
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

        .pointer {
            cursor: pointer;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion p-0">
            <div class="container-fluid d-flex flex-column p-0">
                <a class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#">
                    <div class="sidebar-brand-text mx-3"><span>Asset Mgt</span></div>
                </a>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top"
                    style="margin: 5px 23px;">
                    <div class="container-fluid"><button class="btn btn-link d-md-none rounded-circle mr-3"
                            id="sidebarToggleTop-1" type="button"><i class="fas fa-bars"></i></button>
                        <form
                            class="form-inline d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search position-relative">
                            <div class="input-group">
                                <input class="bg-light form-control border-0 small" type="text"
                                    placeholder="Pesquisar..." id="globalSearchInput" autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button"
                                        style="background: rgb(44,64,74); border: none;">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="globalSearchResults" class="dropdown-menu shadow animated--grow-in"
                                style="width: 100%; display: none;"></div>
                        </form>
                        <ul class="navbar-nav flex-nowrap ml-auto">
                            <li class="nav-item no-arrow mx-1 d-flex align-items-center">
                            </li>
                            <div class="d-none d-sm-block topbar-divider"></div>
                            <li class="nav-item dropdown no-arrow">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link"
                                        aria-expanded="false" data-toggle="dropdown" href="#"><span
                                            class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span><img
                                            class="border rounded-circle img-profile"
                                            src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/Captura%20de%20Tela%202021-08-04%20às%2012.25.13.png?h=fcfb924f0ac1ab5f595f029bf526e62d'; ?>"></a>
                                    <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in">
                                        <a class="dropdown-item" href="profile.php"><i
                                                class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a>
                                        <a class="dropdown-item" href="configuracoes.php"><i
                                                class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a>
                                        <a class="dropdown-item" href="equipamentos.php?status=Manutencao"><i
                                                class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>Ativos em
                                            Manutenção</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="login.php"><i
                                                class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="container-fluid">
                    <h3 class="text-dark mb-4">Configurações do Sistema</h3>

                    <?php
                    $msg_text = "";
                    if (isset($_GET['msg'])) {
                        switch ($_GET['msg']) {
                            case 'success':
                                $msg_text = "Configurações de canais de alerta atualizadas com sucesso!";
                                break;
                            case 'sla_success':
                                $msg_text = "Configurações de SLA atualizadas com sucesso!";
                                break;
                            case 'dep_success':
                                $msg_text = "Configurações de depreciação atualizadas com sucesso!";
                                break;
                            case 'session_success':
                                $msg_text = "Configurações de sessão atualizadas com sucesso!";
                                break;
                        }
                    }
                    if ($msg_text): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $msg_text; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow">
                        <div class="card-header py-3">
                            <p class="text-primary m-0 font-weight-bold">Configuração de SLA (Service Level Agreement)
                            </p>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="configuracoes.php">
                                <p class="mb-4">Defina o tempo máximo de resolução (Horas e Minutos) para cada categoria
                                    de chamado.</p>

                                <?php
                                $categories = ['Incidente', 'Mudança', 'Requisição'];
                                foreach ($categories as $cat) {
                                    $time = getHoursAndMinutes($configs[$cat]);
                                    ?>
                                    <div class="form-group row align-items-center mb-4 sla-row"
                                        data-category="<?php echo $cat; ?>">
                                        <label for="sla_<?php echo $cat; ?>"
                                            class="col-sm-2 col-form-label font-weight-bold"><?php echo $cat; ?></label>
                                        <div class="col-sm-3">
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control sla-hours"
                                                    name="sla[<?php echo $cat; ?>][hours]" value="<?php echo $time['h']; ?>"
                                                    required min="0" placeholder="0">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">Horas</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control sla-minutes"
                                                    name="sla[<?php echo $cat; ?>][minutes]"
                                                    value="<?php echo $time['m']; ?>" required min="0" max="59"
                                                    placeholder="0">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">Minutos</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 d-flex justify-content-between align-items-center pl-4 py-2"
                                            style="background: #f8f9fc; border-radius: 12px; border: 1px solid #e3e6f0;">
                                            <div class="text-center" style="flex: 1;">
                                                <span class="badge badge-danger mb-2 px-3 py-1"
                                                    style="border-radius: 50px; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.5px;">Alta</span>
                                                <div
                                                    style="height: 6px; background: #dc3545; margin: 0 5px 8px 5px; border-radius: 10px; opacity: 0.8;">
                                                </div>
                                                <strong class="text-dark d-block label-50"
                                                    style="font-size: 0.85rem;">--</strong>
                                            </div>
                                            <div class="text-center mx-1" style="flex: 1;">
                                                <span class="badge badge-warning mb-2 px-3 py-1"
                                                    style="border-radius: 50px; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.5px; color: #fff; background-color: #f6c23e;">Média</span>
                                                <div
                                                    style="height: 6px; background: #f6c23e; margin: 0 5px 8px 5px; border-radius: 10px; opacity: 0.8;">
                                                </div>
                                                <strong class="text-dark d-block label-75"
                                                    style="font-size: 0.85rem;">--</strong>
                                            </div>
                                            <div class="text-center" style="flex: 1;">
                                                <span class="badge badge-success mb-2 px-3 py-1"
                                                    style="border-radius: 50px; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.5px;">Baixa</span>
                                                <div
                                                    style="height: 6px; background: #1cc88a; margin: 0 5px 8px 5px; border-radius: 10px; opacity: 0.8;">
                                                </div>
                                                <strong class="text-dark d-block label-100"
                                                    style="font-size: 0.85rem;">--</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                } ?>

                                <div class="form-group row mt-4">
                                    <div class="col-sm-12 text-right">
                                        <button type="submit" class="btn btn-primary"
                                            style="background: rgb(44,64,74);">Salvar Alterações</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Seção de Depreciação e Doação -->
                    <div class="card shadow mt-4">
                        <div class="card-header py-3">
                            <p class="text-primary m-0 font-weight-bold">Configuração de Depreciação e Doação de Ativos
                            </p>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="configuracoes.php">
                                <p class="mb-4">Defina a taxa de depreciação dos ativos e as regras de elegibilidade
                                    para doação.</p>

                                <!-- Taxa de Depreciação -->
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label font-weight-bold">Taxa de Depreciação
                                        (%)</label>
                                    <div class="col-sm-3">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="depreciacao[taxa]"
                                                value="<?php echo $dep_config['taxa_depreciacao']; ?>" required min="0"
                                                max="100" step="0.01" placeholder="10.00">
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Período de Depreciação -->
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label font-weight-bold">Período de
                                        Depreciação</label>
                                    <div class="col-sm-2">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="depreciacao[periodo_anos]"
                                                value="<?php echo $dep_config['periodo_anos']; ?>" required min="0"
                                                placeholder="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text">Anos</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="depreciacao[periodo_meses]"
                                                value="<?php echo $dep_config['periodo_meses']; ?>" required min="0"
                                                max="11" placeholder="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text">Meses</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Elegível para Doação (Global) -->
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label font-weight-bold">Elegível para
                                        Doação?</label>
                                    <div class="col-sm-4">
                                        <div class="custom-control custom-switch" style="margin-top: 7px;">
                                            <input type="checkbox" class="custom-control-input" id="elegivelDoacao"
                                                name="depreciacao[elegivel_doacao]" value="1" <?php echo ($dep_config['elegivel_doacao'] == 1) ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="elegivelDoacao">
                                                <?php echo ($dep_config['elegivel_doacao'] == 1) ? 'Sim, ativos podem ser doados' : 'Não, doação desativada'; ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Elegibilidade por Categoria -->
                                <div id="categoriasDoacaoSection">
                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label font-weight-bold">Elegibilidade por
                                            Categoria</label>
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
                                    <label class="col-sm-3 col-form-label font-weight-bold">Tempo mínimo para
                                        Doação</label>
                                    <div class="col-sm-2">
                                        <div class="input-group">
                                            <input type="number" class="form-control"
                                                name="depreciacao[tempo_doacao_anos]"
                                                value="<?php echo $dep_config['tempo_doacao_anos']; ?>" min="0"
                                                placeholder="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text">Anos</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="input-group">
                                            <input type="number" class="form-control"
                                                name="depreciacao[tempo_doacao_meses]"
                                                value="<?php echo $dep_config['tempo_doacao_meses']; ?>" min="0"
                                                max="11" placeholder="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text">Meses</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row mt-4">
                                    <div class="col-sm-12 text-right">
                                        <button type="submit" class="btn btn-primary"
                                            style="background: rgb(44,64,74);">Salvar Configurações de
                                            Depreciação</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- Seção de Canais de Alerta -->
                    <div class="card shadow mt-4 mb-4">
                        <div class="card-header py-3">
                            <p class="text-primary m-0 font-weight-bold">Canais de Notificação e Alerta</p>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="configuracoes.php">
                                <p class="mb-4">Escolha por quais canais você deseja receber os alertas do sistema
                                    (Novos chamados e Manutenções).</p>

                                <input type="hidden" name="alertas" value="1">


                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <div class="card shadow-sm border-left-info h-100">
                                            <div
                                                class="card-body py-3 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0 font-weight-bold"><i
                                                            class="fas fa-ticket-alt mr-2"></i> Novos Chamados</h6>
                                                    <small class="text-muted">Status global para abertura de
                                                        tickets</small>
                                                </div>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input event-toggle"
                                                        id="toggleChamados" data-event="chamados" <?php echo ($alert_config['chamados_ativo'] ?? 1) ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="toggleChamados"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card shadow-sm border-left-warning h-100">
                                            <div
                                                class="card-body py-3 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-tools mr-2"></i>
                                                        Manutenções</h6>
                                                    <small class="text-muted">Status global para ativos em
                                                        oficina</small>
                                                </div>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input event-toggle"
                                                        id="toggleManutencao" data-event="manutencao" <?php echo ($alert_config['manutencao_ativo'] ?? 1) ? 'checked' : ''; ?>>
                                                    <label class="custom-control-label" for="toggleManutencao"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SEÇÃO 2: COMO NOTIFICAR (CANAIS) -->
                                <div class="row">
                                    <!-- CANAL WHATSAPP -->
                                    <div class="col-xl-6 mb-4">
                                        <div class="card shadow-sm border-0 h-100" style="background: #f8fff9;">
                                            <div class="card-header bg-success text-white border-0 py-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="m-0 font-weight-bold"><i
                                                            class="fab fa-whatsapp fa-lg mr-2"></i> Canal: WhatsApp</h6>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="alertWhatsApp" name="alertas[whatsapp]" value="1" <?php echo (($alert_config['whatsapp_ativo'] ?? 0) == 1) ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" for="alertWhatsApp"></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="small font-weight-bold text-success text-uppercase">O
                                                        que notificar? (Global)</label>
                                                    <div class="d-flex align-items-center mt-1">
                                                        <i class="fas fa-ticket-alt fa-2x pointer global-wa-event mr-4 <?php echo ($alert_config['whatsapp_recebe_chamados'] ?? 1) ? 'text-primary' : 'text-gray-300'; ?>"
                                                            data-event="chamados" title="Chamados"></i>
                                                        <i class="fas fa-tools fa-2x pointer global-wa-event <?php echo ($alert_config['whatsapp_recebe_manutencao'] ?? 1) ? 'text-warning' : 'text-gray-300'; ?>"
                                                            data-event="manutencao" title="Manutenção"></i>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label
                                                        class="small font-weight-bold text-success text-uppercase">Prioridades
                                                        permitidas (Global)</label>
                                                    <div class="priority-badges-global mt-1">
                                                        <span
                                                            class="badge badge-priority pointer global-wa-priority <?php echo ($alert_config['whatsapp_prioridade_alta'] ?? 1) ? 'badge-danger active' : 'badge-inactive'; ?>"
                                                            data-priority="alta"
                                                            title="Alternar Prioridade Alta">Alta</span>
                                                        <span
                                                            class="badge badge-priority pointer global-wa-priority <?php echo ($alert_config['whatsapp_prioridade_media'] ?? 1) ? 'badge-warning text-white active' : 'badge-inactive'; ?>"
                                                            data-priority="media"
                                                            title="Alternar Prioridade Média">Média</span>
                                                        <span
                                                            class="badge badge-priority pointer global-wa-priority <?php echo ($alert_config['whatsapp_prioridade_baixa'] ?? 1) ? 'badge-success active' : 'badge-inactive'; ?>"
                                                            data-priority="baixa"
                                                            title="Alternar Prioridade Baixa">Baixa</span>
                                                    </div>
                                                </div>

                                                <div class="mb-0">
                                                    <label
                                                        class="small font-weight-bold text-success text-uppercase">Categorias
                                                        permitidas (Global)</label>
                                                    <div class="category-badges-global mt-1">
                                                        <span
                                                            class="badge badge-priority pointer global-cat-toggle <?php echo ($alert_config['cat_incidente'] ?? 1) ? 'badge-info active' : 'badge-inactive'; ?>"
                                                            data-category="incidente"
                                                            title="Alternar Incidente">Incidente</span>
                                                        <span
                                                            class="badge badge-priority pointer global-cat-toggle <?php echo ($alert_config['cat_mudanca'] ?? 1) ? 'badge-primary active' : 'badge-inactive'; ?>"
                                                            data-category="mudanca"
                                                            title="Alternar Mudança">Mudança</span>
                                                        <span
                                                            class="badge badge-priority pointer global-cat-toggle <?php echo ($alert_config['cat_requisicao'] ?? 1) ? 'badge-secondary active' : 'badge-inactive'; ?>"
                                                            data-category="requisicao"
                                                            title="Alternar Requisição">Requisição</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- CANAL E-MAIL -->
                                    <div class="col-xl-6 mb-4">
                                        <div class="card shadow-sm border-0 h-100" style="background: #fdfdff;">
                                            <div class="card-header bg-primary text-white border-0 py-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="m-0 font-weight-bold"><i
                                                            class="fas fa-envelope fa-lg mr-2"></i> Canal: E-mail</h6>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="alertEmail" name="alertas[email]" value="1" <?php echo (($alert_config['email_ativo'] ?? 0) == 1) ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" for="alertEmail"></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body px-3 py-3">
                                                <div class="input-group input-group-sm mb-3 shadow-sm">
                                                    <input type="text" id="userSearch" class="form-control border-0"
                                                        placeholder="Pesquisar destinatário por nome ou e-mail...">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-white border-0"><i
                                                                class="fas fa-search text-gray-400"></i></span>
                                                    </div>
                                                </div>
                                                <div id="searchResults" class="dropdown-menu shadow animated--grow-in"
                                                    style="display:none; position:absolute; z-index:1000; width:92%; max-height:200px; overflow-y:auto;">
                                                </div>

                                                <label
                                                    class="small font-weight-bold text-primary text-uppercase mb-2">Destinatários
                                                    Ativos</label>
                                                <div id="destinatariosList" class="row no-gutters overflow-auto"
                                                    style="max-height: 130px;">
                                                    <?php
                                                    $res_dest = $conn->query("SELECT d.*, u.nome, u.sobrenome, u.email 
                                                                            FROM destinatarios_alertas d 
                                                                            JOIN usuarios u ON d.usuario_id = u.id_usuarios");
                                                    if ($res_dest->num_rows == 0):
                                                        echo '<div class="col-12 text-muted small text-center italic py-2">Nenhum e-mail cadastrado.</div>';
                                                    endif;
                                                    while ($dest = $res_dest->fetch_assoc()):
                                                        ?>
                                                        <div class="col-12 mb-2 recipient-card"
                                                            data-id="<?php echo $dest['id']; ?>">
                                                            <div class="card border-left-primary shadow-sm py-1">
                                                                <div
                                                                    class="card-body py-1 px-2 d-flex justify-content-between align-items-center">
                                                                    <div style="flex: 1; min-width: 0;">
                                                                        <div
                                                                            class="text-xs font-weight-bold text-primary text-truncate">
                                                                            <?php echo htmlspecialchars($dest['nome'] . ' ' . $dest['sobrenome']); ?>
                                                                        </div>
                                                                        <div
                                                                            class="priority-badges d-flex align-items-center mt-1">
                                                                            <span
                                                                                class="badge user-priority-badge pointer <?php echo $dest['prioridade_alta'] ? 'badge-danger active' : 'badge-inactive'; ?>"
                                                                                data-priority="alta">A</span>
                                                                            <span
                                                                                class="badge user-priority-badge pointer mx-1 <?php echo $dest['prioridade_media'] ? 'badge-warning text-white active' : 'badge-inactive'; ?>"
                                                                                data-priority="media">M</span>
                                                                            <span
                                                                                class="badge user-priority-badge pointer <?php echo $dest['prioridade_baixa'] ? 'badge-success active' : 'badge-inactive'; ?>"
                                                                                data-priority="baixa">B</span>

                                                                            <div
                                                                                class="ml-2 pl-2 border-left d-flex align-items-center user-cat-toggles">
                                                                                <span
                                                                                    class="user-cat-toggle pointer mr-1 font-weight-bold <?php echo ($dest['cat_incidente'] ?? 1) ? 'text-info' : 'text-gray-300'; ?>"
                                                                                    data-cat="incidente"
                                                                                    title="Incidente">I</span>
                                                                                <span
                                                                                    class="user-cat-toggle pointer mr-1 font-weight-bold <?php echo ($dest['cat_mudanca'] ?? 1) ? 'text-primary' : 'text-gray-300'; ?>"
                                                                                    data-cat="mudanca"
                                                                                    title="Mudança">M</span>
                                                                                <span
                                                                                    class="user-cat-toggle pointer font-weight-bold <?php echo ($dest['cat_requisicao'] ?? 1) ? 'text-secondary' : 'text-gray-300'; ?>"
                                                                                    data-cat="requisicao"
                                                                                    title="Requisição">R</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex align-items-center">
                                                                        <i class="fas fa-ticket-alt pointer user-event-toggle mr-2 <?php echo ($dest['recebe_chamados'] ?? 1) ? 'text-primary' : 'text-gray-300'; ?>"
                                                                            data-event="chamados" title="Chamados"></i>
                                                                        <i class="fas fa-tools pointer user-event-toggle mr-2 <?php echo ($dest['recebe_manutencao'] ?? 1) ? 'text-warning' : 'text-gray-300'; ?>"
                                                                            data-event="manutencao" title="Manutenção"></i>
                                                                        <button type="button"
                                                                            class="btn btn-link text-danger remove-recipient p-0"
                                                                            data-id="<?php echo $dest['id']; ?>">
                                                                            <i class="fas fa-times-circle"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endwhile; ?>
                                                </div> <!-- End of destinatariosList -->
                                            </div> <!-- End of Email card-body -->
                                        </div> <!-- End of Email card shadow-sm -->
                                    </div> <!-- End of Email col-xl-6 -->
                                </div> <!-- End of WhatsApp/Email row -->

                                <div class="form-group row mt-4">
                                    <div class="col-sm-12 text-right">
                                        <button type="submit" class="btn btn-primary"
                                            style="background: rgb(44,64,74);">Salvar Canais de Alerta</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- SESSÃO E SEGURANÇA -->
                    <div class="card shadow mt-4 mb-4">
                        <div class="card-header py-3">
                            <p class="text-primary m-0 font-weight-bold">Sessão e Segurança</p>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="session_config" value="1">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label font-weight-bold">Tempo Geral
                                            (Padrão)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="idle_timeout"
                                                value="<?php echo $alert_config['idle_timeout_minutos'] ?? 10; ?>"
                                                min="1">
                                            <div class="input-group-append">
                                                <span class="input-group-text">minutos</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">Logout para usuários
                                            comuns.</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label font-weight-bold text-primary">Tempo
                                            para Administradores</label>
                                        <div class="input-group border-left-primary">
                                            <input type="number" class="form-control" name="idle_timeout_admin"
                                                value="<?php echo $alert_config['idle_timeout_admin'] ?? 10; ?>"
                                                min="1">
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-primary text-white">minutos</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">Logout para nível Admin.</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label font-weight-bold text-info">Tempo
                                            para Suporte</label>
                                        <div class="input-group border-left-info">
                                            <input type="number" class="form-control" name="idle_timeout_suporte"
                                                value="<?php echo $alert_config['idle_timeout_suporte'] ?? 10; ?>"
                                                min="1">
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-info text-white">minutos</span>
                                            </div>
                                        </div>
                                        <small class="text-muted">Logout para nível Suporte.</small>
                                    </div>
                                </div>
                                <div class="text-right mt-3">
                                    <button type="submit" class="btn btn-primary"
                                        style="background: rgb(44,64,74);">Salvar Configurações de Sessão</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- CONFIGURAÇÕES DE IA (STANDALONE) -->
                    <div class="card shadow mt-4 mb-4" style="border-left: 0.25rem solid #4e73df!important;">
                        <div class="card-header py-3 bg-white">
                            <h6 class="text-primary m-0 font-weight-bold">
                                <i class="fas fa-robot mr-2"></i> Inteligência Artificial (IA)
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="ia_config" value="1">
                                <div class="row align-items-center">
                                    <div class="col-md-9 border-right">
                                        <p class="mb-0 text-muted">Habilitar ou desabilitar o Agente de IA em todo o
                                            sistema. Quando desativado, o chat e outros recursos de IA ficarão
                                            indisponíveis para todos os usuários.</p>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="iaAgenteAtivoBottom"
                                                name="ia_agente_ativo" value="1" <?php echo ($alert_config['ia_agente_ativo'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="custom-control-label font-weight-bold"
                                                for="iaAgenteAtivoBottom" style="cursor: pointer; font-size: 1.1rem;">
                                                <?php echo ($alert_config['ia_agente_ativo'] ?? 1) ? 'Ativo' : 'Inativo'; ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary" style="background: rgb(44,64,74);">
                                        <i class="fas fa-save mr-2"></i> Salvar Configuração de IA
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> <!-- End of tab content / main container -->
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
        $(document).ready(function () {
            var $switch = $('#elegivelDoacao');
            var $doacaoRow = $('#tempoDoacaoRow');
            var $catSection = $('#categoriasDoacaoSection');
            var $label = $switch.next('label');

            function toggleDoacao() {
                if ($switch.is(':checked')) {
                    $catSection.slideDown(200);
                    $doacaoRow.slideDown(200);
                    $label.text('Sim, ativos podem ser doados');
                } else {
                    $catSection.slideUp(200);
                    $doacaoRow.slideUp(200);
                    $label.text('Não, doação desativada');
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

                    $row.find('.label-50').text(formatTimeDisplay(Math.round(total / 3)));
                    $row.find('.label-75').text(formatTimeDisplay(Math.round(total * 2 / 3)));
                    $row.find('.label-100').text(formatTimeDisplay(total));
                });
            }

            // Alertas Channel Toggles
            $('#alertEmail').on('change', function () {      // Not using disabled anymore, but keeping for switch logic if needed
            });

            // User Search for Alerts
            $('#userSearch').on('keyup', function () {
                let term = $(this).val();
                if (term.length > 2) {
                    $.post('ajax_alertas.php', { action: 'search', term: term }, function (data) {
                        let users = JSON.parse(data);
                        let html = '';
                        users.forEach(u => {
                            html += `<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>${u.nome} ${u.sobrenome}</strong><br>
                                            <span class="small text-muted">${u.email}</span>
                                        </div>
                                        <button class="btn btn-success btn-sm add-user" data-id="${u.id}">
                                            <i class="fas fa-plus mr-1"></i> Adicionar
                                        </button>
                                     </div>`;
                        });
                        if (html) {
                            $('#searchResults').html(html).show();
                        } else {
                            $('#searchResults').hide();
                        }
                    });
                } else {
                    $('#searchResults').hide();
                }
            });

            $(document).on('click', '.add-user', function (e) {
                e.preventDefault();
                let uid = $(this).data('id');
                $.post('ajax_alertas.php', { action: 'add', usuario_id: uid }, function (data) {
                    let res = JSON.parse(data);
                    if (res.status === 'success') {
                        // Dynamically add the new recipient card
                        let newRecipient = res.recipient; // Assuming res.recipient contains the new recipient data
                        let newCardHtml = `
                            <div class="col-md-6 mb-2 recipient-card" data-id="${res.recipient.id}">
                                <div class="card bg-light border-left-primary shadow-sm h-100 py-1">
                                    <div class="card-body py-1 d-flex justify-content-between align-items-center">
                                        <div style="flex: 1;">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-0">${res.recipient.nome} ${res.recipient.sobrenome || ''}</div>
                                            <div class="text-muted small mb-1">${res.recipient.email}</div>
                                            <div class="priority-badges d-flex align-items-center">
                                                <span class="badge badge-priority active badge-danger pointer" data-priority="alta" title="Ativo">Alta</span>
                                                <span class="badge badge-priority active badge-warning text-white pointer" data-priority="media" title="Ativo">Média</span>
                                                <span class="badge badge-priority active badge-success pointer" data-priority="baixa" title="Ativo">Baixa</span>
                                                
                                                <div class="ml-2 pl-2 border-left d-flex align-items-center event-toggles-user">
                                                    <i class="fas fa-ticket-alt pointer user-event-toggle mr-2 text-primary" 
                                                       data-event="chamados" title="Alertas de Chamados: Ativo"></i>
                                                    <i class="fas fa-tools pointer user-event-toggle mr-2 text-warning" 
                                                       data-event="manutencao" title="Alertas de Manutenção: Ativo"></i>
                                                    <div class="user-cat-toggles border-left pl-2 d-flex">
                                                        <span class="user-cat-toggle pointer mr-1 font-weight-bold text-info" data-cat="incidente" title="Filtro: Incidente">I</span>
                                                        <span class="user-cat-toggle pointer mr-1 font-weight-bold text-primary" data-cat="mudanca" title="Filtro: Mudança">M</span>
                                                        <span class="user-cat-toggle pointer font-weight-bold text-secondary" data-cat="requisicao" title="Filtro: Requisição">R</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="btn btn-link text-danger p-0 ml-2 btn-remove-dest" data-id="${res.recipient.id}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>`;
                        $('#destinatariosList').append(newCardHtml);
                        $('#searchResults').hide();
                        $('#userSearch').val('');
                        // If there was a "Nenhum destinatário cadastrado" message, remove it
                        $('#destinatariosList').find('.col-12.text-muted.small').remove();
                    } else {
                        alert(res.message);
                    }
                });
            });

            $(document).on('click', '.remove-recipient', function () {
                let id = $(this).data('id');
                if (confirm('Remover este destinatário?')) {
                    $.post('ajax_alertas.php', { action: 'remove', id: id }, function (data) {
                        let res = JSON.parse(data);
                        if (res.status === 'success') {
                            $(`.recipient-card[data-id="${id}"]`).remove();
                            if ($('#destinatariosList').children('.recipient-card').length === 0) {
                                $('#destinatariosList').html('<div class="col-12 text-muted small">Nenhum destinatário cadastrado.</div>');
                            }
                        } else {
                            alert(res.message);
                        }
                    });
                }
            });

            $(document).on('click', '.user-priority-badge', function () {
                let $badge = $(this);
                let $card = $badge.closest('.recipient-card');
                let id = $card.data('id');
                let priority = $badge.data('priority');
                let isActive = $badge.hasClass('active');
                let newValue = isActive ? 0 : 1;

                $.post('ajax_alertas.php', { action: 'update_priority', id: id, priority: priority, value: newValue }, function (data) {
                    let res = JSON.parse(data);
                    if (res.status === 'success') {
                        if (newValue) {
                            $badge.removeClass('badge-inactive').addClass('active').attr('title', 'Ativo');
                            if (priority == 'alta') $badge.addClass('badge-danger');
                            else if (priority == 'media') $badge.addClass('badge-warning text-white');
                            else $badge.addClass('badge-success');
                        } else {
                            $badge.addClass('badge-inactive').removeClass('active badge-danger badge-warning text-white badge-success').attr('title', 'Inativo');
                        }
                    } else {
                        alert(res.message);
                    }
                });
            });

            $(document).on('click', '.global-wa-priority', function () {
                let $badge = $(this);
                let priority = $badge.data('priority');
                let isActive = $badge.hasClass('active');
                let newValue = isActive ? 0 : 1;

                $.post('ajax_alertas.php', { action: 'update_global_priority', priority: priority, value: newValue }, function (data) {
                    let res = JSON.parse(data);
                    if (res.status === 'success') {
                        if (newValue) {
                            $badge.removeClass('badge-inactive').addClass('active').attr('title', 'Ativo');
                            if (priority == 'alta') $badge.addClass('badge-danger');
                            else if (priority == 'media') $badge.addClass('badge-warning text-white');
                            else $badge.addClass('badge-success');
                        } else {
                            $badge.addClass('badge-inactive').removeClass('active badge-danger badge-warning text-white badge-success').attr('title', 'Inativo');
                        }
                    } else {
                        alert(res.message);
                    }
                });
            });

            $(document).on('click', '.global-cat-toggle', function () {
                let $badge = $(this);
                let category = $badge.data('category');
                let isActive = $badge.hasClass('active');
                let newValue = isActive ? 0 : 1;

                $.post('ajax_alertas.php', { action: 'update_global_category', category: category, value: newValue }, function (data) {
                    let res = JSON.parse(data);
                    if (res.status === 'success') {
                        if (newValue) {
                            $badge.addClass('active');
                            if (category == 'incidente') $badge.removeClass('badge-inactive').addClass('badge-info');
                            else if (category == 'mudanca') $badge.removeClass('badge-inactive').addClass('badge-primary');
                            else $badge.removeClass('badge-inactive').addClass('badge-secondary');
                            $badge.attr('title', 'Ativo');
                        } else {
                            $badge.removeClass('active badge-info badge-primary badge-secondary').addClass('badge-inactive');
                            $badge.attr('title', 'Inativo');
                        }
                    } else {
                        alert(res.message);
                    }
                });
            });

            $(document).on('click', '.user-cat-toggle', function () {
                let $span = $(this);
                let $card = $span.closest('.recipient-card');
                let id = $card.data('id');
                let category = $span.data('cat');
                let isActive = !$span.hasClass('text-gray-400');
                let newValue = isActive ? 0 : 1;

                $.post('ajax_alertas.php', { action: 'update_user_category', id: id, category: category, value: newValue }, function (data) {
                    let res = JSON.parse(data);
                    if (res.status === 'success') {
                        if (newValue) {
                            $span.removeClass('text-gray-400');
                            if (category == 'incidente') $span.addClass('text-info');
                            else if (category == 'mudanca') $span.addClass('text-primary');
                            else $span.addClass('text-secondary');
                        } else {
                            $span.addClass('text-gray-400').removeClass('text-info text-primary text-secondary');
                        }
                    } else {
                        alert(res.message);
                    }
                });
            });

            $(document).on('click', '.user-event-toggle', function () {
                let $icon = $(this);
                let $card = $icon.closest('.recipient-card');
                let id = $card.data('id');
                let event = $icon.data('event');
                let isActive = !$icon.hasClass('text-gray-400');
                let newValue = isActive ? 0 : 1;

                $.post('ajax_alertas.php', { action: 'update_user_event', id: id, event: event, value: newValue }, function (data) {
                    let res = JSON.parse(data);
                    if (res.status === 'success') {
                        if (newValue) {
                            $icon.removeClass('text-gray-400');
                            if (event == 'chamados') $icon.addClass('text-primary');
                            else $icon.addClass('text-warning');
                            $icon.attr('title', 'Alertas de ' + (event == 'chamados' ? 'Chamados' : 'Manutenção') + ': Ativo');
                        } else {
                            $icon.addClass('text-gray-400').removeClass('text-primary text-warning');
                            $icon.attr('title', 'Alertas de ' + (event == 'chamados' ? 'Chamados' : 'Manutenção') + ': Inativo');
                        }
                    } else {
                        alert(res.message);
                    }
                });
            });

            $(document).on('click', '.global-wa-event', function () {
                let $icon = $(this);
                let event = $icon.data('event');
                let isActive = !$icon.hasClass('text-gray-300');
                let newValue = isActive ? 0 : 1;

                $.post('ajax_alertas.php', { action: 'update_wa_event', event: event, value: newValue }, function (data) {
                    let res = JSON.parse(data);
                    if (res.status === 'success') {
                        if (newValue) {
                            $icon.removeClass('text-gray-300');
                            if (event == 'chamados') $icon.addClass('text-primary');
                            else $icon.addClass('text-warning');
                        } else {
                            $icon.addClass('text-gray-300').removeClass('text-primary text-warning');
                        }
                    } else {
                        alert(res.message);
                    }
                });
            });

            $(document).on('change', '.event-toggle', function () {
                let $switch = $(this);
                let event = $switch.data('event');
                let value = $switch.is(':checked') ? 1 : 0;

                $.post('ajax_alertas.php', { action: 'toggle_event', event: event, value: value }, function (data) {
                    let res = JSON.parse(data);
                    if (res.status !== 'success') {
                        alert(res.message);
                        $switch.prop('checked', !value);
                    }
                });
            });

            $('.sla-hours, .sla-minutes').on('input', updateSLABars);
            updateSLABars(); // Initial call     });
    </script>
    </div>

</body>


</html>