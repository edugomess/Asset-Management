<?php
include_once 'auth.php';     // Verifica sessão
include_once 'conexao.php';  // Conecta ao banco

// === Toda a sua lógica de SQL deve vir aqui, antes do HTML ===
$count_aberto = 0;
$count_andamento = 0;
// ... (mantenha o restante das suas queries SQL que estavam no corpo do código aqui)

// Apenas após processar tudo, começamos o HTML
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Dashboard - Brand</title>
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
    <?php include 'sidebar_style.php'; ?>
</head>

<?php
// === Contagem de Chamados (QUERY UNICA para todos os status) ===
$count_aberto = 0;
$count_andamento = 0;
$count_pendente = 0;
$data = [];
$total_ativos = 0;

$res = mysqli_query($conn, "SELECT status, COUNT(*) as total FROM chamados WHERE status IN ('Aberto', 'Em Andamento', 'Pendente') GROUP BY status");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$row['status']] = $row['total'];
        $total_ativos += $row['total'];
        if ($row['status'] == 'Aberto')
            $count_aberto = $row['total'];
        elseif ($row['status'] == 'Em Andamento')
            $count_andamento = $row['total'];
        elseif ($row['status'] == 'Pendente')
            $count_pendente = $row['total'];
    }
}
$data_string = implode(",", [
    isset($data['Aberto']) ? $data['Aberto'] : 0,
    isset($data['Em Andamento']) ? $data['Em Andamento'] : 0,
    isset($data['Pendente']) ? $data['Pendente'] : 0
]);

// === Chamados Fechados por Mês (QUERY UNICA) ===
$closed_data = array_fill(1, 12, 0);
$res_closed = mysqli_query($conn, "SELECT MONTH(data_fechamento) as month, COUNT(*) as count FROM chamados WHERE status IN ('Resolvido', 'Fechado', 'Cancelado') AND YEAR(data_fechamento) = YEAR(CURRENT_DATE()) GROUP BY MONTH(data_fechamento)");
if ($res_closed) {
    while ($row = mysqli_fetch_assoc($res_closed)) {
        $closed_data[$row['month']] = $row['count'];
    }
}
$closed_string = implode(",", $closed_data);
?>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion p-0">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include 'sidebar_brand.php'; ?>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-1 topbar static-top"
                    style="margin: 5px 23px;">
                    <div class="container-fluid"><button class="btn btn-link d-md-none rounded-circle mr-3"
                            id="sidebarToggleTop-1" type="button"><i class="fas fa-bars"></i></button>
                        <form
                            class="form-inline d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search position-relative">
                            <div class="input-group">
                                <input class="bg-light form-control border-0 small" type="text"
                                    placeholder="Pesquisar..." id="globalSearchInput" autocomplete="off">
                                <div class="input-group-append"><button class="btn btn-primary py-0" type="button"
                                        style="background: rgb(44,64,74);"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                            <div id="globalSearchResults" class="dropdown-menu shadow animated--grow-in"
                                style="width: 100%; display: none;"></div>
                        </form>
                        <ul class="navbar-nav flex-nowrap ml-auto">
                            <li class="nav-item dropdown d-sm-none no-arrow"><a class="dropdown-toggle nav-link"
                                    aria-expanded="false" data-toggle="dropdown" href="#"><i
                                        class="fas fa-search"></i></a>
                                <div class="dropdown-menu dropdown-menu-right p-3 animated--grow-in"
                                    aria-labelledby="searchDropdown">
                                    <form class="form-inline mr-auto navbar-search w-100">
                                        <div class="input-group"><input class="bg-light form-control border-0 small"
                                                type="text" placeholder="Search for ...">
                                            <div class="input-group-append"><button class="btn btn-primary py-0"
                                                    type="button"><i class="fas fa-search"></i></button></div>
                                        </div>
                                    </form>
                                </div>
                            </li>
                            <li class="nav-item dropdown no-arrow mx-1">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link"
                                        aria-expanded="false" data-toggle="dropdown" href="#"></a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-list animated--grow-in">
                                        <h6 class="dropdown-header">alerts center</h6><a
                                            class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="mr-3">
                                                <div class="bg-primary icon-circle"><i
                                                        class="fas fa-file-alt text-white"></i></div>
                                            </div>
                                            <div><span class="small text-gray-500">December 12, 2019</span>
                                                <p>A new monthly report is ready to download!</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="mr-3">
                                                <div class="bg-success icon-circle"><i
                                                        class="fas fa-donate text-white"></i></div>
                                            </div>
                                            <div><span class="small text-gray-500">December 7, 2019</span>
                                                <p>$290.29 has been deposited into your account!</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="mr-3">
                                                <div class="bg-warning icon-circle"><i
                                                        class="fas fa-exclamation-triangle text-white"></i></div>
                                            </div>
                                            <div><span class="small text-gray-500">December 2, 2019</span>
                                                <p>Spending Alert: We've noticed unusually high spending for your
                                                    account.</p>
                                            </div>
                                        </a><a class="dropdown-item text-center small text-gray-500" href="#">Show
                                            All Alerts</a>
                                    </div>
                                </div>
                            </li>
                            <li class="nav-item dropdown no-arrow mx-1">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link"
                                        aria-expanded="false" data-toggle="dropdown" href="#"></a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-list animated--grow-in">
                                        <h6 class="dropdown-header">alerts center</h6><a
                                            class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle"
                                                    src="/assets/img/avatars/avatar4.jpeg?h=fefb30b61c8459a66bd338b7d790c3d5">
                                                <div class="bg-success status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>Hi there! I am wondering if you can
                                                        help me with a problem I've been having.</span></div>
                                                <p class="small text-gray-500 mb-0">Emily Fowler - 58m</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle"
                                                    src="/assets/img/avatars/avatar2.jpeg?h=5d142be9441885f0935b84cf739d4112">
                                                <div class="status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>I have the photos that you ordered
                                                        last month!</span></div>
                                                <p class="small text-gray-500 mb-0">Jae Chun - 1d</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle"
                                                    src="/assets/img/avatars/avatar3.jpeg?h=c5166867f10a4e454b5b2ae8d63268b3">
                                                <div class="bg-warning status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>Last month's report looks great, I
                                                        am very happy with the progress so far, keep up the good
                                                        work!</span></div>
                                                <p class="small text-gray-500 mb-0">Morgan Alvarez - 2d</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle"
                                                    src="/assets/img/avatars/avatar5.jpeg?h=35dc45edbcda6b3fc752dab2b0f082ea">
                                                <div class="bg-success status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>Am I a good boy? The reason I ask
                                                        is because someone told me that people say this to all dogs,
                                                        even if they aren't good...</span></div>
                                                <p class="small text-gray-500 mb-0">Chicken the Dog · 2w</p>
                                            </div>
                                        </a><a class="dropdown-item text-center small text-gray-500" href="#">Show
                                            All Alerts</a>
                                    </div>
                                </div>
                                <div class="shadow dropdown-list dropdown-menu dropdown-menu-right"
                                    aria-labelledby="alertsDropdown"></div>
                            </li>
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
                                        <div class="dropdown-divider"></div><a href="logout.php"
                                            class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="container-fluid" style="background: #ffffff;">
                    <div class="d-sm-flex justify-content-between align-items-center mb-4">
                        <h3 class="text-dark mb-0">Dashboard</h3>
                        <div class="d-flex align-items-center">
                            <?php
                            $sql_total_fechados = "SELECT COUNT(*) as total FROM chamados WHERE status IN ('Resolvido', 'Fechado', 'Cancelado')";
                            $res_total_fechados = mysqli_query($conn, $sql_total_fechados);
                            $total_fechados = mysqli_fetch_assoc($res_total_fechados)['total'] ?? 0;
                            ?>
                            <div class="bg-success text-white px-2 py-1 rounded shadow-sm d-flex align-items-center mr-2"
                                style="font-size: 0.75rem; height: 31px;">
                                <i class="fas fa-check-circle mr-1"></i>
                                <span class="font-weight-bold">Total de chamados fechados:
                                    <?php echo $total_fechados; ?></span>
                            </div>
                            <a class="text-white px-2 py-1 rounded shadow-sm d-flex align-items-center text-decoration-none"
                                role="button" href="relatorio_resumo_geral.php" target="_blank"
                                style="background: #e74a3b; font-size: 0.75rem; height: 31px;">
                                <i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i>
                                <span class="font-weight-bold">Gerar Relatório</span>
                            </a>
                        </div>
                    </div>
                    <div class="row px-2 flex-nowrap overflow-auto">
                        <?php
                        // Buscar contagem de ativos por categoria
                        $categorias_interesse = [
                            'Computadores' => ['icon' => 'fas fa-desktop', 'color' => 'primary', 'label' => 'Computadores'],
                            'Laptops' => ['icon' => 'fas fa-laptop', 'color' => 'success', 'label' => 'Laptops'], // Assumindo que Laptops podem estar na categoria Computadores ou separados
                            'Periféricos' => ['icon' => 'far fa-keyboard', 'color' => 'info', 'label' => 'Periféricos'], // Ajustar conforme nome real na tabela
                            'Impressoras' => ['icon' => 'fas fa-print', 'color' => 'warning', 'label' => 'Impressoras']
                        ];


                        // Query genérica para pegar todas as categorias e contagens
                        $sql_ativos = "SELECT categoria, COUNT(*) as total, SUM(CASE WHEN assigned_to IS NULL OR assigned_to = 0 THEN 1 ELSE 0 END) as disponiveis 
                                       FROM ativos GROUP BY categoria";
                        $res_ativos = mysqli_query($conn, $sql_ativos);
                        $dados_ativos = [];
                        if ($res_ativos) {
                            while ($row = mysqli_fetch_assoc($res_ativos)) {
                                $dados_ativos[$row['categoria']] = $row;
                            }
                        }

                        // SLA Ranking Logic
                        $sql_ranking = "SELECT 
    r.nome, r.sobrenome, r.id_usuarios, r.foto_perfil,
    COUNT(*) as total,
    SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, c.data_abertura, c.data_fechamento) <= 10 THEN 1 ELSE 0 END) as met_sla
FROM chamados c
JOIN usuarios r ON c.responsavel_id = r.id_usuarios
WHERE c.status IN ('Resolvido', 'Fechado', 'Cancelado')
AND c.data_fechamento >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY r.id_usuarios
ORDER BY (SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, c.data_abertura, c.data_fechamento) <= 10 THEN 1 ELSE 0 END) / COUNT(*)) DESC";
                        $res_ranking = mysqli_query($conn, $sql_ranking);
                        $ranking_data = [];
                        if ($res_ranking) {
                            while ($row = mysqli_fetch_assoc($res_ranking)) {
                                $total = (int) $row['total'];
                                $met_sla = (int) $row['met_sla'];
                                $row['percentage'] = ($total > 0) ? round(($met_sla / $total) * 100) : 0;
                                $ranking_data[] = $row;
                            }
                        }
                        // (Closed data already computed above, no duplicate query needed)
                        
                        // Ranking de Chamados por Recorrência (Top 5 títulos mais frequentes)
                        $sql_recorrencia = "SELECT titulo, COUNT(*) as total FROM chamados GROUP BY titulo ORDER BY total DESC LIMIT 5";
                        $res_recorrencia = mysqli_query($conn, $sql_recorrencia);
                        $recorrencia_data = [];
                        $max_recorrencia = 0;
                        if ($res_recorrencia) {
                            while ($row = mysqli_fetch_assoc($res_recorrencia)) {
                                $recorrencia_data[] = $row;
                                if ($row['total'] > $max_recorrencia) {
                                    $max_recorrencia = $row['total'];
                                }
                            }
                        }


                        // 4. Licenças - Métricas Específicas
                        // M365
                        $sql_m365 = "SELECT SUM(quantidade_total) as total, SUM(quantidade_uso) as em_uso FROM licencas WHERE software LIKE '%365%'";
                        $res_m365 = mysqli_query($conn, $sql_m365);
                        $data_m365 = mysqli_fetch_assoc($res_m365);
                        $total_m365 = $data_m365['total'] ?? 0;
                        $disp_m365 = $total_m365 - ($data_m365['em_uso'] ?? 0);

                        // Adobe
                        $sql_adobe = "SELECT SUM(quantidade_total) as total, SUM(quantidade_uso) as em_uso FROM licencas WHERE software LIKE '%Adobe%' OR fabricante LIKE '%Adobe%'";
                        $res_adobe = mysqli_query($conn, $sql_adobe);
                        $data_adobe = mysqli_fetch_assoc($res_adobe);
                        $total_adobe = $data_adobe['total'] ?? 0;
                        $disp_adobe = $total_adobe - ($data_adobe['em_uso'] ?? 0);

                        // Windows
                        $sql_win = "SELECT SUM(quantidade_total) as total, SUM(quantidade_uso) as em_uso FROM licencas WHERE software LIKE '%Windows%'";
                        $res_win = mysqli_query($conn, $sql_win);
                        $data_win = mysqli_fetch_assoc($res_win);
                        $total_win = $data_win['total'] ?? 0;
                        $disp_win = $total_win - ($data_win['em_uso'] ?? 0);

                        // Expiradas ou prestes a vencer (60 dias)
                        $sql_exp = "SELECT COUNT(*) as total FROM licencas WHERE status = 'Expirada' OR (data_expiracao IS NOT NULL AND data_expiracao <= DATE_ADD(CURDATE(), INTERVAL 60 DAY))";
                        $res_exp = mysqli_query($conn, $sql_exp);
                        $count_exp = mysqli_fetch_assoc($res_exp)['total'] ?? 0;

                        // Mapeamento manual para os cards (ajuste as chaves conforme o banco de dados)
                        // Exemplo: 'Computadores' no banco pode mapear para o card 'Computadores'
                        // Se não houver correspondencia exata, você pode criar cards genéricos ou ajustar o array $categorias_interesse
                        
                        // Para simplificar e atender o pedido, vamos criar cards dinâmicos baseados no que tem no banco, 
                        // ou manter o layout fixo e preencher com o que encontrar.
                        // Vamos tentar preencher os 4 cards fixos com os dados mais prováveis.
                        
                        // Card 1: Desktops
                        $total_pc = isset($dados_ativos['Desktop']) ? $dados_ativos['Desktop']['total'] : 0;
                        $disp_pc = isset($dados_ativos['Desktop']) ? $dados_ativos['Desktop']['disponiveis'] : 0;

                        // Card 2: Notebooks
                        $total_note = isset($dados_ativos['Notebook']) ? $dados_ativos['Notebook']['total'] : (isset($dados_ativos['Notebooks']) ? $dados_ativos['Notebooks']['total'] : 0);
                        $disp_note = isset($dados_ativos['Notebook']) ? $dados_ativos['Notebook']['disponiveis'] : (isset($dados_ativos['Notebooks']) ? $dados_ativos['Notebooks']['disponiveis'] : 0);

                        // Card 3: Monitores (Monitor, Monitores)
                        $total_mon = (isset($dados_ativos['Monitor']) ? $dados_ativos['Monitor']['total'] : 0) +
                            (isset($dados_ativos['Monitores']) ? $dados_ativos['Monitores']['total'] : 0);
                        $disp_mon = (isset($dados_ativos['Monitor']) ? $dados_ativos['Monitor']['disponiveis'] : 0) +
                            (isset($dados_ativos['Monitores']) ? $dados_ativos['Monitores']['disponiveis'] : 0);

                        // Card 4: Impressoras
                        $total_imp = isset($dados_ativos['Impressoras']) ? $dados_ativos['Impressoras']['total'] : 0;
                        $disp_imp = isset($dados_ativos['Impressoras']) ? $dados_ativos['Impressoras']['disponiveis'] : 0;

                        // Se os totais forem 0, exibir pelo menos um placeholder ou buscar tudo
                        // Vamos exibir todos:
                        ?>

                        <div class="col-xl-1-5 col-md-3 mb-4 px-1"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px;">
                            <div class="card shadow border-left-primary py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-primary font-weight-bold text-sm mb-1">
                                                <span>Desktops</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_pc; ?></span>
                                                <span class="text-muted small ml-1"
                                                    style="font-size: 0.85rem;">(<?php echo $disp_pc; ?>
                                                    Disponíveis)</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-desktop fa-lg text-gray-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-1-5 col-md-3 mb-4 px-1"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px;">
                            <div class="card shadow border-left-success py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-success font-weight-bold text-sm mb-1">
                                                <span>Notebooks</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_note; ?></span>
                                                <span class="text-muted small ml-1"
                                                    style="font-size: 0.85rem;">(<?php echo $disp_note; ?>
                                                    Disponíveis)</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-laptop fa-lg text-gray-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-1-5 col-md-3 mb-4 px-1"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px;">
                            <div class="card shadow border-left-info py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-info font-weight-bold text-sm mb-1">
                                                <span>Monitores</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_mon; ?></span>
                                                <span class="text-muted small ml-1"
                                                    style="font-size: 0.85rem;">(<?php echo $disp_mon; ?>
                                                    Disponíveis)</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-desktop fa-lg text-gray-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-1-5 col-md-3 mb-4 px-1"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px;">
                            <div class="card shadow border-left-warning py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-warning font-weight-bold text-sm mb-1">
                                                <span>Impressoras</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_imp; ?></span>
                                                <span class="text-muted small ml-1"
                                                    style="font-size: 0.85rem;">(<?php echo $disp_imp; ?>
                                                    Disponíveis)</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-print fa-lg text-gray-600"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Novas Licenças na mesma fileira -->
                        <div class="col-xl-1-5 col-md-3 mb-4 px-1"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px;">
                            <div class="card shadow border-left-secondary py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-secondary font-weight-bold text-sm mb-1">
                                                <span>Office 365</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_m365; ?></span>
                                                <span class="text-muted small ml-1" style="font-size: 0.85rem;">(
                                                    <?php echo $disp_m365; ?> Disponíveis)</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-cloud fa-lg text-gray-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-1-5 col-md-3 mb-4 px-1"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px;">
                            <div class="card shadow border-left-danger py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-danger font-weight-bold text-sm mb-1">
                                                <span>Adobe CC</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_adobe; ?></span>
                                                <span class="text-muted small ml-1"
                                                    style="font-size: 0.85rem;">(<?php echo $disp_adobe; ?>
                                                    Disponíveis)</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-palette fa-lg text-gray-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-1-5 col-md-3 mb-4 px-1"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px;">
                            <div class="card shadow border-left-dark py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-dark font-weight-bold text-sm mb-1">
                                                <span>Windows 11</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_win; ?></span>
                                                <span class="text-muted small ml-1"
                                                    style="font-size: 0.85rem;">(<?php echo $disp_win; ?>
                                                    Disponíveis)</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fab fa-windows fa-lg text-gray-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-1-5 col-md-3 mb-4 px-1"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px;">
                            <div class="card shadow border-left-warning py-2"
                                style="border-left-color: #f6c23e !important;">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-warning font-weight-bold text-sm mb-1">
                                                <span>Alertas</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $count_exp; ?></span>
                                                <span class="text-muted small ml-1"
                                                    style="font-size: 0.85rem;">Expirando</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i
                                                class="fas fa-exclamation-circle fa-lg text-gray-600"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-7 col-xl-8">
                            <div class="card shadow mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center"
                                    style="background: rgb(248, 249, 252);">
                                    <h6 class="text-primary font-weight-bold m-0">Chamados Finalizados (Mês)</h6>
                                    <div class="dropdown no-arrow"><button class="btn btn-link btn-sm dropdown-toggle"
                                            aria-expanded="false" data-toggle="dropdown" type="button"><i
                                                class="fas fa-ellipsis-v text-gray-400"></i></button>
                                        <div class="dropdown-menu shadow dropdown-menu-right animated--fade-in">
                                            <p class="text-center dropdown-header">Opções:</p><a class="dropdown-item"
                                                href="chamados.php?filtro_status=finalizados">&nbsp;Ver
                                                Finalizados</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area"><canvas
                                            data-bss-chart="{&quot;type&quot;:&quot;line&quot;,&quot;data&quot;:{&quot;labels&quot;:[&quot;Jan&quot;,&quot;Fev&quot;,&quot;Mar&quot;,&quot;Abr&quot;,&quot;Mai&quot;,&quot;Jun&quot;,&quot;Jul&quot;,&quot;Ago&quot;,&quot;Set&quot;,&quot;Out&quot;,&quot;Nov&quot;,&quot;Dez&quot;],&quot;datasets&quot;:[{&quot;label&quot;:&quot;Chamados&quot;,&quot;fill&quot;:true,&quot;data&quot;:[<?php echo $closed_string; ?>],&quot;backgroundColor&quot;:&quot;rgba(78, 115, 223, 0.05)&quot;,&quot;borderColor&quot;:&quot;rgb(26, 121, 158)&quot;}]},&quot;options&quot;:{&quot;maintainAspectRatio&quot;:false,&quot;legend&quot;:{&quot;display&quot;:false,&quot;labels&quot;:{&quot;fontStyle&quot;:&quot;normal&quot;}},&quot;title&quot;:{&quot;fontStyle&quot;:&quot;normal&quot;},&quot;scales&quot;:{&quot;xAxes&quot;:[{&quot;gridLines&quot;:{&quot;color&quot;:&quot;rgb(234, 236, 244)&quot;,&quot;zeroLineColor&quot;:&quot;rgb(234, 236, 244)&quot;,&quot;drawBorder&quot;:false,&quot;drawTicks&quot;:false,&quot;borderDash&quot;:[&quot;2&quot;],&quot;zeroLineBorderDash&quot;:[&quot;2&quot;],&quot;drawOnChartArea&quot;:false},&quot;ticks&quot;:{&quot;fontColor&quot;:&quot;#858796&quot;,&quot;fontStyle&quot;:&quot;normal&quot;,&quot;padding&quot;:20}}],&quot;yAxes&quot;:[{&quot;gridLines&quot;:{&quot;color&quot;:&quot;rgb(234, 236, 244)&quot;,&quot;zeroLineColor&quot;:&quot;rgb(234, 236, 244)&quot;,&quot;drawBorder&quot;:false,&quot;drawTicks&quot;:false,&quot;borderDash&quot;:[&quot;2&quot;],&quot;zeroLineBorderDash&quot;:[&quot;2&quot;]},&quot;ticks&quot;:{&quot;fontColor&quot;:&quot;#858796&quot;,&quot;fontStyle&quot;:&quot;normal&quot;,&quot;padding&quot;:20}}]}}}"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 col-xl-4">
                            <div class="card shadow mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="text-primary font-weight-bold m-0">Status dos Chamados</h6>
                                    <div class="dropdown no-arrow"><button class="btn btn-link btn-sm dropdown-toggle"
                                            aria-expanded="false" data-toggle="dropdown" type="button"><i
                                                class="fas fa-ellipsis-v text-gray-400"></i></button>
                                        <div class="dropdown-menu shadow dropdown-menu-right animated--fade-in">
                                            <p class="text-center dropdown-header">Opções:</p><a class="dropdown-item"
                                                href="chamados.php">&nbsp;Ver Chamados</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area" style="position: relative;">
                                        <canvas
                                            data-bss-chart="{&quot;type&quot;:&quot;doughnut&quot;,&quot;data&quot;:{&quot;labels&quot;:[&quot;Aberto&quot;,&quot;Em Andamento&quot;,&quot;Pendente&quot;],&quot;datasets&quot;:[{&quot;label&quot;:&quot;&quot;,&quot;backgroundColor&quot;:[&quot;#4e73df&quot;,&quot;#36b9cc&quot;,&quot;#f6c23e&quot;],&quot;borderColor&quot;:[&quot;#ffffff&quot;,&quot;#ffffff&quot;,&quot;#ffffff&quot;],&quot;data&quot;:[<?php echo $data_string; ?>]}]},&quot;options&quot;:{&quot;maintainAspectRatio&quot;:false,&quot;cutoutPercentage&quot;:80,&quot;legend&quot;:{&quot;display&quot;:false,&quot;labels&quot;:{&quot;fontStyle&quot;:&quot;normal&quot;}},&quot;title&quot;:{&quot;fontStyle&quot;:&quot;normal&quot;},&quot;tooltips&quot;:{&quot;backgroundColor&quot;:&quot;#fff&quot;,&quot;bodyFontColor&quot;:&quot;#858796&quot;,&quot;borderColor&quot;:&quot;#dddfeb&quot;,&quot;borderWidth&quot;:1,&quot;xPadding&quot;:15,&quot;yPadding&quot;:15,&quot;displayColors&quot;:false,&quot;caretPadding&quot;:10}}}"></canvas>
                                        <div
                                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 4.7rem; font-weight: 800; color: #5a5c69; pointer-events: none;">
                                            <?php echo $total_ativos; ?>
                                        </div>
                                    </div>
                                    <div class="text-center small mt-4"><span class="mr-2"><i
                                                class="fas fa-circle text-primary"></i> Aberto</span><span
                                            class="mr-2"><i class="fas fa-circle text-info"></i> Em And.</span><span
                                            class="mr-2"><i class="fas fa-circle text-warning"></i> Pendente</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- End: Charts -->
                    <!-- Start: SLA Ranking -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Ranking de SLA (Último Mês) -
                                        Melhores Técnicos</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Responsável</th>
                                                    <th>Chamados Resolvidos</th>
                                                    <th>Dentro do Prazo </th>
                                                    <th>% SLA Atingido</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ranking_data as $rank): ?>
                                                    <tr>
                                                        <td class="align-middle">
                                                            <img class="img-profile rounded-circle"
                                                                style="width: 30px; height: 30px; margin-right: 10px; object-fit: cover;"
                                                                src="<?php echo !empty($rank['foto_perfil']) ? htmlspecialchars($rank['foto_perfil']) : '/assets/img/avatars/avatar1.jpeg'; ?>">
                                                            <?php echo htmlspecialchars($rank['nome'] . ' ' . $rank['sobrenome']); ?>
                                                        </td>
                                                        <td class="align-middle"><?php echo $rank['total']; ?></td>
                                                        <td class="align-middle"><?php echo $rank['met_sla']; ?></td>
                                                        <td class="align-middle">
                                                            <div class="progress" style="height: 20px;">
                                                                <?php
                                                                $color = 'bg-danger';
                                                                if ($rank['percentage'] >= 80)
                                                                    $color = 'bg-success';
                                                                elseif ($rank['percentage'] >= 50)
                                                                    $color = 'bg-warning';
                                                                ?>
                                                                <div class="progress-bar <?php echo $color; ?>"
                                                                    role="progressbar"
                                                                    style="width: <?php echo $rank['percentage']; ?>%"
                                                                    aria-valuenow="<?php echo $rank['percentage']; ?>"
                                                                    aria-valuemin="0" aria-valuemax="100">
                                                                    <?php echo $rank['percentage']; ?>%
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                endforeach; ?>
                                                <?php if (empty($ranking_data)): ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center">Nenhum chamado finalizado no
                                                            último mês.</td>
                                                    </tr>
                                                    <?php
                                                endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- End: SLA Ranking -->
                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary font-weight-bold m-0">Ranking de Chamados por
                                        Recorrência</h6>
                                </div>
                                <div class="card-body">
                                    <?php
                                    if (!empty($recorrencia_data)) {
                                        $cores = ['bg-danger', 'bg-warning', 'bg-primary', 'bg-info', 'bg-success'];
                                        foreach ($recorrencia_data as $i => $rec) {
                                            $pct = ($max_recorrencia > 0) ? round(($rec['total'] / $max_recorrencia) * 100) : 0;
                                            $cor = $cores[$i % count($cores)];
                                            $titulo_chamado = htmlspecialchars(mb_strimwidth($rec['titulo'], 0, 45, '...'));
                                            ?>
                                            <h4 class="small font-weight-bold"><?php echo $titulo_chamado; ?><span
                                                    class="float-right"><?php echo $rec['total']; ?> chamado(s)</span></h4>
                                            <div class="progress mb-4">
                                                <div class="progress-bar <?php echo $cor; ?>" role="progressbar"
                                                    aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100"
                                                    style="width: <?php echo $pct; ?>%;">
                                                    <?php echo $rec['total']; ?>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        echo '<p class="text-center text-muted">Nenhum chamado registrado.</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="bg-white sticky-footer" style="background: rgb(34,40,39);padding: 0;">
                <!-- Start: Simple footer by krissy -->
                <section class="text-center footer" style="padding: 10px;margin-top: 70px;">
                    <!-- Start: Footer text -->
                    <p style="margin-bottom: 0px;font-size: 15px;">DEGB&nbsp;Copyright © 2015-2024<br></p>
                    <!-- End: Footer text -->
                </section><!-- End: Simple footer by krissy -->
            </footer>
        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
    <script>
        // Inline script removed, moved to global_search.js
    </script>
</body>

</html>