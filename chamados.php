<?php
include 'auth.php';
include 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

$results_per_page = 10;

// Filtro de Status
$filtro_status = isset($_GET['filtro_status']) ? $_GET['filtro_status'] : 'aberto';
$where_clause = "";

switch ($filtro_status) {
    case 'aberto':
        $where_clause = "WHERE c.status = 'Aberto'";
        break;
    case 'em_andamento':
        $where_clause = "WHERE c.status = 'Em Andamento'";
        break;
    case 'pendente':
        $where_clause = "WHERE c.status = 'Pendente'";
        break;
    case 'finalizados':
        $where_clause = "WHERE c.status IN ('Resolvido', 'Fechado', 'Cancelado')";
        break;
    case 'todos':
        $where_clause = ""; // Sem filtro
        break;
    default:
        $where_clause = "WHERE c.status = 'Aberto'"; // Default
        $filtro_status = 'aberto';
        break;
}

// Search Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
if (!empty($search)) {
    if (empty($where_clause)) {
        $where_clause = "WHERE (c.titulo LIKE '%$search%' OR c.id LIKE '%$search%')";
    } else {
        $where_clause .= " AND (c.titulo LIKE '%$search%' OR c.id LIKE '%$search%')";
    }
}

$sql_count = "SELECT COUNT(*) AS total FROM chamados c $where_clause";
$result_count = mysqli_query($conn, $sql_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_results = $row_count['total'];
$total_pages = ceil($total_results / $results_per_page);
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start_from = ($current_page - 1) * $results_per_page;

$sql = "SELECT c.*, u.nome, u.sobrenome, r.nome AS resp_nome, r.sobrenome AS resp_sobrenome 
        FROM chamados c 
        LEFT JOIN usuarios u ON c.usuario_id = u.id_usuarios 
        LEFT JOIN usuarios r ON c.responsavel_id = r.id_usuarios
        $where_clause
        ORDER BY 
            CASE WHEN c.status = 'Aberto' THEN 0 ELSE 1 END ASC,
            c.data_abertura DESC
        LIMIT $start_from, $results_per_page";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<style>
    .btn-tamanho-fixo {
        width: 130px;
    }

    .btn-edit {
        width: 50px;
        /* Metade da largura */
    }

    .btn-medium {
        width: 100px;
        /* Metade da largura */
    }

    <style>.badge-success {
        background-color: #28a745 !important;
        /* Verde para ativo */
        color: #fff !important;
        /* Texto branco */
    }

    .badge-danger {
        background-color: #dc3545 !important;
        /* Vermelho para inativo */
        color: #fff !important;
        /* Texto branco */
    }

    .badge-warning {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
</style>

</style>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Chamados - Asset Mgt</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=f3d9abe8d5aa7831c01bfaa2a1563712">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=41e93b37bc495fd67938799bb3a6adaf">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="/assets/css/Simple-footer-by-krissy.css?h=73316da5ae5ad6b51632cd2e5413f263">
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0"><a
                    class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#">
                    <div class="sidebar-brand-icon rotate-n-15"><svg xmlns="http://www.w3.org/2000/svg" width="1em"
                            height="1em" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icon-tabler-layout-distribute-horizontal"
                            style="width: 30px;height: 30px;">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <line x1="4" y1="4" x2="20" y2="4"></line>
                            <line x1="4" y1="20" x2="20" y2="20"></line>
                            <rect x="6" y="9" width="12" height="6" rx="2"></rect>
                        </svg></div>

                    <div class="sidebar-brand-text mx-3"><span>ASSET MGT</span></div>
                </a>
                <hr class="sidebar-divider my-0">
                <ul class="navbar-nav text-light" id="accordionSidebar">
                    <li class="nav-item"><a class="nav-link" href="/index.php"><i
                                class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/inicio.php"><i class="fas fa-home"></i><span>
                                Início</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/usuarios.php"><i class="fas fa-user-alt"></i><span>
                                Usuários</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/centro_de_custo.php"><i
                                class="fas fa-file-invoice-dollar"></i><span> Centro de Custo</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/fornecedores.php"><i
                                class="fas fa-hands-helping"></i><span> Fornecedores</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/equipamentos.php"><i class="fas fa-boxes"></i><span>
                                Ativos</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/licencas.php"><i class="fas fa-key"></i><span>
                                Licenças</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/relatorios.php"><i class="fas fa-scroll"></i><span>
                                Relatórios</span></a></li>
                    <li class="nav-item"><a class="nav-link active" href="/chamados.php"><i
                                class="fas fa-headset"></i><span> Chamados</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/suporte.php"><i class="fas fa-user-cog"></i><span>
                                Suporte</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/agent.php"><i class="fas fa-robot"></i><span> IA
                                Agent</span></a></li>
                </ul>
                <div class="text-center d-none d-md-inline"><button class="btn rounded-circle border-0"
                        id="sidebarToggle" type="button"></button></div>

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
                            <!-- Navbar items (User profile, alerts, etc.) kept same as other pages -->
                            <li class="nav-item dropdown no-arrow">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link"
                                        aria-expanded="false" data-toggle="dropdown" href="#"><span
                                            class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span><img
                                            class="border rounded-circle img-profile"
                                            src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/Captura%20de%20Tela%202021-08-04%20às%2012.25.13.png?h=fcfb924f0ac1ab5f595f029bf526e62d'; ?>"></a>
                                    <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in"><a
                                            class="dropdown-item" href="#"><i
                                                class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a><a
                                            class="dropdown-item" href="configuracoes.php"><i
                                                class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a><a
                                            class="dropdown-item" href="#"><i
                                                class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>Desativar
                                            conta</a>
                                        <div class="dropdown-divider"></div><a class="dropdown-item" href="login.php"><i
                                                class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="container-fluid">
                    <h3 class="text-dark mb-4">Chamados</h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-xl-3 text-nowrap">
                                    <div id="dataTable_length" class="dataTables_length" aria-controls="dataTable"><a
                                            class="btn btn-success btn-block active text-white pulse animated btn-user"
                                            role="button"
                                            style="background: rgb(44,64,74);border-radius: 10px;border-width: 0px;height: 50px;margin-top: 0px;padding: 30px, 30px;margin-bottom: 0px;padding-top: 13px;"
                                            href="/cadastro_de_chamados.php">Novo Chamado</a></div>
                                </div>
                                <div class="col-md-6 col-xl-5">
                                    <form method="GET" class="form-inline" style="margin-top: 0px;">
                                        <label class="mr-2 font-weight-bold">Filtrar por:</label>
                                        <select name="filtro_status" class="form-control mr-2"
                                            onchange="this.form.submit()">
                                            <option value="aberto" <?php echo ($filtro_status == 'aberto') ? 'selected' : ''; ?>>Abertos</option>
                                            <option value="em_andamento" <?php echo ($filtro_status == 'em_andamento') ? 'selected' : ''; ?>>Em Andamento</option>
                                            <option value="pendente" <?php echo ($filtro_status == 'pendente') ? 'selected' : ''; ?>>Pendentes</option>
                                            <option value="finalizados" <?php echo ($filtro_status == 'finalizados') ? 'selected' : ''; ?>>Finalizados</option>
                                            <option value="todos" <?php echo ($filtro_status == 'todos') ? 'selected' : ''; ?>>Todos</option>
                                        </select>
                                        <input type="search" name="search" class="form-control form-control-sm"
                                            placeholder="Buscar..."
                                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                    </form>
                                </div>
                            </div>
                            <div class="table-responsive table mt-2" id="dataTable" role="grid"
                                aria-describedby="dataTable_info">
                                <table class="table my-0" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Título</th>
                                            <th>Categoria</th>
                                            <th>Prioridade</th>
                                            <th>Data Abertura</th>
                                            <th>Solicitante</th>
                                            <th>Responsável</th>
                                            <th>Status</th>
                                            <th>SLA STATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (mysqli_num_rows($result) > 0) {
                                            // 1. Busca as configurações de SLA ANTES de iniciar o loop dos chamados
                                            $sla_configs = [];
                                            // Alterado para buscar a coluna correta de MINUTOS
                                            $res_config = mysqli_query($conn, "SELECT categoria, tempo_sla_minutos FROM configuracoes_sla");

                                            if ($res_config) {
                                                while ($row_config = mysqli_fetch_assoc($res_config)) {
                                                    $sla_configs[$row_config['categoria']] = $row_config['tempo_sla_minutos'];
                                                }
                                            }

                                            // Valores padrão em minutos caso não exista no banco (24h, 48h, 72h)
                                            $defaults = ['Incidente' => 1440, 'Mudança' => 2880, 'Requisição' => 4320];

                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $categoria = $row['categoria'];

                                                // 2. CORREÇÃO DO ERRO: Busca o valor em minutos do array ou usa o padrão
                                                // Isso elimina o "Undefined array key" na linha 220
                                                $sla_total_minutos = $sla_configs[$categoria] ?? ($defaults[$categoria] ?? 1440);

                                                // Ajuste de SLA baseado na Prioridade
                                                $prioridade = isset($row['prioridade']) ? $row['prioridade'] : 'Média';

                                                if ($prioridade == 'Alta') {
                                                    $sla_total_minutos = $sla_total_minutos * 0.3; // 30% do tempo
                                                } elseif ($prioridade == 'Média') {
                                                    $sla_total_minutos = $sla_total_minutos * 0.7; // 70% do tempo
                                                }
                                                // Baixa = 100% (padrão)
                                        
                                                $data_abertura = new DateTime($row['data_abertura']);
                                                $agora = new DateTime();
                                                $intervalo = $data_abertura->diff($agora);

                                                // Calcula minutos decorridos de forma precisa
                                                $minutos_decorridos = ($intervalo->days * 24 * 60) + ($intervalo->h * 60) + $intervalo->i;

                                                // 3. Cálculo da porcentagem do SLA
                                                if ($sla_total_minutos > 0) {
                                                    $sla_percentage = min(100, ($minutos_decorridos / $sla_total_minutos) * 100);
                                                } else {
                                                    $sla_percentage = 100; // Se SLA for 0, considera estourado se já passou algum tempo
                                                }

                                                $sla_status_text = '';
                                                $progress_bar_class = '';
                                                $sla_status_html = '';

                                                if ($row['status'] == 'Aberto' || $row['status'] == 'Em Andamento') {
                                                    if ($minutos_decorridos >= $sla_total_minutos) {
                                                        $sla_status_text = 'Vencido';
                                                        $progress_bar_class = 'bg-danger';
                                                    } elseif ($minutos_decorridos >= ($sla_total_minutos * 0.8)) { // > 80%
                                                        $sla_status_text = 'Atenção';
                                                        $progress_bar_class = 'bg-warning';
                                                    } else {
                                                        $sla_status_text = 'No Prazo';
                                                        $progress_bar_class = 'bg-success';
                                                    }

                                                    // Formatação do tempo decorrido para exibição
                                                    if ($minutos_decorridos < 60) {
                                                        $tempo_formatado = round($minutos_decorridos) . 'm';
                                                    } elseif ($minutos_decorridos < 1440) {
                                                        $horas = floor($minutos_decorridos / 60);
                                                        $minutos = $minutos_decorridos % 60;
                                                        $tempo_formatado = "{$horas}h {$minutos}m";
                                                    } else {
                                                        $dias = floor($minutos_decorridos / 1440);
                                                        $horas = floor(($minutos_decorridos % 1440) / 60);
                                                        $tempo_formatado = "{$dias}d {$horas}h";
                                                    }

                                                    $sla_status_html = '
                <div class="d-flex flex-column">
                    <span class="small font-weight-bold mb-1" style="font-size: 0.75rem;">' . $sla_status_text . ' (' . $tempo_formatado . ')</span>
                    <div class="progress" style="height: 10px; min-width: 100px;">
                        <div class="progress-bar ' . $progress_bar_class . ' progress-bar-striped progress-bar-animated" role="progressbar" style="width: ' . $sla_percentage . '%" aria-valuenow="' . $sla_percentage . '" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>';
                                                } elseif ($row['status'] == 'Resolvido' || $row['status'] == 'Fechado') {
                                                    $sla_status_html = '<span class="badge badge-success">Concluído</span>';
                                                } elseif ($row['status'] == 'Cancelado') {
                                                    $sla_status_html = '<span class="badge badge-danger">Cancelado</span>';
                                                } else {
                                                    $sla_status_html = '<span class="badge badge-secondary">-</span>';
                                                }

                                                // Definição das cores dos Badges de Status
                                                $status_class = 'badge-secondary';
                                                switch ($row['status']) {
                                                    case 'Aberto':
                                                        $status_class = 'badge-primary';
                                                        break;
                                                    case 'Em Andamento':
                                                        $status_class = 'badge-info';
                                                        break;
                                                    case 'Pendente':
                                                        $status_class = 'badge-warning';
                                                        break;
                                                    case 'Resolvido':
                                                        $status_class = 'badge-success';
                                                        break;
                                                    case 'Cancelado':
                                                        $status_class = 'badge-danger';
                                                        break;
                                                    case 'Fechado':
                                                        $status_class = 'badge-dark';
                                                        break;
                                                }

                                                $solicitante = $row['nome'] ? $row['nome'] . ' ' . $row['sobrenome'] : 'Não identificado';
                                                $responsavel = $row['resp_nome'] ? $row['resp_nome'] . ' ' . $row['resp_sobrenome'] : 'Não Atribuído';
                                                $responsavel_class = $row['resp_nome'] ? 'badge-dark' : 'badge-secondary';

                                                $prioridade_class = 'badge-secondary';
                                                switch ($prioridade) {
                                                    case 'Alta':
                                                        $prioridade_class = 'badge-danger';
                                                        break;
                                                    case 'Média':
                                                        $prioridade_class = 'badge-warning';
                                                        break;
                                                    case 'Baixa':
                                                        $prioridade_class = 'badge-info';
                                                        break;
                                                }

                                                echo "<tr>
                <td>" . htmlspecialchars($row['id']) . "</td>
                <td><a href='editar_chamado.php?id=" . $row['id'] . "' class='font-weight-bold text-primary'>" . htmlspecialchars($row['titulo']) . "</a></td>
                <td>" . htmlspecialchars($row['categoria']) . "</td>
                <td><span class='badge " . $prioridade_class . "'>" . htmlspecialchars($prioridade) . "</span></td>
                <td>" . date('d/m/Y H:i', strtotime($row['data_abertura'])) . "</td>
                <td>" . htmlspecialchars($solicitante) . "</td>
                <td><span class='badge " . $responsavel_class . "'>" . htmlspecialchars($responsavel) . "</span></td>
                <td><span class='badge " . $status_class . "'>" . htmlspecialchars($row['status']) . "</span></td>
                <td style='vertical-align: middle;'>" . $sla_status_html . "</td>
            </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='9' class='text-center'>Nenhum chamado encontrado.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <style>
                                .pagination-custom {
                                    display: flex;
                                    gap: 6px;
                                    list-style: none;
                                    padding: 0;
                                    margin: 16px 0;
                                    flex-wrap: wrap;
                                }

                                .pagination-custom li a {
                                    display: inline-flex;
                                    align-items: center;
                                    justify-content: center;
                                    min-width: 38px;
                                    height: 38px;
                                    padding: 0 12px;
                                    border-radius: 8px;
                                    font-size: 14px;
                                    font-weight: 500;
                                    text-decoration: none;
                                    transition: all 0.2s ease;
                                    border: 1.5px solid rgba(44, 64, 74, 0.25);
                                    color: rgb(44, 64, 74);
                                    background: #fff;
                                }

                                .pagination-custom li a:hover {
                                    background: rgba(44, 64, 74, 0.08);
                                    border-color: rgb(44, 64, 74);
                                    color: rgb(44, 64, 74);
                                    transform: translateY(-1px);
                                    box-shadow: 0 2px 8px rgba(44, 64, 74, 0.15);
                                }

                                .pagination-custom li.active a {
                                    background: rgb(44, 64, 74);
                                    color: #fff;
                                    border-color: rgb(44, 64, 74);
                                    box-shadow: 0 2px 8px rgba(44, 64, 74, 0.3);
                                }

                                .pagination-custom li.active a:hover {
                                    background: rgb(34, 54, 64);
                                }
                            </style>
                            <div class="d-flex justify-content-start mt-3">
                                <ul class="pagination-custom">
                                    <?php
                                    $current_filter = "&filtro_status=" . urlencode($filtro_status);
                                    if ($current_page > 1) {
                                        echo "<li><a href='?page=" . ($current_page - 1) . $current_filter . "'>« Anterior</a></li>";
                                    }
                                    for ($page = 1; $page <= $total_pages; $page++) {
                                        if ($page == $current_page) {
                                            echo "<li class='active'><a href='?page=$page$current_filter'>$page</a></li>";
                                        } else {
                                            echo "<li><a href='?page=$page$current_filter'>$page</a></li>";
                                        }
                                    }
                                    if ($current_page < $total_pages) {
                                        echo "<li><a href='?page=" . ($current_page + 1) . $current_filter . "'>Próximo »</a></li>";
                                    }
                                    ?>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <footer class="bg-white sticky-footer" style="background: rgb(34,40,39);padding: 0;">
                <section class="text-center footer" style="padding: 10px;margin-top: 70px;">
                    <p style="margin-bottom: 0px;font-size: 15px;">DEGB&nbsp;Copyright © 2015-2024<br></p>
                </section>
            </footer>
        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
</body>

</html>