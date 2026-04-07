<?php include 'performance_header.php'; ?>
<?php
/**
 * INVENTÁRIO DE ATIVOS: equipamentos.php
 * Listagem principal para gestão, filtragem e acompanhamento de todo o hardware da empresa.
 */
// Inclui os arquivos de autenticação e conexão com o banco de dados
include_once 'auth.php';
include_once 'conexao.php';

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
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>"> <!-- Define o idioma da página conforme a sessão -->
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
    <script>
        // --- FUNÇÃO DE IMPRESSÃO (DEFINIDA NO TOPO PARA SEGURANÇA) ---
        function printAssetTagSuccess() {
            const assetId = <?php echo (isset($new_asset) && $new_asset) ? $new_asset['id_asset'] : "0"; ?>;
            if (assetId === 0) return;
            
            const printUrl = `imprimir_etiqueta.php?id=${assetId}`;
            let printFrame = document.getElementById('print_iframe');
            if (!printFrame) {
                printFrame = document.createElement('iframe');
                printFrame.id = 'print_iframe';
                printFrame.style.display = 'none';
                document.body.appendChild(printFrame);
            }
            printFrame.src = printUrl;
        }
    </script>
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <?php renderPerformanceHints(); ?>
    <?php include_once 'sidebar_style.php'; ?>
    <link rel="stylesheet" href="/assets/css/Simple-footer-by-krissy.css?h=73316da5ae5ad6b51632cd2e5413f263">
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
    <?php startNProgress(); ?>
    <div id="wrapper">
        <!-- Barra Lateral (Sidebar) -->
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include_once 'sidebar_brand.php'; ?>
                <?php include_once 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column premium-page-fade" id="content-wrapper">
            <div id="content">
                <!-- Barra Superior (Topbar) -->
                <?php include_once 'topbar.php'; ?>

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

                                // Busca os dados dos ativos com JOIN em usuários, locais e status de manutenção
                                $sql = "SELECT a.*, u.nome AS user_nome, l.nome_local, m_info.id_manutencao as em_manutencao, m_info.observacoes as manutencao_motivo 
                                        FROM ativos a 
                                        LEFT JOIN usuarios u ON a.assigned_to = u.id_usuarios 
                                        LEFT JOIN locais l ON a.id_local = l.id_local
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
                                            <th scope="col"><?php echo __('Nível de Atribuição'); ?></th>
                                            <th scope="col"><?php echo __('CC'); ?></th>
                                            <?php if ($status_filter !== 'Manutencao'): ?>
                                                <th scope="col"><?php echo __('Atribuído a'); ?></th>
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
                                            $dep_config = ['taxa_depreciacao' => 10.00, 'taxa_tier1' => 10.00, 'taxa_tier2' => 10.00, 'taxa_tier3' => 10.00, 'taxa_tier4' => 10.00, 'taxa_infraestrutura' => 10.00, 'periodo_anos' => 1, 'periodo_meses' => 0];
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

                                                $tier_row = $row['tier'] ?? null;
                                                $taxa_pct = floatval($dep_config['taxa_depreciacao']);
                                                if ($tier_row === 'Tier 1') {
                                                    $taxa_pct = floatval($dep_config['taxa_tier1']);
                                                } elseif ($tier_row === 'Tier 2') {
                                                    $taxa_pct = floatval($dep_config['taxa_tier2']);
                                                } elseif ($tier_row === 'Tier 3') {
                                                    $taxa_pct = floatval($dep_config['taxa_tier3']);
                                                } elseif ($tier_row === 'Tier 4') {
                                                    $taxa_pct = floatval($dep_config['taxa_tier4']);
                                                } elseif ($tier_row === 'Infraestrutura') {
                                                    $taxa_pct = floatval($dep_config['taxa_infraestrutura']);
                                                }
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
                                                    data-href="perfil_ativo.php?id=<?php echo $row['id_asset']; ?>">
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
                                                    <td><strong><?php echo htmlspecialchars(($row['tier'] ?: $row['setor']) ?: '-'); ?></strong></td>
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
                                                            <?php 
                                                            if (!empty($row['assigned_to']) && !empty($row['user_nome'])) {
                                                                echo htmlspecialchars($row['user_nome']);
                                                            } elseif (!empty($row['id_local']) && !empty($row['nome_local'])) {
                                                                echo htmlspecialchars($row['nome_local']);
                                                            } else {
                                                                echo __('Disponível');
                                                            }
                                                            ?>
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
                                                        <?php
                                                        $raw_status = ucfirst(strtolower($row['status']));
                                                        
                                                        // Override the raw DB string visually if assignment is present 
                                                        $is_assigned = (!empty($row['user_nome']) || !empty($row['nome_local']));
                                                        if (!empty($row['em_manutencao'])) {
                                                            $raw_status = 'Em manutenção';
                                                        } elseif ($is_assigned) {
                                                            $raw_status = 'Em uso';
                                                        } else {
                                                            $raw_status = 'Disponível';
                                                        }

                                                        $badge_class = 'badge-secondary';
                                                        if (in_array($raw_status, ['Disponível'])) {
                                                            $badge_class = 'badge-success';
                                                        } elseif ($raw_status === 'Em uso') {
                                                            $badge_class = 'badge-primary';
                                                        } elseif (in_array($raw_status, ['Em manutenção', 'Manutenção', 'Manutencao'])) {
                                                            $badge_class = 'badge-warning';
                                                        } else {
                                                            $badge_class = 'badge-danger';
                                                        }
                                                        ?>
                                                        <span class="status-badge <?php echo $badge_class; ?>">
                                                            <?php echo __($raw_status); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <!-- Ações rápidas: Atribuir, Editar e Manutenção -->
                                                        <div class="d-flex align-items-center">
                                                            <?php if (!$row['em_manutencao']): ?>
                                                                <?php if (!empty($row['assigned_to']) || !empty($row['id_local'])): ?>
                                                                    <button class='btn btn-danger btn-tamanho-fixo mr-2'
                                                                        style="border: none;"
                                                                        title="<?php echo __('Liberar Ativo'); ?>"
                                                                        onclick='event.stopPropagation(); unassignUser(<?php echo $row["id_asset"]; ?>)'>
                                                                        <i class='fas fa-minus-circle mr-2'></i><?php echo __('Liberar'); ?>
                                                                    </button>
                                                                <?php else: ?>
                                                                    <button class='btn btn-success btn-tamanho-fixo mr-2' 
                                                                        style="border: none;"
                                                                        title="<?php echo __('Atribuir Responsável / Local'); ?>"
                                                                        onclick='event.stopPropagation(); openAssignModal(<?php echo $row["id_asset"]; ?>)'>
                                                                        <i class='fas fa-plus-circle mr-2'></i><?php echo __('Atribuir'); ?>
                                                                    </button>
                                                                <?php endif; ?>
                                                            <?php endif; ?>

                                                            <a class='btn btn-warning btn-edit mr-2'
                                                                href='editar_ativo.php?id=<?php echo $row["id_asset"]; ?>'
                                                                onclick='event.stopPropagation();'
                                                                title="<?php echo __('Editar Ativo'); ?>"><i
                                                                    class='fas fa-edit'></i></a>


                                                            <?php if ($row['em_manutencao']): ?>
                                                                <button class='btn btn-success btn-tamanho-fixo mr-2' 
                                                                        title="<?php echo __('Liberar da Manutenção'); ?>"
                                                                        onclick="event.stopPropagation(); releaseFromMaintenance(<?php echo $row['id_asset']; ?>)">
                                                                    <i class='fas fa-check-circle mr-2'></i><?php echo __('Liberar'); ?>
                                                                </button>
                                                            <?php else: ?>
                                                                <button class='btn btn-maintenance-system btn-edit mr-2' 
                                                                        style="background: #2c404a; border: none;"
                                                                        title="<?php echo __('Enviar para Manutenção'); ?>"
                                                                        onclick="event.stopPropagation(); sendToMaintenance(<?php echo $row['id_asset']; ?>)">
                                                                    <i class='fas fa-tools'></i>
                                                                </button>
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
                        </div> <!-- card-body -->
                    </div> <!-- card shadow -->
                </div> <!-- container-fluid -->
            </div> <!-- content -->

        </div> <!-- content-wrapper -->
    <!-- performance_footer.php is now at the very end -->
    </div> <!-- wrapper -->

    <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <!-- Bibliotecas JavaScript (Sincronizadas e Locais para Resiliência) -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script src="assets/js/theme.js"></script>
    <!-- SweetAlert2 para notificações elegantes -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="assets/js/global_search.js" defer></script>


    <!-- Modal Sucesso Cadastro com Etiqueta -->
    <div class="modal fade" id="successTagModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 p-4" style="background: #2c404a; color: white;">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 mr-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-weight-bold mb-0" id="successTagModalLabel"><?php echo __('Ativo Cadastrado!'); ?></h5>
                            <small class="text-white-50"><?php echo __('A identificação visual já está pronta para identificação física.'); ?></small>
                        </div>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-4 bg-light">
                    <div class="p-3 bg-white d-inline-block rounded shadow-sm mb-3" style="border: 1px dashed #ddd;">
                        <div id="qrcode_success"></div>
                    </div>
                    
                    <div class="mt-2">
                        <span class="badge badge-dark p-2" id="tag_badge_success" style="font-size: 1.2rem; border-radius: 10px; letter-spacing: 1px;">
                            <?php echo (isset($new_asset) && $new_asset) ? htmlspecialchars($new_asset['tag']) : ''; ?>
                        </span>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-white d-flex flex-column">
                    <button type="button" class="btn btn-primary btn-block p-3 font-weight-bold shadow-sm mb-2" onclick="printAssetTagSuccess()" style="border-radius: 12px; background: #2c404a; border: none;">
                        <i class="fas fa-print mr-2"></i><?php echo __('Imprimir Etiqueta Agora'); ?>
                    </button>
                    <button type="button" class="btn btn-link text-muted font-weight-bold btn-block m-0" data-dismiss="modal">
                        <?php echo __('Ignorar e Continuar'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <!-- Modals (Posicionados no fim do body para evitar problemas de z-index/backdrop) -->
    
    <?php include_once 'modals_ativos_shared.php'; ?>
    <script>
        $(document).ready(function() {
            // Lógica de Impressão e Modal de Sucesso (QRCode)
            <?php if (isset($new_asset) && $new_asset): ?>
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



            // Torna as linhas da tabela clicáveis com proteção para botões
            $(document).on('click', '.clickable-row', function(e) {
                if (!$(e.target).closest('button, a, .btn, .status-badge').length) {
                    const href = $(this).data('href');
                    if (href) {
                        window.location = href;
                    }
                }
            });
        });
    </script>

    <?php include_once 'performance_footer.php'; ?>
</body>
</html>