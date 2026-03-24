<?php
/**
 * INVENTÁRIO DE ATIVOS: equipamentos.php
 * Listagem principal para gestão, filtragem e acompanhamento de todo o hardware da empresa.
 */
// Inclui os arquivos de autenticação e conexão com o banco de dados
include 'auth.php';
include 'conexao.php';

// Captura de sucesso pós-cadastro para exibir modal de etiqueta
$new_asset = null;
if (isset($_GET['new_asset_id']) && isset($_GET['show_tag'])) {
    $new_id = intval($_GET['new_asset_id']);
    $query_new = mysqli_query($conn, "SELECT id_asset, tag, modelo FROM ativos WHERE id_asset = $new_id");
    $new_asset = mysqli_fetch_assoc($query_new);
}

// Restrição de acesso
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

    /* Estilo premium para o sistema de manutenção (Cor Escura) */
    .btn-maintenance-system {
        background-color: #2c404a !important;
        border-color: #2c404a !important;
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
        border-left: .25rem solid #2c404a !important;
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

    .asset-thumbnail { width: 45px; height: 45px; border-radius: 8px; object-fit: cover; margin-right: 12px; border: 1px solid #e3e6f0; }
    .asset-placeholder { width: 45px; height: 45px; border-radius: 8px; background: #f8f9fc; display: flex; align-items: center; justify-content: center; margin-right: 12px; color: #b7b9cc; border: 1px solid #e3e6f0; }

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
    <title><?php echo __('Inventário de Ativos'); ?> - Asset Mgt</title>
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
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include 'sidebar_brand.php'; ?>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <!-- Barra Superior (Topbar) -->
                <?php include 'topbar.php'; ?>

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
                                                <span><?php echo __('Total de Ativos'); ?></span>
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
                                                <span><?php echo __('Disponíveis'); ?></span>
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
                                            <div class="text-uppercase text-info font-weight-bold text-xs mb-1"><span><?php echo __('Em Uso'); ?></span></div>
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
                                                style="color: #2c404a !important;">
                                                <span><?php echo __('Manutenção'); ?></span>
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
                        echo (isset($_GET['status']) && ($_GET['status'] === 'Manutencao' || $_GET['status'] === 'Manutenção')) ? __('Ativos em Manutenção') : __('Ativos');
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
                                            <a class="btn-premium-cadastro pulse animated"
                                                role="button"
                                                href="/cadastro_de_equipamentos.php"><?php echo __('Cadastrar Novo'); ?></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-10">
                                    <div class="text-md-right dataTables_filter">
                                        <?php if (!isset($_GET['status']) || ($_GET['status'] !== 'Manutencao' && $_GET['status'] !== 'Manutenção')): ?>
                                        <!-- Formulário de busca simples na tabela -->
                                        <form method="GET" action="" class="form-inline justify-content-end">
                                            <div class="form-group mr-2">
                                                <input type="search" name="search"
                                                    class="form-control form-control-sm premium-filter"
                                                    placeholder="<?php echo __('Buscar...'); ?>" onsearch="this.form.submit()"
                                                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                                    aria-label="<?php echo __('Buscar Ativos'); ?>">
                                            </div>
                                            <div class="form-group mr-2">
                                                <input type="search" name="sector"
                                                    class="form-control form-control-sm premium-filter"
                                                    placeholder="<?php echo __('Setor...'); ?>" onsearch="this.form.submit()"
                                                    value="<?php echo isset($_GET['sector']) ? htmlspecialchars($_GET['sector']) : ''; ?>"
                                                    aria-label="<?php echo __('Filtrar por Setor'); ?>">
                                            </div>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive table mt-2">
                                <?php
                                // Configurações de filtros e busca
                                $results_per_page = 10;
                                $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                                $sector_filter = isset($_GET['sector']) ? mysqli_real_escape_string($conn, $_GET['sector']) : '';
                                $status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
                                $maintenance_join = "";

                                $where_clauses = [];
                                // Filtro por termo de busca (Modelo, Tag ou HostName)
                                if (!empty($search)) {
                                    $where_clauses[] = "(a.modelo LIKE '%$search%' OR a.tag LIKE '%$search%' OR a.hostName LIKE '%$search%')";
                                }
                                if (!empty($sector_filter)) {
                                    $where_clauses[] = "a.setor LIKE '%$sector_filter%'";
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

                                // Ordenação: Alfabética por Modelo
                                $order_by = "a.modelo ASC";
                                if ($status_filter === 'Manutenção' || $status_filter === 'Manutencao') {
                                    $order_by = "a.modelo ASC";
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
                                            <th scope="col"><?php echo __('Modelo'); ?></th>
                                            <th scope="col"><?php echo __('Fabricante'); ?></th>
                                            <th scope="col"><?php echo __('Categoria'); ?></th>
                                            <th scope="col"><?php echo __('Tag'); ?></th>
                                            <th scope="col"><?php echo __('HostName'); ?></th>
                                            <th scope="col"><?php echo __('Valor Atual'); ?></th>
                                            <th scope="col"><?php echo __('MAC'); ?></th>
                                            <th scope="col"><?php echo __('Setor'); ?></th>
                                            <th scope="col"><?php echo __('CC'); ?></th>
                                            <?php if ($status_filter !== 'Manutencao'): ?>
                                                <th scope="col"><?php echo __('Usuário'); ?></th>
                                            <?php else: ?>
                                                <th scope="col"><?php echo __('Motivo Manut.'); ?></th>
                                            <?php endif; ?>
                                            <th scope="col"><?php echo __('Status'); ?></th>
                                            <th scope="col"><?php echo __('Ações'); ?></th>
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

                                            // Mapa de Centros de Custo para links
                                            $cc_map = [];
                                            $res_cc_map = mysqli_query($conn, "SELECT id_centro_de_custo, nomeSetor FROM centro_de_custo");
                                            while($cc_row = mysqli_fetch_assoc($res_cc_map)) {
                                                $cc_map[$cc_row['nomeSetor']] = $cc_row['id_centro_de_custo'];
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
                                                    onclick="window.location='perfil_ativo.php?id=<?php echo $row['id_asset']; ?>'">
                                                    <td class="d-flex align-items-center">
                                                        <?php
                                                        // Exibe miniatura da foto do ativo ou ícone padrão
                                                        $foto = !empty($row['imagem']) ? htmlspecialchars($row['imagem']) : '';
                                                        if ($foto) {
                                                            echo "<img src='$foto' class='asset-thumbnail' alt='Miniatura do Ativo'>";
                                                        } else {
                                                            echo "<div class='asset-placeholder'><i class='fas fa-box'></i></div>";
                                                        }
                                                        echo "<strong>" . htmlspecialchars($row['modelo']) . "</strong>";
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['fabricante']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                                                    <td>
                                                        <a href="perfil_ativo.php?id=<?php echo $row['id_asset']; ?>"
                                                            class="font-weight-bold" style="color: #2c404a;"
                                                            onclick="event.stopPropagation();"><?php echo htmlspecialchars($row['tag']); ?></a>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['hostName']); ?></td>
                                                    <td>
                                                        <span class="font-weight-bold" style="color: #2c404a;">
                                                            R$ <?php echo number_format($valor_atual, 2, ',', '.'); ?>
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">(<?php echo __('Original:'); ?> R$
                                                            <?php echo number_format($valor_original, 2, ',', '.'); ?>)</small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['macAdress']); ?></td>
                                                    <td><strong><?php echo htmlspecialchars($row['setor'] ?: '-'); ?></strong></td>
                                                    <td>
                                                        <?php 
                                                        $cc_nome = $row['centroDeCusto'];
                                                        if (isset($cc_map[$cc_nome])) {
                                                            echo "<a href='perfil_centro_de_custo.php?id=" . $cc_map[$cc_nome] . "' onclick='event.stopPropagation();' class='font-weight-bold'>" . htmlspecialchars($cc_nome) . "</a>";
                                                        } else {
                                                            echo htmlspecialchars($cc_nome);
                                                        }
                                                        ?>
                                                    </td>

                                                    <?php if ($status_filter !== 'Manutencao'): ?>
                                                        <td>
                                                            <?php echo ($assigned_to && !empty($row['user_nome'])) ? htmlspecialchars($row['user_nome']) : __('Disponível'); ?>
                                                        </td>
                                                    <?php else: ?>
                                                        <td>
                                                            <?php
                                                            // Mostra o motivo da manutenção com tooltip para o texto completo
                                                            $motivo_completo = !empty($row['manutencao_motivo']) ? $row['manutencao_motivo'] : __('Sem observações');
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
                                                            class="status-badge <?php echo ($row['status'] === 'Ativo') ? 'badge-success' : (($row['status'] === 'Manutencao' || $row['status'] === 'Manutenção') ? 'badge-warning' : 'badge-danger'); ?>">
                                                            <?php echo __(ucfirst($row['status'])); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <!-- Ações rápidas: Atribuir, Editar e Manutenção -->
                                                        <div class="d-flex align-items-center">
                                                            <?php if (!$row['em_manutencao']): ?>
                                                                <?php if ($assigned_to): ?>
                                                                    <button class='btn btn-dark btn-tamanho-fixo mr-2'
                                                                        title="<?php echo __('Liberar'); ?>"
                                                                        onclick='event.stopPropagation(); unassignUser(<?php echo $row["id_asset"]; ?>)'><?php echo __('Liberar'); ?>
                                                                        <i class='fas fa-user-minus'></i></button>
                                                                <?php else: ?>
                                                                    <button class='btn btn-info btn-tamanho-fixo mr-2' title="<?php echo __('Atribuir'); ?>"
                                                                        onclick='event.stopPropagation(); openAssignModal(<?php echo $row["id_asset"]; ?>)'><?php echo __('Atribuir'); ?>
                                                                        <i class='fas fa-user-plus'></i></button>
                                                                <?php endif; ?>
                                                            <?php endif; ?>

                                                            <a class='btn btn-warning btn-edit mr-2'
                                                                href='editar_ativo.php?id=<?php echo $row["id_asset"]; ?>'
                                                                title="<?php echo __('Editar'); ?>" onclick="event.stopPropagation();"><i
                                                                    class='fas fa-edit'></i></a>

                                                            <?php if ($row['em_manutencao']): ?>
                                                                <button class="btn btn-success btn-edit" title="<?php echo __('Liberar Manutenção'); ?>"
                                                                    onclick="event.stopPropagation(); releaseFromMaintenance(<?php echo $row["id_asset"]; ?>)"><i
                                                                        class="fas fa-check-circle"></i></button>
                                                            <?php else: ?>
                                                                <button class="btn btn-maintenance-system btn-edit"
                                                                    title="<?php echo __('Enviar para Manutenção'); ?>"
                                                                    onclick="event.stopPropagation(); sendToMaintenance(<?php echo $row["id_asset"]; ?>)"><i
                                                                        class="fas fa-tools"></i></button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='11'>" . __('Nenhum ativo encontrado para os filtros aplicados.') . "</td></tr>";
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
                                        echo "<li><a href='?page=" . ($current_page - 1) . "$params'>« " . __('Anterior') . "</a></li>";
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
                                        echo "<li><a href='?page=" . ($current_page + 1) . "$params'>" . __('Próxima') . " »</a></li>";
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
            <div class="modal-content" style="border-radius: 15px; border: none; overflow: hidden;">
                <div class="modal-header" style="background: #2c404a; color: white;">
                    <h5 class="modal-title" id="assignModalLabel"><?php echo __('Atribuir Ativo'); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="assignForm">
                    <div class="modal-body">
                        <input type="hidden" id="assign_asset_id" name="id_asset">
                        <div class="form-group">
                            <label for="select_user_assign"><?php echo __('Selecionar Usuário'); ?></label>
                            <select class="form-control" id="select_user_assign" name="id_usuario" required
                                aria-label="Selecionar usuário para atribuição">
                                <option value=""><?php echo __('Selecione...'); ?></option>
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
                    <div class="modal-footer" style="background: #f8f9fc;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 10px;"><?php echo __('Cancelar'); ?></button>
                        <button type="submit" class="btn btn-primary" style="background: #2c404a; border: none; border-radius: 10px;"><?php echo __('Atribuir'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Enviar Ativo para Manutenção -->
    <div class="modal fade" id="maintenanceModal" tabindex="-1" role="dialog" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 15px; border: none; shadow: 0 15px 35px rgba(0,0,0,0.2); overflow: hidden;">
                <div class="modal-header" style="background: #2c404a; color: white;">
                    <h5 class="modal-title" id="maintenanceModalLabel"><?php echo __('Solicitar Manutenção'); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="maintenance_asset_id">
                    <p class="text-muted small mb-4"><?php echo __('Selecione o fluxo técnico para registro da manutenção.'); ?></p>
                    
                    <!-- Seleção de Tipo -->
                    <div class="row text-center mb-4">
                        <div class="col-4 px-1">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="typeInsumo" name="maintenanceType" class="custom-control-input" value="Insumo" checked>
                                <label class="custom-control-label font-weight-bold text-dark" for="typeInsumo" style="cursor: pointer; font-size: 0.85rem;">
                                    <i class="fas fa-fill-drip d-block mb-1 text-success"></i> <?php echo __('Suprimento'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-4 px-1">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="typeRepair" name="maintenanceType" class="custom-control-input" value="Reparo">
                                <label class="custom-control-label font-weight-bold text-dark" for="typeRepair" style="cursor: pointer; font-size: 0.85rem;">
                                    <i class="fas fa-wrench d-block mb-1 text-warning"></i> <?php echo __('Reparo'); ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-4 px-1">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="typeUpgrade" name="maintenanceType" class="custom-control-input" value="Upgrade">
                                <label class="custom-control-label font-weight-bold text-dark" for="typeUpgrade" style="cursor: pointer; font-size: 0.85rem;">
                                    <i class="fas fa-arrow-up d-block mb-1 text-primary"></i> <?php echo __('Upgrade'); ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Fluxo de REPARO -->
                    <div id="repairFields" style="display: none;">
                        <div class="form-group mb-3">
                            <label class="text-gray-600 small font-weight-bold" for="maintenanceReason"><?php echo __('Motivo / Problema'); ?></label>
                            <textarea id="maintenanceReason" class="form-control" rows="3" placeholder="<?php echo __('Ex: Teclado falhando, carregador com defeito...'); ?>" style="border-radius: 10px;"></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-gray-600 small font-weight-bold" for="itemTrocado"><?php echo __('Peças Trocadas (opcional)'); ?></label>
                            <textarea id="itemTrocado" class="form-control" rows="2" placeholder="<?php echo __('Liste os componentes substituídos...'); ?>" style="border-radius: 10px;"></textarea>
                        </div>
                    </div>

                    <!-- Fluxo de UPGRADE -->
                    <div id="upgradeFields" style="display: none;">
                        <div class="form-group mb-3">
                            <label class="text-gray-600 small font-weight-bold" for="upgradeCategory"><?php echo __('Categoria do Upgrade'); ?></label>
                            <select id="upgradeCategory" class="form-control" style="border-radius: 10px;">
                                <option value=""><?php echo __('Selecione uma categoria...'); ?></option>
                                <option value="Memória"><?php echo __('Memória RAM'); ?></option>
                                <option value="Armazenamento"><?php echo __('Armazenamento (Disco)'); ?></option>
                                <option value="Outro"><?php echo __('Outro'); ?></option>
                            </select>
                        </div>

                        <div id="ramFields" style="display: none;">
                            <div class="form-group mb-3">
                                <label class="text-gray-600 small font-weight-bold" for="ramModule"><?php echo __('Módulo Selecionado'); ?></label>
                                <select id="ramModule" class="form-control" style="border-radius: 10px;">
                                    <option value="4GB DDR4">4GB DDR4</option>
                                    <option value="8GB DDR4">8GB DDR4</option>
                                    <option value="16GB DDR4">16GB DDR4</option>
                                    <option value="32GB DDR4">32GB DDR4</option>
                                    <option value="8GB DDR5">8GB DDR5</option>
                                    <option value="16GB DDR5">16GB DDR5</option>
                                    <option value="32GB DDR5">32GB DDR5</option>
                                </select>
                            </div>
                        </div>

                        <div id="storageFields" style="display: none;">
                            <div class="row">
                                <div id="divDiskType" class="col-md-7">
                                    <div class="form-group mb-3">
                                        <label class="text-gray-600 small font-weight-bold" for="diskType"><?php echo __('Tecnologia de Disco'); ?></label>
                                        <select id="diskType" class="form-control" style="border-radius: 10px;">
                                            <option value="SSD SATA">SSD SATA</option>
                                            <option value="SSD NVMe">SSD NVMe</option>
                                            <option value="HDD">HDD</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="divUpgradeValue" class="col-md-5">
                                    <div class="form-group mb-3">
                                        <label class="text-gray-600 small font-weight-bold" for="upgradeValue"><?php echo __('Capacidade'); ?></label>
                                        <select id="upgradeValue" class="form-control" style="border-radius: 10px;">
                                            <option value="120GB">120GB</option>
                                            <option value="240GB">240GB</option>
                                            <option value="480GB">480GB</option>
                                            <option value="960GB">960GB</option>
                                            <option value="256GB">256GB</option>
                                            <option value="512GB">512GB</option>
                                            <option value="1TB">1TB</option>
                                            <option value="2TB">2TB</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="text-gray-600 small font-weight-bold" for="upgradeDescription"><?php echo __('Observações do Upgrade'); ?></label>
                            <textarea id="upgradeDescription" class="form-control" rows="2" placeholder="<?php echo __('Detalhes adicionais do upgrade...'); ?>" style="border-radius: 10px;"></textarea>
                        </div>
                    </div>

                    <!-- Fluxo de INSUMO -->
                    <div id="supplyFields">
                        <div class="form-group mb-3">
                            <label class="text-gray-600 small font-weight-bold" for="supplyType"><?php echo __('Tipo de Insumo'); ?></label>
                            <select id="supplyType" class="form-control" style="border-radius: 10px;">
                                <option value=""><?php echo __('Selecione o insumo...'); ?></option>
                                <option value="Toner">Toner</option>
                                <option value="Difusor"><?php echo __('Difusor'); ?></option>
                                <option value="Cartucho de Tinta"><?php echo __('Cartucho de Tinta'); ?></option>
                                <option value="Cilindro / Drum"><?php echo __('Cilindro / Drum'); ?></option>
                                <option value="Fita de Impressão"><?php echo __('Fita de Impressão'); ?></option>
                                <option value="Kit Fusor"><?php echo __('Kit Fusor'); ?></option>
                                <option value="Outro"><?php echo __('Outros Suprimentos'); ?></option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-gray-600 small font-weight-bold" for="supplyDescription"><?php echo __('Observações'); ?></label>
                            <textarea id="supplyDescription" class="form-control" rows="2" placeholder="<?php echo __('Ex: Troca do Toner Preto - Unidade A'); ?>" style="border-radius: 10px;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fc;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 10px;"><?php echo __('Cancelar'); ?></button>
                    <button type="button" id="confirmMaintenance" class="btn btn-primary" style="background: #2c404a; border: none; border-radius: 10px;"><?php echo __('Confirmar'); ?></button>
                </div>
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
                title: '<?php echo __('Confirmar Liberação?'); ?>',
                text: "<?php echo __('O ativo voltará a ficar disponível.'); ?>",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<?php echo __('Sim, liberar'); ?>',
                cancelButtonText: '<?php echo __('Cancelar'); ?>'
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
            // Reset fields
            $('#maintenanceReason, #itemTrocado, #upgradeDescription, #supplyDescription, #upgradeValue').val('');
            $('#upgradeCategory, #ramModule, #diskType, #supplyType').val('');
            $('#ramFields, #storageFields').hide();
            
            // Set default view (Supply/Insumo)
            $('#typeInsumo').prop('checked', true);
            $('#repairFields, #upgradeFields').hide();
            $('#supplyFields').show();
            
            $('#maintenanceModal').modal('show');
        }

        // Lógica dinâmica para alternar fluxos no modal
        $(document).on('change', 'input[name="maintenanceType"]', function() {
            const type = $(this).val();
            $('#repairFields, #upgradeFields, #supplyFields').hide();
            
            if (type === 'Reparo') {
                $('#repairFields').slideDown();
            } else if (type === 'Upgrade') {
                $('#upgradeFields').slideDown();
            } else if (type === 'Insumo') {
                $('#supplyFields').slideDown();
            }
        });

        $(document).on('change', '#upgradeCategory', function() {
            const cat = $(this).val();
            $('#ramFields, #storageFields').hide();
            if (cat === 'Memória') {
                $('#ramFields').slideDown();
            }
            if (cat === 'Armazenamento') {
                $('#storageFields').slideDown();
                // Mostrar ambos os campos conforme nova solicitação (Tecnologia e Capacidade)
                $('#divUpgradeValue').show();
                $('#divDiskType').removeClass('col-md-12').addClass('col-md-7');
            } else {
                $('#divUpgradeValue').show();
                $('#divDiskType').removeClass('col-md-12').addClass('col-md-7');
            }
        });

        // Envia os dados para manutenção via AJAX
        $(document).on('click', '#confirmMaintenance', function() {
            const type = $('input[name="maintenanceType"]:checked').val();
            let data = {
                action: 'send_to_maintenance',
                id_asset: $('#maintenance_asset_id').val(),
                tipo_manutencao: type
            };

            if (type === 'Reparo') {
                data.observacoes = $('#maintenanceReason').val();
                data.item_trocado = $('#itemTrocado').val();
            } else if (type === 'Upgrade') {
                data.categoria_upgrade = $('#upgradeCategory').val();
                data.observacoes = $('#upgradeDescription').val();
                data.valor_upgrade = $('#upgradeValue').val();
                if (data.categoria_upgrade === 'Memória') data.item_trocado = $('#ramModule').val();
                if (data.categoria_upgrade === 'Armazenamento') {
                    data.item_trocado = $('#diskType').val(); // Tecnologia (SSD NVMe)
                    data.detalhes_update = $('#upgradeValue').val(); // Capacidade (1TB)
                    data.valor_upgrade = 0; // Resetar valor financeiro
                }
            } else if (type === 'Insumo') {
                data.item_trocado = $('#supplyType').val();
                data.observacoes = $('#supplyDescription').val();
            }

            if (!data.observacoes && type !== 'Insumo') {
                Swal.fire('Aviso', 'Por favor, preencha as observações.', 'warning');
                return;
            }

            $.post('ajax_ativos.php', data, function(res) {
                if (res.success) {
                    location.reload();
                } else {
                    Swal.fire('Erro', res.message || 'Erro ao processar manutenção', 'error');
                }
            }, 'json');
        });

    <!-- Modal Sucesso Cadastro com Etiqueta -->
    <div class="modal fade" id="successTagModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 bg-light" style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <h5 class="modal-title font-weight-bold text-success">
                        <i class="fas fa-check-circle mr-2"></i><?php echo __('Ativo Cadastrado!'); ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="mb-4 text-muted"><?php echo __('A identificação visual já está pronta para identificação física.'); ?></p>
                    
                    <div class="p-3 bg-white d-inline-block rounded shadow-sm mb-3" style="border: 1px dashed #ddd;">
                        <div id="qrcode_success"></div>
                    </div>
                    
                    <div class="mt-2">
                        <span class="badge badge-dark p-2" id="tag_badge_success" style="font-size: 1.2rem; border-radius: 10px; letter-spacing: 1px;">
                            <?php echo $new_asset ? htmlspecialchars($new_asset['tag']) : ''; ?>
                        </span>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-primary btn-block p-3 font-weight-bold" onclick="printAssetTagSuccess()" style="border-radius: 12px;">
                        <i class="fas fa-print mr-2"></i><?php echo __('Imprimir Etiqueta Agora'); ?>
                    </button>
                    <button type="button" class="btn btn-link text-muted btn-block" data-dismiss="modal"><?php echo __('Ignorar e Continuar'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Lógica de Impressão e Modal de Sucesso
        document.addEventListener("DOMContentLoaded", function() {
            <?php if ($new_asset): ?>
                const qrContainer = document.getElementById("qrcode_success");
                if (qrContainer) {
                    const qrData = <?php echo json_encode("ID: " . $new_asset['id_asset'] . "\nTag: " . $new_asset['tag'] . "\nModelo: " . $new_asset['modelo']); ?>;
                    new QRCode(qrContainer, {
                        text: qrData,
                        width: 200,
                        height: 200,
                        colorDark : "#2c404a",
                        colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.H
                    });
                    setTimeout(() => { $('#successTagModal').modal('show'); }, 500);
                }
            <?php endif; ?>
        });

        function printAssetTagSuccess() {
            const qrImg = document.querySelector('#qrcode_success img').src;
            const tagText = document.getElementById('tag_badge_success').innerText;
            const assetModel = <?php echo $new_asset ? json_encode($new_asset['modelo']) : "''"; ?>;
            
            const printWindow = window.open('', '_blank', 'width=400,height=500');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Imprimir Etiqueta - ${tagText}</title>
                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap');
                        body { margin: 0; padding: 20px; display: flex; align-items: center; justify-content: center; font-family: 'Montserrat', sans-serif; background: white; }
                        .label-container { text-align: center; width: 220px; padding: 10px; border: 1px dashed #ccc; border-radius: 10px; }
                        img { width: 180px; height: 180px; }
                        .tag-text { font-size: 1.4rem; font-weight: 800; color: #000; margin-top: 5px; border-top: 2px solid #000; padding-top: 5px; }
                        .asset-info { font-size: 0.7rem; color: #666; text-transform: uppercase; margin-bottom: 5px; }
                        @media print { .label-container { border: none; } body { padding: 0; } }
                    </style>
                </head>
                <body onload="setTimeout(function(){ window.print(); window.close(); }, 500);">
                    <div class="label-container">
                        <div class="asset-info">${assetModel}</div>
                        <img src="${qrImg}">
                        <div class="tag-text">${tagText}</div>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
        }

        // Finaliza o status de manutenção
        function releaseFromMaintenance(id) {
            Swal.fire({
                title: '<?php echo __('Finalizar Manutenção?'); ?>',
                text: "<?php echo __('O ativo voltará a ficar disponível e o registro de manutenção será encerrado.'); ?>",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<?php echo __('Sim, finalizar'); ?>',
                cancelButtonText: '<?php echo __('Cancelar'); ?>'
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