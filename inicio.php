<?php
/**
 * PAINEL DE CONTROLE: inicio.php
 * Página inicial após login, exibindo resumos estatísticos e indicadores de performance (KPIs).
 */
include 'auth.php';
// Validação de sessão
include 'conexao.php'; // Conexão com banco

// SEGURANÇA: Bloqueia acesso de 'Usuário' comum, redirecionando para o dashboard pessoal
if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    header("Location: index.php");
    exit();
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Console Operacional'); ?> - Asset MGT</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css">
    <link rel="stylesheet" href="/assets/css/Nunito.css">
    <link rel="stylesheet" href="/assets/css/Raleway.css">
    <link rel="stylesheet" href="/assets/css/Roboto.css">
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
    <link rel="stylesheet" href="/assets/css/Map-Clean.css">
    <link rel="stylesheet" href="/assets/css/Modern-Contact-Form.css?h=af67b929d317df499a992472a9bb8fcc">
    <link rel="stylesheet"
        href="/assets/css/Multi-Select-Dropdown-by-Jigar-Mistry.css?h=28bd9d636c700fbf60086e2bcb002efb">
    <link rel="stylesheet"
        href="/assets/css/Password-Strenght-Checker---Ambrodu-1.css?h=1af6ac373aa34a3b40f3d87a4f494eaf">
    <link rel="stylesheet"
        href="/assets/css/Password-Strenght-Checker---Ambrodu.css?h=5818638767f362b9d58a96550bd9a9a3">
    <?php include 'sidebar_style.php'; ?>
    <style>
        .clickable-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .clickable-row:hover {
            background-color: rgba(0, 0, 0, 0.05) !important;
        }

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
                <div class="container-fluid" style="padding-left: 23px; padding-right: 23px;">

                    <?php
                    include 'conexao.php';
                    require_once 'language.php';

                    // Buscar configurações de depreciação/doação globais
                    $dep_config = [
                        'taxa_depreciacao' => 10.00,
                        'taxa_tier1' => 10.00,
                        'taxa_tier2' => 10.00,
                        'taxa_tier3' => 10.00,
                        'taxa_tier4' => 10.00,
                        'taxa_infraestrutura' => 10.00,
                        'periodo_anos' => 1,
                        'periodo_meses' => 0,
                        'elegivel_doacao' => 1,
                        'tempo_doacao_anos' => 5,
                        'tempo_doacao_meses' => 0
                    ];
                    $res_dep = mysqli_query($conn, "SELECT * FROM configuracoes_depreciacao LIMIT 1");
                    if ($res_dep && mysqli_num_rows($res_dep) > 0) {
                        $dep_config = mysqli_fetch_assoc($res_dep);
                    }
                    $doacao_global = intval($dep_config['elegivel_doacao']);
                    $tempo_min_doacao_meses = (intval($dep_config['tempo_doacao_anos']) * 12) + intval($dep_config['tempo_doacao_meses']);

                    // Categorias elegíveis
                    $cat_doacao_map = [];
                    $result_cat = mysqli_query($conn, "SELECT categoria, elegivel_doacao FROM categoria_doacao");
                    if ($result_cat) {
                        while ($r = mysqli_fetch_assoc($result_cat)) {
                            $cat_doacao_map[$r['categoria']] = intval($r['elegivel_doacao']);
                        }
                    }

                    // Métricas Agregadas (Para o Dashboard)
                    $sql_metrics = "SELECT a.valor, a.dataAtivacao, a.categoria, a.tier FROM ativos a WHERE a.assigned_to IS NOT NULL";
                    $res_metrics = mysqli_query($conn, $sql_metrics);

                    $count_ativos_metrics = 0;
                    $valor_patrimonial_atual = 0;
                    $valor_original_total = 0;
                    $count_elegiveis = 0;

                    while ($m = mysqli_fetch_assoc($res_metrics)) {
                        $count_ativos_metrics++;
                        $v_original = floatval($m['valor']);
                        $valor_original_total += $v_original;

                        // Calc depreciação para métrica
                        $dat_atv = new DateTime($m['dataAtivacao']);
                        $dat_cur = new DateTime();
                        $diff_atv = $dat_atv->diff($dat_cur);
                        $meses_atv = ($diff_atv->y * 12) + $diff_atv->m;

                        $tier_m = $m['tier'] ?? null;
                        $taxa_pct = floatval($dep_config['taxa_depreciacao']);
                        if ($tier_m === 'Tier 1') {
                            $taxa_pct = floatval($dep_config['taxa_tier1']);
                        } elseif ($tier_m === 'Tier 2') {
                            $taxa_pct = floatval($dep_config['taxa_tier2']);
                        } elseif ($tier_m === 'Tier 3') {
                            $taxa_pct = floatval($dep_config['taxa_tier3']);
                        } elseif ($tier_m === 'Tier 4') {
                            $taxa_pct = floatval($dep_config['taxa_tier4']);
                        } elseif ($tier_m === 'Infraestrutura') {
                            $taxa_pct = floatval($dep_config['taxa_infraestrutura']);
                        }
                        $periodo_meses = (intval($dep_config['periodo_anos']) * 12) + intval($dep_config['periodo_meses']);

                        if ($periodo_meses > 0 && $v_original > 0) {
                            $periodos_comp = floor($meses_atv / $periodo_meses);
                            $dep_total = min($v_original, $v_original * ($taxa_pct / 100) * $periodos_comp);
                            $v_atual = max(0, $v_original - $dep_total);
                        } else {
                            $v_atual = $v_original;
                        }
                        $valor_patrimonial_atual += $v_atual;

                        // Elegibilidade
                        $cat_el = isset($cat_doacao_map[$m['categoria']]) ? $cat_doacao_map[$m['categoria']] : 1;
                        if ($doacao_global && $cat_el && ($meses_atv >= $tempo_min_doacao_meses)) {
                            $count_elegiveis++;
                        }
                    }
                    $perda_patrimonial_avg = ($valor_original_total > 0) ? (1 - ($valor_patrimonial_atual / $valor_original_total)) * 100 : 0;

                    // Paginação
                    $results_per_page = 10;
                    $sql_count = "SELECT COUNT(*) AS total FROM ativos WHERE assigned_to IS NOT NULL";
                    $res_count = mysqli_query($conn, $sql_count);
                    $row_count = mysqli_fetch_assoc($res_count);
                    $total_results = $row_count['total'];
                    $total_pages = ceil($total_results / $results_per_page);
                    $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                    $start_from = ($current_page - 1) * $results_per_page;

                    // Lista de Ativos
                    $sql_list = "SELECT a.*, u.nome as nome_usuario 
                            FROM ativos a 
                            LEFT JOIN usuarios u ON a.assigned_to = u.id_usuarios 
                            WHERE a.assigned_to IS NOT NULL 
                            ORDER BY a.id_asset DESC 
                            LIMIT $start_from, $results_per_page";
                    $result = mysqli_query($conn, $sql_list);
                    ?>

                    <h3 class="text-dark mb-4"><?php echo __('Operação'); ?></h3>

                    <!-- METRICS GRID -->
                    <div class="row mb-4 animate__animated animate__fadeInUp">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card shadow border-left-primary py-2 h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"><?php echo __('Ativos em Operação'); ?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $count_ativos_metrics; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-desktop fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card shadow border-left-success py-2 h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1"><?php echo __('Patrimônio Atualizado'); ?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">R$ <?php echo number_format($valor_patrimonial_atual, 0, ',', '.'); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card shadow border-left-warning py-2 h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"><?php echo __('Elegíveis para Doação'); ?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $count_elegiveis; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-hand-holding-heart fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card shadow border-left-danger py-2 h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1"><?php echo __('Desvalorização Média'); ?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($perda_patrimonial_avg, 1); ?>%</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow">
                        <div class="col-md-6 col-xl-3 text-nowrap">
                            <div id="dataTable_length" class="dataTables_length" aria-controls="dataTable"></div><a
                                class="btn btn-success btn-block active text-white animate__animated animate__pulse btn-user"
                                role="button"
                                style="background: rgb(44,64,74);border-radius: 10px;padding: 30px, 30px;border-width: 0px;height: 50px;margin-top: 23px;padding-top: 13px;"
                                href="/ativos_doados.php"><?php echo __('Ver Doações'); ?></a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive mt-2">
                                <table class="table my-0" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('Modelo'); ?></th>
                                            <th><?php echo __('Fabricante'); ?></th>
                                            <th><?php echo __('Categoria'); ?></th>
                                            <th><?php echo __('Tag'); ?></th>
                                            <th><?php echo __('HostName'); ?></th>
                                            <th><?php echo __('Valor'); ?></th>
                                            <th><?php echo __('MAC Address'); ?></th>
                                            <th><?php echo __('Usuário'); ?></th>
                                            <th><?php echo __('Centro de Custo'); ?></th>
                                            <th><?php echo __('Ações'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>


                                        <?php
                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $assigned_to = $row['assigned_to'];
                                                $valor_original = floatval($row['valor']);

                                                // Calcular depreciação
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
                                                    onclick="window.location='perfil_ativo.php?id=<?php echo $row['id_asset']; ?>'">
                                                    <td class="d-flex align-items-center">
                                                        <?php
                                                        $foto = !empty($row['imagem']) ? htmlspecialchars($row['imagem']) : '';
                                                        if ($foto) {
                                                            echo "<img src='$foto' class='asset-thumbnail' loading='lazy'>";
                                                        } else {
                                                            echo "<div class='asset-placeholder'><i class='fas fa-box'></i></div>";
                                                        }
                                                        echo htmlspecialchars($row['modelo']);
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['fabricante']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['tag']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['hostName']); ?></td>
                                                    <td>
                                                        <span class="font-weight-bold" style="color: #2c404a;">
                                                            R$ <?php echo number_format($valor_atual, 2, ',', '.'); ?>
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            (R$ <?php echo number_format($valor_original, 2, ',', '.'); ?>)
                                                        </small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['macAdress']); ?></td>
                                                    <td>
                                                        <?php
                                                        echo !empty($row['nome_usuario']) ? htmlspecialchars($row['nome_usuario']) : __('Desconhecido');
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['centroDeCusto']); ?></td>
                                                    <td>
                                                        <?php
                                                        // Elegibilidade para doação baseada nas configurações
                                                        $cat_do_ativo = $row['categoria'];
                                                        $cat_elegivel = isset($cat_doacao_map[$cat_do_ativo]) ? $cat_doacao_map[$cat_do_ativo] : 1;
                                                        $meses_ativos = ($diff->y * 12) + $diff->m;

                                                        $btn_style = 'style="width:150px; font-size:12px; border-radius:10px;"';

                                                        if (!$doacao_global) {
                                                            echo '<button class="btn btn-secondary btn-sm" disabled title="' . __('A doação está desativada globalmente nas configurações.') . '" ' . $btn_style . ' >' . __('Desativada') . '</button>';
                                                        } elseif (!$cat_elegivel) {
                                                            echo '<button class="btn btn-secondary btn-sm" disabled title="' . __('A categoria') . ' &quot;' . htmlspecialchars($cat_do_ativo) . '&quot; ' . __('não está habilitada para doação.') . '" ' . $btn_style . ' >' . __('Cat. não elegível') . '</button>';
                                                        } elseif ($meses_ativos >= $tempo_min_doacao_meses) {
                                                            echo '<button class="btn btn-success btn-sm" onclick="event.stopPropagation(); sellAsset(' . $row['id_asset'] . ')" ' . $btn_style . ' >' . __('Doar') . '</button>';
                                                        } else {
                                                            $restante = $tempo_min_doacao_meses - $meses_ativos;
                                                            $a = floor($restante / 12);
                                                            $m = $restante % 12;
                                                            $t = '';
                                                            if ($a > 0)
                                                                $t .= $a . ' ' . __('ano(s)');
                                                            if ($a > 0 && $m > 0)
                                                                $t .= __(' e ');
                                                            if ($m > 0)
                                                                $t .= $m . ' ' . __('mês(es)');
                                                            if (empty($t))
                                                                $t = __('menos de 1 mês');
                                                            echo '<button class="btn btn-warning btn-sm" disabled title="' . __('Carência:') . ' ' . $t . '" ' . $btn_style . ' >' . __('Bloqueado') . '</button>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='10' class='text-center py-4 text-muted'>" . __('Nenhum ativo atribuído encontrado.') . "</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-start mt-3">
                                <ul class="pagination-custom">
                                    <?php
                                    if ($current_page > 1) {
                                        echo "<li><a href='?page=" . ($current_page - 1) . "'>" . __('« Anterior') . "</a></li>";
                                    }
                                    for ($page = 1; $page <= $total_pages; $page++) {
                                        if ($page == $current_page) {
                                            echo "<li class='active'><span>$page</span></li>";
                                        } else {
                                            echo "<li><a href='?page=$page'>$page</a></li>";
                                        }
                                    }
                                    if ($current_page < $total_pages) {
                                        echo "<li><a href='?page=" . ($current_page + 1) . "'>" . __('Próximo »') . "</a></li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <script>

                    // Função para doar o ativo (transferir para a tabela "venda" - agora doações)
                    // Função para doar o ativo (transferir para a tabela "venda" - agora doações)
                    function sellAsset(assetId) {
                        Swal.fire({
                            title: '<?php echo __('Confirmar Doação?'); ?>',
                            text: "<?php echo __('Tem certeza que deseja doar este ativo? O registro será movido do inventário ativo.'); ?>",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#2c404a',
                            cancelButtonColor: '#d33',
                            confirmButtonText: '<?php echo __('Sim, doar'); ?>',
                            cancelButtonText: '<?php echo __('Cancelar'); ?>'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch('doar_ativo.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({ id_asset: assetId })
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            Swal.fire({
                                                title: '<?php echo __('Sucesso!'); ?>',
                                                text: '<?php echo __('Ativo doado com sucesso!'); ?>',
                                                icon: 'success',
                                                timer: 1500,
                                                showConfirmButton: false
                                            }).then(() => location.reload());
                                        } else {
                                            Swal.fire('<?php echo __('Erro'); ?>', data.message || '<?php echo __('Erro ao processar doação!'); ?>', 'error');
                                        }
                                    })
                                    .catch(error => {
                                        Swal.fire('<?php echo __('Erro'); ?>', '<?php echo __('Falha na comunicação com o servidor.'); ?>', 'error');
                                    });
                            }
                        });
                    }



                </script>

                <?php
                mysqli_close($conn);
                ?>



            </div>
        </div>
    </div>
    </div>
    <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"
        defer></script>
    <script src="/assets/js/bs-init.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" defer></script>
    <script src="/assets/js/global_search.js" defer></script>
</body>

</html>