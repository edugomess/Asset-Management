<?php
include 'auth.php';
include 'conexao.php';
?>
<!DOCTYPE html>
<html>
<style>
    .btn-tamanho-fixo { 
        width: 130px; 
    }
    .btn-edit {
        width: 50px; /* Metade da largura */
    }
    .btn-medium {
        width: 100px; /* Metade da largura */
    }
    <style>
    .badge-success {
        background-color: #28a745 !important; /* Verde para ativo */
        color: #fff !important; /* Texto branco */
    }
    .badge-danger {
        background-color: #dc3545 !important; /* Vermelho para inativo */
        color: #fff !important; /* Texto branco */
    }
</style>

</style>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Table - Brand</title>
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
    <link rel="stylesheet" href="/assets/css/Form-Select---Full-Date---Month-Day-Year.css?h=7b6a3c2cb7894fdb77bae43c70b92224">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightpick@1.3.4/css/lightpick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="/assets/css/Map-Clean.css?h=bdd15207233b27ebc7c6fc928c71b34c">
    <link rel="stylesheet" href="/assets/css/Modern-Contact-Form.css?h=af67b929d317df499a992472a9bb8fcc">
    <link rel="stylesheet" href="/assets/css/Multi-Select-Dropdown-by-Jigar-Mistry.css?h=28bd9d636c700fbf60086e2bcb002efb">
    <link rel="stylesheet" href="/assets/css/Password-Strenght-Checker---Ambrodu-1.css?h=1af6ac373aa34a3b40f3d87a4f494eaf">
    <link rel="stylesheet" href="/assets/css/Password-Strenght-Checker---Ambrodu.css?h=5818638767f362b9d58a96550bd9a9a3">
    <link rel="stylesheet" href="/assets/css/Simple-footer-by-krissy.css?h=73316da5ae5ad6b51632cd2e5413f263">
    <link rel="stylesheet" href="/assets/css/TR-Form.css?h=ce0bc58b5b8027e2406229d460f4d895">
    <script src="https://kit.fontawesome.com/8786c39b09.js"></script>
   

</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0" style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0"><a class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#">
                    <div class="sidebar-brand-icon rotate-n-15"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-layout-distribute-horizontal" style="width: 30px;height: 30px;">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <line x1="4" y1="4" x2="20" y2="4"></line>
                            <line x1="4" y1="20" x2="20" y2="20"></line>
                            <rect x="6" y="9" width="12" height="6" rx="2"></rect>
                        </svg></div>
                        
                    <div class="sidebar-brand-text mx-3"><span>ASSET MGT</span></div>
                </a>
                <hr class="sidebar-divider my-0">
                <ul class="navbar-nav text-light" id="accordionSidebar">
                    <li class="nav-item"><a class="nav-link" href="/index.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/inicio.php"><i class="fas fa-home"></i><span> Início</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/usuarios.php"><i class="fas fa-user-alt"></i><span> Usuários</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/centro_de_custo.php"><i class="fas fa-file-invoice-dollar"></i><span> Centro de Custo</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/fornecedores.php"><i class="fas fa-hands-helping"></i><span> Fornecedores</span></a></li>
                    <li class="nav-item"><a class="nav-link active" href="/equipamentos.php"><i class="fas fa-boxes"></i><span> Ativos</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/relatorios.php"><i class="fas fa-scroll"></i><span> Relatórios</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/chamados.php"><i class="fas fa-headset"></i><span> Chamados</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/suporte.php"><i class="fas fa-user-cog"></i><span> Suporte</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/agent.php"><i class="fas fa-robot"></i><span> IA Agent</span></a></li>
                </ul>
                <div class="text-center d-none d-md-inline"><button class="btn rounded-circle border-0" id="sidebarToggle" type="button"></button></div>
                
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top" style="margin: 23px;">
                    <div class="container-fluid"><button class="btn btn-link d-md-none rounded-circle mr-3" id="sidebarToggleTop-1" type="button"><i class="fas fa-bars"></i></button>
                        <form class="form-inline d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search position-relative">
                            <div class="input-group">
                                <input class="bg-light form-control border-0 small" type="text" placeholder="Pesquisar..." id="globalSearchInput" autocomplete="off">
                                <div class="input-group-append"><button class="btn btn-primary py-0" type="button" style="background: rgb(44,64,74);"><i class="fas fa-search"></i></button></div>
                            </div>
                            <div id="globalSearchResults" class="dropdown-menu shadow animated--grow-in" style="width: 100%; display: none;"></div>
                        </form>
                        <ul class="navbar-nav flex-nowrap ml-auto">
                            <li class="nav-item dropdown d-sm-none no-arrow"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#"><i class="fas fa-search"></i></a>
                                <div class="dropdown-menu dropdown-menu-right p-3 animated--grow-in" aria-labelledby="searchDropdown">
                                    <form class="form-inline mr-auto navbar-search w-100">
                                        <div class="input-group"><input class="bg-light form-control border-0 small" type="text" placeholder="Search for ...">
                                            <div class="input-group-append"><button class="btn btn-primary py-0" type="button"><i class="fas fa-search"></i></button></div>
                                        </div>
                                    </form>
                                </div>
                            </li>
                            <li class="nav-item dropdown no-arrow mx-1">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#"></a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-list animated--grow-in">
                                        <h6 class="dropdown-header">alerts center</h6><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="mr-3">
                                                <div class="bg-primary icon-circle"><i class="fas fa-file-alt text-white"></i></div>
                                            </div>
                                            <div><span class="small text-gray-500">December 12, 2019</span>
                                                <p>A new monthly report is ready to download!</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="mr-3">
                                                <div class="bg-success icon-circle"><i class="fas fa-donate text-white"></i></div>
                                            </div>
                                            <div><span class="small text-gray-500">December 7, 2019</span>
                                                <p>$290.29 has been deposited into your account!</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="mr-3">
                                                <div class="bg-warning icon-circle"><i class="fas fa-exclamation-triangle text-white"></i></div>
                                            </div>
                                            <div><span class="small text-gray-500">December 2, 2019</span>
                                                <p>Spending Alert: We've noticed unusually high spending for your account.</p>
                                            </div>
                                        </a><a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                                    </div>
                                </div>
                            </li>
                            <li class="nav-item dropdown no-arrow mx-1">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#"></a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-list animated--grow-in">
                                        <h6 class="dropdown-header">alerts center</h6><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle" src="/assets/img/avatars/avatar4.jpeg?h=fefb30b61c8459a66bd338b7d790c3d5">
                                                <div class="bg-success status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>Hi there! I am wondering if you can help me with a problem I've been having.</span></div>
                                                <p class="small text-gray-500 mb-0">Emily Fowler - 58m</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle" src="/assets/img/avatars/avatar2.jpeg?h=5d142be9441885f0935b84cf739d4112">
                                                <div class="status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>I have the photos that you ordered last month!</span></div>
                                                <p class="small text-gray-500 mb-0">Jae Chun - 1d</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle" src="/assets/img/avatars/avatar3.jpeg?h=c5166867f10a4e454b5b2ae8d63268b3">
                                                <div class="bg-warning status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>Last month's report looks great, I am very happy with the progress so far, keep up the good work!</span></div>
                                                <p class="small text-gray-500 mb-0">Morgan Alvarez - 2d</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle" src="/assets/img/avatars/avatar5.jpeg?h=35dc45edbcda6b3fc752dab2b0f082ea">
                                                <div class="bg-success status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>Am I a good boy? The reason I ask is because someone told me that people say this to all dogs, even if they aren't good...</span></div>
                                                <p class="small text-gray-500 mb-0">Chicken the Dog · 2w</p>
                                            </div>
                                        </a><a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                                    </div>
                                </div>
                                <div class="shadow dropdown-list dropdown-menu dropdown-menu-right" aria-labelledby="alertsDropdown"></div>
                            </li>
                            <div class="d-none d-sm-block topbar-divider"></div>
                            <li class="nav-item dropdown no-arrow">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#"><span class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span><img class="border rounded-circle img-profile" src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/Captura%20de%20Tela%202021-08-04%20às%2012.25.13.png?h=fcfb924f0ac1ab5f595f029bf526e62d'; ?>"></a>
                                    <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in"><a class="dropdown-item" href="profile.php"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a><a class="dropdown-item" href="configuracoes.php"><i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a><a class="dropdown-item" href="#"><i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>Desativar conta</a>
                                        <div class="dropdown-divider"></div><a class="dropdown-item" href="login.php"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="container-fluid">
                    <h3 class="text-dark mb-4">Ativos</h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-xl-3 text-nowrap">
                                    <div id="dataTable_length" class="dataTables_length" aria-controls="dataTable"><a class="btn btn-success btn-block active text-white pulse animated btn-user" role="button" style="background: rgb(44,64,74);border-radius: 10px;border-width: 0px;height: 50px;margin-top: 23px;padding: 30px, 30px;margin-bottom: 0px;padding-top: 13px;" href="/cadastro_de_equipamentos.php">Cadastrar Novo</a></div>
                                </div>
                                <div class="col-md-6 col-xl-9">
                                    <div class="text-md-right dataTables_filter" id="dataTable_filter"><form method="GET" action=""><label><input type="search" name="search" class="form-control form-control-sm" aria-controls="dataTable" placeholder="Buscar..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"></label></form></div>
                                </div>
                            </div>
                            <div class="table-responsive table mt-2" id="dataTable" role="grid" aria-describedby="dataTable_info">
                                    <?php
// include 'auth.php'; -- Moved to top
// include 'conexao.php'; -- Moved to top

// Definir o número de resultados por página
$results_per_page = 10;

// Buscar termo de pesquisa
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where_clause = "";
if (!empty($search)) {
    $where_clause = "WHERE modelo LIKE '%$search%' OR tag LIKE '%$search%' OR hostName LIKE '%$search%'";
}

// Verificar o número de resultados no banco de dados
$sql = "SELECT COUNT(*) AS total FROM ativos $where_clause";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_results = $row['total'];

// Determinar o número de páginas necessárias
$total_pages = ceil($total_results / $results_per_page);

// Determinar a página atual a partir da URL, se não definida, assume 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calcular o limite de registros para a consulta
$start_from = ($current_page - 1) * $results_per_page;

// Consultar os ativos
$sql = "SELECT * FROM ativos $where_clause LIMIT $start_from, $results_per_page";
$result = mysqli_query($conn, $sql);

// Buscar configurações de depreciação/doação globais
$dep_config_eq = [
    'taxa_depreciacao' => 10.00,
    'periodo_anos' => 1,
    'periodo_meses' => 0,
    'elegivel_doacao' => 0,
    'tempo_doacao_anos' => 5,
    'tempo_doacao_meses' => 0
];
$result_dep_eq = mysqli_query($conn, "SELECT * FROM configuracoes_depreciacao LIMIT 1");
if ($result_dep_eq && mysqli_num_rows($result_dep_eq) > 0) {
    $dep_config_eq = mysqli_fetch_assoc($result_dep_eq);
}
$doacao_global = intval($dep_config_eq['elegivel_doacao']);
$tempo_min_doacao_meses_eq = (intval($dep_config_eq['tempo_doacao_anos']) * 12) + intval($dep_config_eq['tempo_doacao_meses']);
$taxa_dep_eq = floatval($dep_config_eq['taxa_depreciacao']);
$periodo_total_meses_eq = (intval($dep_config_eq['periodo_anos']) * 12) + intval($dep_config_eq['periodo_meses']);

// Buscar elegibilidade por categoria
$cat_doacao_map = [];
$result_cat_eq = mysqli_query($conn, "SELECT categoria, elegivel_doacao FROM categoria_doacao");
if ($result_cat_eq) {
    while ($r = mysqli_fetch_assoc($result_cat_eq)) {
        $cat_doacao_map[$r['categoria']] = intval($r['elegivel_doacao']);
    }
}
?>



    <table class="table my-0" id="dataTable">
        <thead>
            <tr>
                <th>Categoria</th>
                <th>Fabricante</th>
                <th>Modelo</th>
                <th>Tag</th>
                <th>HostName</th>
                <th>Valor</th>
                <th>MAC Address</th>
                <th>Centro de Custo</th>
                <th>Usuário</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $assigned_to = $row['assigned_to']; // Verifica se o ativo tem um usuário atribuído

        // Calcular depreciação baseada nas configurações
        $data_ativacao = new DateTime($row['dataAtivacao']);
        $data_atual = new DateTime();
        $diff = $data_ativacao->diff($data_atual);
        $dias_ativos = $diff->days;

        $valor_original = floatval($row['valor']);
        if ($periodo_total_meses_eq > 0 && $valor_original > 0) {
            $meses_ativos = ($diff->y * 12) + $diff->m;
            $periodos_completos = floor($meses_ativos / $periodo_total_meses_eq);
            $depreciacao = min($valor_original, $valor_original * ($taxa_dep_eq / 100) * $periodos_completos);
            $valor_atual = max(0, $valor_original - $depreciacao);
        }
        else {
            $depreciacao = 0;
            $valor_atual = $valor_original;
        }

        // Elegibilidade para doação baseada nas configurações
        $meses_desde_cadastro = ($diff->y * 12) + $diff->m;
        $cat_do_ativo = $row['categoria'];
        $cat_elegivel = isset($cat_doacao_map[$cat_do_ativo]) ? $cat_doacao_map[$cat_do_ativo] : 1;

        if (!$doacao_global) {
            $elegivel_doacao = false;
            $doacao_msg = 'Doação Desativada';
            $doacao_title = 'A doação está desativada globalmente nas configurações.';
        }
        elseif (!$cat_elegivel) {
            $elegivel_doacao = false;
            $doacao_msg = 'Categoria não elegível';
            $doacao_title = 'A categoria "' . htmlspecialchars($cat_do_ativo) . '" não está habilitada para doação.';
        }
        elseif ($meses_desde_cadastro >= $tempo_min_doacao_meses_eq) {
            $elegivel_doacao = true;
            $doacao_msg = 'Doar Ativo';
            $doacao_title = '';
        }
        else {
            $elegivel_doacao = false;
            $restante = $tempo_min_doacao_meses_eq - $meses_desde_cadastro;
            $a = floor($restante / 12);
            $m = $restante % 12;
            $t = '';
            if ($a > 0)
                $t .= $a . ' ano(s)';
            if ($a > 0 && $m > 0)
                $t .= ' e ';
            if ($m > 0)
                $t .= $m . ' mês(es)';
            if (empty($t))
                $t = 'menos de 1 mês';
            $doacao_msg = 'Bloqueado (Carência: ' . $t . ')';
            $doacao_title = 'Ativo precisa de mais ' . $t . ' para ser elegível para doação.';
        }
?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                        <td><?php echo htmlspecialchars($row['fabricante']); ?></td>
                        <td><?php echo htmlspecialchars($row['modelo']); ?></td>
                        <td><?php echo htmlspecialchars($row['tag']); ?></td>
                        <td><?php echo htmlspecialchars($row['hostName']); ?></td>
                        <td><?php echo "R$ " . number_format($valor_atual, 2, ',', '.') . " <small class='text-muted'>(Orig: " . number_format($valor_original, 2, ',', '.') . ")</small>"; ?></td>
                        <td><?php echo htmlspecialchars($row['macAdress']); ?></td>
                        <td><?php echo htmlspecialchars($row['centroDeCusto']); ?></td>

                        <td>
    <?php
        if ($assigned_to) {
            // Buscar os detalhes do usuário atribuído diretamente
            $sql_user = "SELECT nome, sobrenome, usuarioAD, email, centroDeCusto FROM usuarios WHERE id_usuarios = '$assigned_to'";
            $result_user = mysqli_query($conn, $sql_user);
            if ($result_user && mysqli_num_rows($result_user) > 0) {
                $user = mysqli_fetch_assoc($result_user);
                echo "<a href='#' onclick='showUserModal($assigned_to, \"" . addslashes($user['nome']) . "\", \"" . addslashes($user['sobrenome']) . "\", \"" . addslashes($user['usuarioAD']) . "\", \"" . addslashes($user['email']) . "\", \"" . addslashes($user['centroDeCusto']) . "\", " . $row['id_asset'] . ", " . ($elegivel_doacao ? 'true' : 'false') . ", \"" . addslashes($doacao_msg) . "\", \"" . addslashes($doacao_title) . "\")'>" . htmlspecialchars($user['nome']) . "</a>";
            }
        }
        else {
            echo "Não Atribuído";
        }
?>
</td>
<td> <!-- Exibe o status com cor de fundo condicional --> <span class="badge 
<?php echo($row['status'] === 'Ativo') ? 'badge-success' : 'badge-danger'; ?>"> 
<?php echo htmlspecialchars(ucfirst($row['status']));
?> </span> </td>
<td>
    <!-- Disposição dos botões lado a lado com o mesmo tamanho -->
    <div class="d-flex align-items-center">
        <!-- Botão de Atribuir ou Desatribuir (tamanho fixo de 130px) -->
        <?php
        if ($assigned_to) {
            echo "<button class='btn btn-dark btn-tamanho-fixo mr-2' 
            onclick='unassignUser(" . $row['id_asset'] . ")'> 
            Desatribuir <i class='fas fa-address-card'></i> </button>";
        }
        else {
            echo "<button class='btn btn-info btn-tamanho-fixo mr-2' 
            onclick='openAssignModal(" . $row['id_asset'] . ")'> 
            Atribuir <i class='fas fa-address-card'></i> </button>";
        }
?>

        <!-- Botão de Editar (tamanho fixo de 130px, mas como botão pequeno, será mais estreito) -->
        <a class='btn btn-warning btn-edit mr-2' href='editar_ativo.php?id=<?php echo $row['id_asset']; ?>'>
            <i class='fas fa-edit'></i>
        </a>

        <!-- Botão de Histórico -->
        <a class='btn btn-info btn-edit mr-2' href='detalhes_do_equipamento.php?id=<?php echo $row['id_asset']; ?>' title="Histórico">
            <i class='fas fa-history'></i>
        </a>

        <!-- Botão de Ativar/Desativar (tamanho fixo de 130px) -->
        
    </div>
</td>

                            </a>
                        </td>
                    </tr>
            <?php
    }
}
else {
    echo "<tr><td colspan='11'>Nenhum dado encontrado.</td></tr>";
}
?>
        </tbody>
    </table>

<div class="pagination justify-content-start">
    <nav>
    <ul class="pagination">
    <?php
// Previous Page Link
$search_param = !empty($search) ? "&search=" . urlencode($search) : "";
if ($current_page > 1) {
    echo "<li class='page-item'><a class='btn btn-dark' href='?page=" . ($current_page - 1) . "$search_param'>« Anterior</a></li>";
}

// Page Links
for ($page = 1; $page <= $total_pages; $page++) {
    if ($page == $current_page) {
        echo "<li class='page-item active'><a class='btn btn-dark' href='?page=$page$search_param'>$page</a></li>"; // Current page
    }
    else {
        echo "<li class='page-item'><a class='btn btn-dark' href='?page=$page$search_param'>$page</a></li>"; // Other pages
    }
}

// Next Page Link
if ($current_page < $total_pages) {
    echo "<li class='page-item'><a class='btn btn-dark' href='?page=" . ($current_page + 1) . "'>Próximo »</a></li>";
}
?>
</ul>
    </nav>
</div>


<?php
mysqli_close($conn);
?>


<!-- Modal para exibir os dados do usuário -->
<div id="userModal" class="modal" tabindex="-1" role="dialog" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Usuário</h5>
                <button type="button" class="close" onclick="closeUserModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="userDetails">
                    <!-- Aqui os detalhes do usuário serão carregados -->
                </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-danger" id="sellAssetButton" data-id="" onclick="sellAsset()">Doar Ativo</button>


                <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para atribuir ativo -->
<div id="assignModal" class="modal" tabindex="-1" role="dialog" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atribuir Ativo ao Usuário</h5>
                <button type="button" class="close" onclick="closeAssignModal()">&times;</button>
            </div>
            <div class="modal-body">
                <input type="text" id="userSearch" class="form-control" placeholder="Pesquisar usuário..." oninput="searchUsers()">
                <ul id="userList" class="list-group mt-2"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAssignModal()">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
function showUserModal(userId, nome, sobrenome, usuarioAD, email, centroDeCusto, assetId, isEligible, doacaoMsg, doacaoTitle) {
    const userDetails = `
        <p><strong>Nome:</strong> ${nome} ${sobrenome}</p>
        <p><strong>Usuário AD:</strong> ${usuarioAD}</p>
        <p><strong>Email:</strong> ${email}</p>
        <p><strong>Centro de Custo:</strong> ${centroDeCusto}</p>
    `;
    document.getElementById('userDetails').innerHTML = userDetails;

    // Configura o ID do ativo no botão
    const sellButton = document.getElementById('sellAssetButton');
    sellButton.setAttribute('data-id', assetId);

    // Habilitar/Desabilitar botão baseada na elegibilidade
    if (isEligible) {
        sellButton.disabled = false;
        sellButton.className = 'btn btn-success';
        sellButton.innerHTML = doacaoMsg || 'Doar Ativo';
        sellButton.title = '';
    } else {
        sellButton.disabled = true;
        sellButton.className = 'btn btn-secondary';
        sellButton.innerHTML = doacaoMsg || 'Doação Bloqueada';
        sellButton.title = doacaoTitle || 'Este ativo não pode ser doado no momento.';
    }

    // Exibe o modal
    document.getElementById('userModal').style.display = 'block';
}


// Função para fechar o modal
function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

// Função para desatribuir o usuário (não vende o ativo, apenas desatribui)
function unassignUser(assetId) {
    if (confirm('Tem certeza que deseja desatribuir este usuário do ativo?')) {
        fetch('unassign_asset.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_asset: assetId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Usuário desatribuído com sucesso!');
                location.reload(); // Recarrega a página para refletir as mudanças
            } else {
                alert('Erro ao desatribuir o usuário: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro ao tentar desatribuir o usuário:', error);
        });
    }
}

// Função para doar o ativo (transferir para a tabela "venda" - agora doações)
function sellAsset() {
    const sellButton = document.getElementById('sellAssetButton');
    const assetId = sellButton.getAttribute('data-id');

    // (Opcional) Poderíamos adicionar uma verificação de data aqui também no frontend, 
    // mas a validação principal deve ser no backend.

    if (confirm('Tem certeza que deseja doar este ativo?')) {
        fetch('doar_ativo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_asset: assetId }) // Envia o id do ativo como JSON
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Ativo doado com sucesso!');
                location.reload(); // Recarrega a página para refletir as mudanças
            } else {
                alert('Erro ao doar o ativo: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro ao tentar doar o ativo:', error);
        });
    }
}





// Função para abrir o modal de atribuição
function openAssignModal(assetId) {
    currentAssetId = assetId;
    document.getElementById('assignModal').style.display = 'block';
}

// Função para fechar o modal de atribuição
function closeAssignModal() {
    document.getElementById('assignModal').style.display = 'none';
    document.getElementById('userList').innerHTML = '';
    document.getElementById('userSearch').value = '';
}
</script>

                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="assignModal" class="modal" tabindex="-1" role="dialog" style="display: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atribuir Ativo ao Usuário</h5>
                <button type="button" class="close" onclick="closeAssignModal()">&times;</button>
            </div>
            <div class="modal-body">
                <input type="text" id="userSearch" class="form-control" placeholder="Pesquisar usuário..." oninput="searchUsers()">
                <ul id="userList" class="list-group mt-2"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="window.location.reload()">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentAssetId = null;

// Função para abrir o modal e passar o id do ativo
function openAssignModal(assetId) {
    currentAssetId = assetId;
    document.getElementById('assignModal').style.display = 'block';
}

// Função para fechar o modal
function closeAssignModal() {
    document.getElementById('assignModal').style.display = 'none';
    document.getElementById('userList').innerHTML = '';
    document.getElementById('userSearch').value = '';
    window.location.reload();
}

// Função para buscar usuários enquanto digita
function searchUsers() {
    const query = document.getElementById('userSearch').value;

    fetch(`search_users.php?query=${query}`)
        .then(response => response.json())
        .then(users => {
            console.log(users);  // Verifique a resposta da busca
            const userList = document.getElementById('userList');
            userList.innerHTML = '';  // Limpa a lista de usuários

            if (users.length === 0) {
                userList.innerHTML = '<li class="list-group-item">Nenhum usuário encontrado</li>';
            } else {
                users.forEach(user => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item';
                    li.textContent = user.name;
                    li.onclick = () => assignUser(user.id, user.name);  // Atribuindo evento de clique
                    userList.appendChild(li);
                });
            }
        })
        .catch(error => {
            console.error('Erro na busca de usuários:', error);
        });
}

// Função para atribuir o usuário ao ativo
function assignUser(userId, userName) {
    // Exibe um alerta com o nome do usuário clicado
    alert(`Atribuindo ativo ao usuário: ${userName}`);

    // Faz a requisição para atribuir o ativo ao usuário
    fetch('assign_asset.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_asset: currentAssetId, assigned_to: userId }) // Passando o id do ativo e o id do usuário
    })
    .then(response => response.json()) // Converte a resposta para JSON
    .then(data => {
        if (data.success) {
            // Se a atribuição foi bem-sucedida, exibe um alerta de sucesso
            alert('Ativo atribuído com sucesso!');
            closeAssignModal(); // Fecha o modal de atribuição
            window.location.reload(); // Recarrega a página para refletir as mudanças
        } else {
            // Se houver algum erro na atribuição, exibe um alerta de erro
            alert('Erro ao atribuir ativo.');
        }
    })
    .catch(error => {
        // Se houver algum erro na requisição, exibe um erro no console
        console.error('Erro ao tentar atribuir o ativo:', error);
    });
}


function toggleStatus(id, newStatus, button) {
    fetch('alterar_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id_asset: id, status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar a classe e o texto do botão
            button.className = (newStatus === 'ativo') ? 'btn btn-danger' : 'btn btn-success';
            button.innerHTML = (newStatus === 'ativo') ? 'Inativar <i class="fas fa-power-off"></i>' : 'Ativar <i class="fas fa-power-off"></i>';
        } else {
            alert('Erro ao atualizar o status');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
    });
}
</script>



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
</body>

</html>
