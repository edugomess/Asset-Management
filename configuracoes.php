<?php
include 'auth.php';
include 'conexao.php';

// Process SLA form submission
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
    $message = "Configurações de SLA atualizadas com sucesso!";
}

// Process Depreciation form submission
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

    $message = "Configurações de depreciação atualizadas com sucesso!";
}

// Fetch current settings
$configs = [];
// Check column name first to be safe or assume rename worked. 
// We know we ran: ALTER TABLE configuracoes_sla CHANGE tempo_sla_horas tempo_sla_minutos INT...
$result = mysqli_query($conn, "SELECT * FROM configuracoes_sla");
while ($row = mysqli_fetch_assoc($result)) {
    $configs[$row['categoria']] = $row['tempo_sla_minutos'];
}

// Default values if database is empty or category missing (in minutes)
$defaults = ['Incidente' => 1440, 'Mudança' => 2880, 'Requisição' => 4320]; // 24h, 48h, 72h
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

// Fetch per-category donation eligibility
$cat_doacao = [];
$result_cat_doacao = mysqli_query($conn, "SELECT c.categoria, COALESCE(cd.elegivel_doacao, 1) as elegivel_doacao FROM categoria c LEFT JOIN categoria_doacao cd ON c.categoria = cd.categoria ORDER BY c.categoria ASC");
if ($result_cat_doacao) {
    while ($row_cd = mysqli_fetch_assoc($result_cat_doacao)) {
        $cat_doacao[$row_cd['categoria']] = $row_cd['elegivel_doacao'];
    }
}

// Helper function to get hours and minutes from total minutes
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
                    style="margin: 23px;">
                    <div class="container-fluid"><button class="btn btn-link d-md-none rounded-circle mr-3"
                            id="sidebarToggleTop-1" type="button"><i class="fas fa-bars"></i></button>
                        <form
                            class="form-inline d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search position-relative">
                            <div class="input-group">
                                <input class="bg-light form-control border-0 small" type="text"
                                    placeholder="Pesquisar..." id="globalSearchInput" autocomplete="off">
                                <div class="input-group-append"><button class="btn btn-primary py-0" type="button"
                                        style="background: rgb(44,64,74);"><i class="fas fa-search"></i></button></div>
                            </div>
                            <div id="globalSearchResults" class="dropdown-menu shadow animated--grow-in"
                                style="width: 100%; display: none;"></div>
                        </form>
                        <ul class="navbar-nav flex-nowrap ml-auto">
                            <div class="d-none d-sm-block topbar-divider"></div>
                            <li class="nav-item dropdown no-arrow">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link"
                                        aria-expanded="false" data-toggle="dropdown" href="#"><span
                                            class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span><img
                                            class="border rounded-circle img-profile"
                                            src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/Captura%20de%20Tela%202021-08-04%20às%2012.25.13.png?h=fcfb924f0ac1ab5f595f029bf526e62d'; ?>"></a>
                                    <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in"><a
                                            class="dropdown-item" href="profile.php"><i
                                                class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a><a
                                            class="dropdown-item" href="configuracoes.php"><i
                                                class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a><a
                                            class="dropdown-item" href="equipamentos.php?status=Manutencao"><i
                                                class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>Ativos em
                                            Manutenção</a>
                                        <div class="dropdown-divider"></div><a class="dropdown-item" href="login.php"><i
                                                class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="container-fluid">
                    <h3 class="text-dark mb-4">Configurações do Sistema</h3>

                    <?php if (isset($message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php
                    endif; ?>

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
                                        <div class="col-sm-4 d-flex justify-content-between align-items-center pl-4 py-2" style="background: #f8f9fc; border-radius: 12px; border: 1px solid #e3e6f0;">
                                            <div class="text-center" style="flex: 1;">
                                                <span class="badge badge-danger mb-2 px-3 py-1" style="border-radius: 50px; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.5px;">Alta</span>
                                                <div style="height: 6px; background: #dc3545; margin: 0 5px 8px 5px; border-radius: 10px; opacity: 0.8;"></div>
                                                <strong class="text-dark d-block label-50" style="font-size: 0.85rem;">--</strong>
                                            </div>
                                            <div class="text-center mx-1" style="flex: 1;">
                                                <span class="badge badge-warning mb-2 px-3 py-1" style="border-radius: 50px; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.5px; color: #fff; background-color: #f6c23e;">Média</span>
                                                <div style="height: 6px; background: #f6c23e; margin: 0 5px 8px 5px; border-radius: 10px; opacity: 0.8;"></div>
                                                <strong class="text-dark d-block label-75" style="font-size: 0.85rem;">--</strong>
                                            </div>
                                            <div class="text-center" style="flex: 1;">
                                                <span class="badge badge-success mb-2 px-3 py-1" style="border-radius: 50px; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.5px;">Baixa</span>
                                                <div style="height: 6px; background: #1cc88a; margin: 0 5px 8px 5px; border-radius: 10px; opacity: 0.8;"></div>
                                                <strong class="text-dark d-block label-100" style="font-size: 0.85rem;">--</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                } ?>

                                <div class="form-group row mt-4">
                                    <div class="col-sm-10">
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
                                    <div class="col-sm-10">
                                        <button type="submit" class="btn btn-primary"
                                            style="background: rgb(44,64,74);">Salvar Configurações de
                                            Depreciação</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="bg-white sticky-footer" style="background: rgb(34,40,39);padding: 0;">
                <section class="text-center footer" style="padding: 10px;margin-top: 115px;">
                    <p style="margin-bottom: 0px;font-size: 15px;">DEGB&nbsp;Copyright © 2015-2024<br></p>
                </section>
            </footer>
        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
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

                    $row.find('.label-50').text(formatTimeDisplay(Math.round(total * 0.5)));
                    $row.find('.label-75').text(formatTimeDisplay(Math.round(total * 0.75)));
                    $row.find('.label-100').text(formatTimeDisplay(total));
                });
            }

            $('.sla-hours, .sla-minutes').on('input', updateSLABars);
            updateSLABars(); // Initial call
        });
    </script>
</body>

</html>