<?php
/**
 * INVENTÁRIO DE ATIVOS: equipamentos.php
 * Listagem principal para gestão, filtragem e acompanhamento de todo o hardware da empresa.
 */
// Inclui os arquivos de autenticação e conexão com o banco de dados
include 'auth.php';
include 'conexao.php';

// Restrição de acesso: Verifica se o usuário tem nível 'Admin' ou 'Suporte' para acessar o inventário
if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    // Redireciona para a página inicial se não tiver permissão
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br"> <!-- Define o idioma da página como português do Brasil -->
<style>
    /* Estilos customizados para botões e elementos da interface */
    .btn-tamanho-fixo {
        width: 130px;
    }

    .btn-edit {
        width: 50px;
        padding: 5px 0;
    }

    /* Estilo premium para o sistema de manutenção (Cor Laranja) */
    .btn-maintenance-system {
        background-color: #ff8c00 !important;
        border-color: #ff8c00 !important;
        color: white !important;
        transition: all 0.3s ease;
    }

    .btn-maintenance-system:hover {
        background-color: #e67e00 !important;
        border-color: #e67e00 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(255, 140, 0, 0.3);
    }

    .badge-maintenance-system {
        background-color: #ff8c00 !important;
        color: #fff !important;
        font-weight: bold;
        padding: 0.5em 0.8em;
    }

    .border-left-maintenance-system {
        border-left: .25rem solid #ff8c00 !important;
    }

    .btn-medium {
        width: 100px;
    }

    .badge-success {
        background-color: #28a745 !important;
        color: #fff !important;
    }

    .badge-danger {
        background-color: #dc3545 !important;
        color: #fff !important;
    }

    .badge-warning {
        background-color: #f6c23e !important;
        color: #fff !important;
    }

    .clickable-row {
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .clickable-row:hover {
        background-color: rgba(0, 0, 0, 0.05) !important;
    }

    .btn-system {
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-system:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Estilos para imagens em miniatura dos ativos */
    .asset-thumbnail {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 10px;
        border: 1px solid #ddd;
    }

    .asset-placeholder {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background-color: #f8f9fc;
        border: 1px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        color: #b7b9cc;
    }
</style>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Inventário de Ativos - Gestão</title>
    <!-- Favicon e Bibliotecas de CSS Externas -->
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=f3d9abe8d5aa7831c01bfaa2a1563712">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=41e93b37bc495fd67938799bb3a6adaf">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.js/3.5.2/animate.min.css">
    <link rel="stylesheet" href="/assets/css/Simple-footer-by-krissy.css?h=73316da5ae5ad6b51632cd2e5413f263">
    <?php include 'sidebar_style.php'; ?>
    <style>
        .card.shadow {
            transition: all 0.3s ease-in-out;
            cursor: pointer;
        }

        .card.shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15) !important;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Barra Lateral (Sidebar) -->
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion p-0">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include 'sidebar_brand.php'; ?>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <!-- Barra Superior (Topbar) -->
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top"
                    style="margin: 5px 23px;">
                    <div class="container-fluid"><button class="btn btn-link d-md-none rounded-circle mr-3"
                            id="sidebarToggleTop-1" type="button"><i class="fas fa-bars"></i></button>
                        <!-- Busca Global -->
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
                        <!-- Menu do Perfil do Usuário -->
                        <ul class="navbar-nav flex-nowrap ml-auto">
                            <li class="nav-item dropdown no-arrow">
                                <a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown"
                                    href="#">
                                    <span
                                        class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span>
                                    <img class="border rounded-circle img-profile"
                                        src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/avatar5.jpeg'; ?>">
                                </a>
                                <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in">
                                    <a class="dropdown-item" href="profile.php"><i
                                            class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a>
                                    <a class="dropdown-item" href="configuracoes.php"><i
                                            class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a>
                                    <a class="dropdown-item" href="equipamentos.php?status=Manutencao"><i
                                            class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>Ativos em
                                        Manutenção</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="logout.php"><i
                                            class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>

                <div class="container-fluid" style="padding-left: 23px; padding-right: 23px;">
                    <!-- Seção de Resumo: Cards Informativos -->
                    <div class="row">
                        <?php
                        // Consultas SQL para buscar estatísticas de ativos (não em manutenção)
                        $res_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM ativos WHERE id_asset NOT IN (SELECT id_asset FROM manutencao WHERE status_manutencao = 'Em Manutenção')");
                        $total_ativos = mysqli_fetch_assoc($res_total)['total'];

                        $res_disp = mysqli_query($conn, "SELECT COUNT(*) as total FROM ativos WHERE (assigned_to IS NULL OR assigned_to = 0) AND id_asset NOT IN (SELECT id_asset FROM manutencao WHERE status_manutencao = 'Em Manutenção')");
                        $total_disp = mysqli_fetch_assoc($res_disp)['total'];

                        $res_uso = mysqli_query($conn, "SELECT COUNT(*) as total FROM ativos WHERE (assigned_to IS NOT NULL AND assigned_to != 0) AND id_asset NOT IN (SELECT id_asset FROM manutencao WHERE status_manutencao = 'Em Manutenção')");
                        $total_uso = mysqli_fetch_assoc($res_uso)['total'];

                        // Consulta específica para ativos em manutenção
                        $res_manut_card = mysqli_query($conn, "SELECT COUNT(*) as total FROM manutencao WHERE status_manutencao = 'Em Manutenção'");
                        $total_manut = mysqli_fetch_assoc($res_manut_card)['total'];
                        ?>
                        <!-- Exibição dos cards de resumo -->
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-left-primary py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-primary font-weight-bold text-xs mb-1">
                                                <span>Total de Ativos</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_ativos; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-boxes fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-left-success py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-success font-weight-bold text-xs mb-1">
                                                <span>Disponíveis</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_disp; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-left-info py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-info font-weight-bold text-xs mb-1"><span>Em
                                                    Uso</span></div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_uso; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-user-check fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-left-maintenance-system py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-warning font-weight-bold text-xs mb-1"
                                                style="color: #ff8c00 !important;">
                                                <span>Manutenção</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_manut; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-tools fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-dark mb-4">
                        <?php
                        // Define o título da página com base no filtro de status
                        echo (isset($_GET['status']) && $_GET['status'] === 'Manutencao') ? 'Ativos em Manutenção' : 'Ativos';
                        ?>
                    </h3>

                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-xl-2 text-nowrap">
                                    <div class="dataTables_length">
                                        <?php
                                        // Exibe o botão de cadastro apenas se não estivermos na visualização de manutenção
                                        if (!isset($_GET['status']) || ($_GET['status'] !== 'Manutencao' && $_GET['status'] !== 'Manutenção')):
                                            ?>
                                            <a class="btn btn-success btn-block active text-white pulse animated btn-user"
                                                role="button"
                                                style="background: rgb(44,64,74);border-radius: 10px;border-width: 0px;height: 50px;padding-top: 13px;"
                                                href="/cadastro_de_equipamentos.php">Cadastrar Novo</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-10">
                                    <div class="text-md-right dataTables_filter">
                                        <!-- Formulário de busca simples na tabela -->
                                        <form method="GET" action="" class="form-inline justify-content-end">
                                            <div class="form-group mr-2">
                                                <input type="search" name="search"
                                                    class="form-control form-control-sm premium-filter"
                                                    placeholder="Buscar..." onsearch="this.form.submit()"
                                                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                                    aria-label="Buscar Ativos">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive table mt-2">
                                <?php
                                // Configurações de filtros e busca
                                $results_per_page = 10;
                                $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                                $status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
                                $maintenance_join = "";

                                $where_clauses = [];
                                // Filtro por termo de busca (Modelo, Tag ou HostName)
                                if (!empty($search)) {
                                    $where_clauses[] = "(a.modelo LIKE '%$search%' OR a.tag LIKE '%$search%' OR a.hostName LIKE '%$search%')";
                                }

                                // Lógica de filtro para ativos em manutenção
                                if ($status_filter === 'Manutenção' || $status_filter === 'Manutencao') {
                                    $maintenance_join = " JOIN manutencao m ON a.id_asset = m.id_asset ";
                                    $where_clauses[] = "m.status_manutencao = 'Em Manutenção'";
                                } else {
                                    // Oculta ativos que estão em manutenção na visualização geral
                                    $where_clauses[] = "a.id_asset NOT IN (SELECT id_asset FROM manutencao WHERE status_manutencao = 'Em Manutenção')";
                                    if (!empty($status_filter)) {
                                        $where_clauses[] = "a.status LIKE '%$status_filter%'";
                                    }
                                }

                                // Constrói a cláusula SQL WHERE
                                $where_clause = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

                                // Calcula o total de registros para a paginação
                                $sql_count = "SELECT COUNT(*) AS total FROM ativos a $maintenance_join $where_clause";
                                $res_count = mysqli_query($conn, $sql_count);
                                $total_results = mysqli_fetch_assoc($res_count)['total'];
                                $total_pages = ceil($total_results / $results_per_page);
                                $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                                $start_from = ($current_page - 1) * $results_per_page;

                                // Ordenação: Mais recentes primeiro
                                $order_by = "a.id_asset DESC";
                                if ($status_filter === 'Manutenção' || $status_filter === 'Manutencao') {
                                    $order_by = "m.id_manutencao DESC";
                                }

                                // Busca os dados dos ativos com JOIN em usuários e status de manutenção
                                $sql = "SELECT a.*, u.nome AS user_nome, m_info.id_manutencao as em_manutencao, m_info.observacoes as manutencao_motivo 
                                        FROM ativos a 
                                        LEFT JOIN usuarios u ON a.assigned_to = u.id_usuarios 
                                        LEFT JOIN manutencao m_info ON a.id_asset = m_info.id_asset AND m_info.status_manutencao = 'Em Manutenção'
                                        $maintenance_join $where_clause 
                                        ORDER BY $order_by LIMIT $start_from, $results_per_page";
                                $result = mysqli_query($conn, $sql);
                                ?>

                                <table class="table my-0" id="assetsDataTable">
                                    <thead>
                                        <tr>
                                            <th scope="col">Modelo</th>
                                            <th scope="col">Fabricante</th>
                                            <th scope="col">Categoria</th>
                                            <th scope="col">Tag</th>
                                            <th scope="col">HostName</th>
                                            <th scope="col">Valor Atual</th>
                                            <th scope="col">MAC</th>
                                            <th scope="col">CC</th>
                                            <?php if ($status_filter !== 'Manutencao'): ?>
                                                <th scope="col">Usuário</th>
                                            <?php else: ?>
                                                <th scope="col">Motivo Manut.</th>
                                            <?php endif; ?>
                                            <th scope="col">Status</th>
                                            <th scope="col">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (mysqli_num_rows($result) > 0) {
                                            // Busca configurações globais de depreciação
                                            $dep_config = ['taxa_depreciacao' => 10.00, 'periodo_anos' => 1, 'periodo_meses' => 0];
                                            $res_dep_config = mysqli_query($conn, "SELECT * FROM configuracoes_depreciacao LIMIT 1");
                                            if ($res_dep_config && mysqli_num_rows($res_dep_config) > 0) {
                                                $dep_config = mysqli_fetch_assoc($res_dep_config);
                                            }

                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $assigned_to = $row['assigned_to'];
                                                $valor_original = floatval($row['valor']);

                                                // Cálculo de Depreciação (Lógica baseada no tempo decorrido desde a ativação)
                                                $data_ativacao = new DateTime($row['dataAtivacao']);
                                                $data_atual = new DateTime();
                                                $diff = $data_ativacao->diff($data_atual);

                                                $taxa_pct = floatval($dep_config['taxa_depreciacao']);
                                                $periodo_total_meses = (intval($dep_config['periodo_anos']) * 12) + intval($dep_config['periodo_meses']);

                                                if ($periodo_total_meses > 0 && $valor_original > 0) {
                                                    $meses_ativos = ($diff->y * 12) + $diff->m;
                                                    $periodos_completos = floor($meses_ativos / $periodo_total_meses);
                                                    $depreciacao_total = min($valor_original, $valor_original * ($taxa_pct / 100) * $periodos_completos);
                                                    $valor_atual = max(0, $valor_original - $depreciacao_total);
                                                } else {
                                                    $valor_atual = $valor_original;
                                                }
                                                ?>
                                                <tr class="clickable-row"
                                                    onclick="window.location='detalhes_do_equipamento.php?id=<?php echo $row['id_asset']; ?>'">
                                                    <td class="d-flex align-items-center">
                                                        <?php
                                                        // Exibe miniatura da foto do ativo ou ícone padrão
                                                        $foto = !empty($row['imagem']) ? htmlspecialchars($row['imagem']) : '';
                                                        if ($foto) {
                                                            echo "<img src='$foto' class='asset-thumbnail' alt='Miniatura do Ativo'>";
                                                        } else {
                                                            echo "<div class='asset-placeholder'><i class='fas fa-box'></i></div>";
                                                        }
                                                        echo htmlspecialchars($row['modelo']);
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['fabricante']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                                                    <td>
                                                        <a href="detalhes_do_equipamento.php?id=<?php echo $row['id_asset']; ?>"
                                                            class="font-weight-bold" style="color: #2c404a;"
                                                            onclick="event.stopPropagation();"><?php echo htmlspecialchars($row['tag']); ?></a>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['hostName']); ?></td>
                                                    <td>
                                                        <span class="font-weight-bold" style="color: #2c404a;">
                                                            R$ <?php echo number_format($valor_atual, 2, ',', '.'); ?>
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">(Original: R$
                                                            <?php echo number_format($valor_original, 2, ',', '.'); ?>)</small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['macAdress']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['centroDeCusto']); ?></td>

                                                    <?php if ($status_filter !== 'Manutencao'): ?>
                                                        <td>
                                                            <?php echo ($assigned_to && !empty($row['user_nome'])) ? htmlspecialchars($row['user_nome']) : "Disponível"; ?>
                                                        </td>
                                                    <?php else: ?>
                                                        <td>
                                                            <?php
                                                            // Mostra o motivo da manutenção com tooltip para o texto completo
                                                            $motivo_completo = !empty($row['manutencao_motivo']) ? $row['manutencao_motivo'] : 'Sem observações';
                                                            $motivo_resumo = (mb_strlen($motivo_completo) > 30) ? mb_substr($motivo_completo, 0, 27) . "..." : $motivo_completo;
                                                            ?>
                                                            <span data-toggle="tooltip"
                                                                title="<?php echo htmlspecialchars($motivo_completo); ?>"
                                                                style="cursor: help;">
                                                                <?php echo htmlspecialchars($motivo_resumo); ?>
                                                            </span>
                                                        </td>
                                                    <?php endif; ?>

                                                    <td>
                                                        <span
                                                            class="badge <?php echo ($row['status'] === 'Ativo') ? 'badge-success' : (($row['status'] === 'Manutencao' || $row['status'] === 'Manutenção') ? 'badge-maintenance-system' : 'badge-danger'); ?>">
                                                            <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <!-- Ações rápidas: Atribuir, Editar e Manutenção -->
                                                        <div class="d-flex align-items-center">
                                                            <?php if (!$row['em_manutencao']): ?>
                                                                <?php if ($assigned_to): ?>
                                                                    <button class='btn btn-dark btn-tamanho-fixo mr-2'
                                                                        title="Desatribuir"
                                                                        onclick='event.stopPropagation(); unassignUser(<?php echo $row['id_asset']; ?>)'>Liberar
                                                                        <i class='fas fa-user-minus'></i></button>
                                                                <?php else: ?>
                                                                    <button class='btn btn-info btn-tamanho-fixo mr-2' title="Atribuir"
                                                                        onclick='event.stopPropagation(); openAssignModal(<?php echo $row['id_asset']; ?>)'>Atribuir
                                                                        <i class='fas fa-user-plus'></i></button>
                                                                <?php endif; ?>
                                                            <?php endif; ?>

                                                            <a class='btn btn-warning btn-edit mr-2'
                                                                href='editar_ativo.php?id=<?php echo $row['id_asset']; ?>'
                                                                title="Editar" onclick="event.stopPropagation();"><i
                                                                    class='fas fa-edit'></i></a>

                                                            <?php if ($row['em_manutencao']): ?>
                                                                <button class="btn btn-success btn-edit" title="Liberar Manutenção"
                                                                    onclick="event.stopPropagation(); releaseFromMaintenance(<?php echo $row['id_asset']; ?>)"><i
                                                                        class="fas fa-check-circle"></i></button>
                                                            <?php else: ?>
                                                                <button class="btn btn-maintenance-system btn-edit"
                                                                    title="Enviar para Manutenção"
                                                                    onclick="event.stopPropagation(); sendToMaintenance(<?php echo $row['id_asset']; ?>)"><i
                                                                        class="fas fa-tools"></i></button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='11'>Nenhum ativo encontrado para os filtros aplicados.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginação Customizada -->
                            <div class="d-flex justify-content-start mt-3">
                                <ul class="pagination-custom">
                                    <?php
                                    $params = ($search ? "&search=$search" : "") . ($status_filter ? "&status=$status_filter" : "");
                                    // Botão Anterior
                                    if ($current_page > 1) {
                                        echo "<li><a href='?page=" . ($current_page - 1) . "$params'>« Anterior</a></li>";
                                    }
                                    // Loop das Páginas
                                    for ($p = 1; $p <= $total_pages; $p++) {
                                        if ($p == $current_page) {
                                            echo "<li class='active'><span>$p</span></li>";
                                        } else {
                                            echo "<li><a href='?page=$p$params'>$p</a></li>";
                                        }
                                    }
                                    // Botão Próximo
                                    if ($current_page < $total_pages) {
                                        echo "<li><a href='?page=" . ($current_page + 1) . "$params'>Próximo »</a></li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Atribuir Ativo a um Usuário -->
    <div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="assignModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignModalLabel">Atribuir Ativo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="assignForm">
                    <div class="modal-body">
                        <input type="hidden" id="assign_asset_id" name="id_asset">
                        <div class="form-group">
                            <label for="select_user_assign">Selecionar Usuário</label>
                            <select class="form-control" id="select_user_assign" name="id_usuario" required
                                aria-label="Selecionar usuário para atribuição">
                                <option value="">Selecione...</option>
                                <?php
                                // Busca apenas usuários ativos para atribuição
                                $users_res = mysqli_query($conn, "SELECT id_usuarios, nome FROM usuarios WHERE status = 'Ativo' ORDER BY nome");
                                while ($u = mysqli_fetch_assoc($users_res)) {
                                    echo "<option value='" . $u['id_usuarios'] . "'>" . htmlspecialchars($u['nome']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Atribuir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Enviar Ativo para Manutenção -->
    <div class="modal fade" id="maintenanceModal" tabindex="-1" role="dialog" aria-labelledby="maintenanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="maintenanceModalLabel">Enviar para Manutenção</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="maintenanceForm">
                    <div class="modal-body">
                        <input type="hidden" id="maintenance_asset_id" name="id_asset">
                        <div class="form-group">
                            <label for="maintenance_obs">Observações / Motivo</label>
                            <textarea class="form-control" id="maintenance_obs" name="observacoes" rows="3" required
                                aria-label="Observações sobre a manutenção"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-maintenance-system">Confirmar Manutenção</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>
    <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>

    <!-- Bibliotecas JavaScript (jQuery, Bootstrap, Easing) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.js"></script>
    <script src="/assets/js/theme.js?h=7ad6447a09c485202611e51240369c73"></script>
    <!-- SweetAlert2 para notificações elegantes -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Funções para gerenciar o estado dos ativos via AJAX

        // Abre o modal de atribuição definindo o ID do ativo
        function openAssignModal(id) {
            $('#assign_asset_id').val(id);
            $('#assignModal').modal('show');
        }

        // Envia os dados para atribuir o usuário
        $('#assignForm').on('submit', function (e) {
            e.preventDefault();
            $.post('ajax_ativos.php', {
                action: 'assign',
                id_asset: $('#assign_asset_id').val(),
                id_usuario: $('#select_user_assign').val()
            }, function (res) {
                if (res.success) {
                    location.reload();
                } else {
                    Swal.fire('Erro', res.message || 'Erro ao atribuir', 'error');
                }
            }, 'json');
        });

        // Libera um ativo (remove a atribuição atual)
        function unassignUser(id) {
            Swal.fire({
                title: 'Confirmar Liberação?',
                text: "O ativo voltará a ficar disponível.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, liberar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('ajax_ativos.php', { action: 'unassign', id_asset: id }, function (res) {
                        if (res.success) location.reload();
                    }, 'json');
                }
            });
        }

        // Abre o modal de manutenção definindo o ID do ativo
        function sendToMaintenance(id) {
            $('#maintenance_asset_id').val(id);
            $('#maintenanceModal').modal('show');
        }

        // Envia os dados para colocar o ativo em manutenção
        $('#maintenanceForm').on('submit', function (e) {
            e.preventDefault();
            $.post('ajax_ativos.php', {
                action: 'send_to_maintenance',
                id_asset: $('#maintenance_asset_id').val(),
                observacoes: $('#maintenance_obs').val()
            }, function (res) {
                if (res.success) {
                    location.reload();
                } else {
                    Swal.fire('Erro', res.message || 'Erro ao enviar para manutenção', 'error');
                }
            }, 'json');
        });

        // Finaliza o status de manutenção do ativo
        function releaseFromMaintenance(id) {
            Swal.fire({
                title: 'Finalizar Manutenção?',
                text: "O ativo voltará a ficar disponível e o registro de manutenção será encerrado.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, finalizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('ajax_ativos.php', {
                        action: 'release_maintenance',
                        id_asset: id
                    }, function (res) {
                        if (res.success) {
                            location.reload();
                        } else {
                            Swal.fire('Erro', res.message || 'Erro ao liberar manutenção', 'error');
                        }
                    }, 'json');
                }
            });
        }

        function searchUsers() {
            const q = document.getElementById('userSearch').value;
            fetch(`search_users.php?query=${q}`)
                .then(r => r.json())
                .then(users => {
                    const list = document.getElementById('userList');
                    list.innerHTML = '';
                    users.forEach(u => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action';
                        li.textContent = u.name;
                        li.onclick = () => assignUser(u.id);
                        list.appendChild(li);
                    });
                });
        }

        function assignUser(uid) {
            fetch('assign_asset.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_asset: currentAssetId,
                    assigned_to: uid
                })
            }).then(() => location.reload());
        }
    </script>
</body>

</html>