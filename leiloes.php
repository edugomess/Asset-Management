<?php
/**
 * LISTAGEM DE ATIVOS ELEGÍVEIS PARA LEILÃO: leiloes.php
 * Exibe os ativos cujo Tier determina o fim-de-vida como Leilão e estão 100% depreciados.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'auth.php';
include_once 'conexao.php';

// POST Export CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_leilao'])) {
    $ids = $_POST['lote'] ?? [];
    if (!empty($ids)) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Lote_Leilao_' . date('Ymd_His') . '.csv"');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        
        $output = fopen('php://output', 'w');
        fputcsv($output, [
            __('Tag'), 
            __('Categoria'), 
            __('Fabricante'), 
            __('Modelo'), 
            __('Série / SN'), 
            __('MAC / IMEI'), 
            __('Processador'), 
            __('Memória'), 
            __('Armazenamento'), 
            __('Centro de Custo'), 
            __('Data de Ativação'), 
            __('Tier'),
            __('Status Atual'), 
            __('Usuário / Local'), 
            __('Valor Original'), 
            __('Lance Sugerido (10%)')
        ], ';');
        
        $ids_str = implode(',', array_map('intval', $ids));
        $sql_exp = "SELECT a.*, u.nome as user_nome, u.sobrenome as user_sb, l.nome_local 
                    FROM ativos a 
                    LEFT JOIN usuarios u ON a.assigned_to = u.id_usuarios 
                    LEFT JOIN locais l ON a.id_local = l.id_local
                    WHERE a.id_asset IN ($ids_str)";
        $res_exp = mysqli_query($conn, $sql_exp);
        
        if ($res_exp) {
            while ($row = mysqli_fetch_assoc($res_exp)) {
                $status = ucfirst(strtolower($row['status'] ?? 'Disponível'));
                $is_assigned = (!empty($row['user_nome']) || !empty($row['user_sb']) || !empty($row['nome_local']));
                if ($is_assigned) $status = 'Em uso';
                elseif (in_array($status, ['Em estoque', 'Disponível'])) $status = 'Disponível';
                
                $usuario = __('Disponível');
                if (!empty($row['user_nome']) || !empty($row['user_sb'])) {
                    $usuario = trim($row['user_nome'] . ' ' . $row['user_sb']);
                } elseif (!empty($row['nome_local'])) {
                    $usuario = trim($row['nome_local']);
                }
                
                $v_original = floatval($row['valor']);
                $lance = $v_original * 0.10;
                
                // Formatação unida de hardware
                $armazenamento = (!empty($row['armazenamento']) && !empty($row['tipo_armazenamento'])) ? $row['armazenamento'] . ' ' . $row['tipo_armazenamento'] : ($row['armazenamento'] ?? '');
                $mac_imei = (!empty($row['macAdress'])) ? $row['macAdress'] : ($row['imei'] ?? '');
                $dataAtv = (!empty($row['dataAtivacao'])) ? date('d/m/Y', strtotime($row['dataAtivacao'])) : '';

                fputcsv($output, [
                    $row['tag'] ?? '',
                    $row['categoria'] ?? '',
                    $row['fabricante'] ?? '',
                    $row['modelo'] ?? '',
                    $row['numero_serie'] ?? '',
                    $mac_imei,
                    $row['processador'] ?? '',
                    $row['memoria'] ?? '',
                    $armazenamento,
                    $row['centroDeCusto'] ?? '',
                    $dataAtv,
                    $row['tier'] ?? '',
                    __($status),
                    $usuario,
                    'R$ ' . number_format($v_original, 2, ',', '.'),
                    'R$ ' . number_format($lance, 2, ',', '.')
                ], ';');
            }
        }
        fclose($output);
        exit;
    }
}

// Busca configurações de depreciação globais
$dep_config = [
    'taxa_depreciacao' => 10.00,
    'taxa_tier1' => 10.00,
    'taxa_tier2' => 10.00,
    'taxa_tier3' => 10.00,
    'taxa_tier4' => 10.00,
    'taxa_infraestrutura' => 10.00,
    'periodo_anos' => 1,
    'periodo_meses' => 0,
    'elegivel_doacao' => 0,
    'tempo_doacao_anos' => 5,
    'tempo_doacao_meses' => 0,
    'destinacao_tier1' => 'Doação',
    'destinacao_tier2' => 'Doação',
    'destinacao_tier3' => 'Doação',
    'destinacao_tier4' => 'Doação',
    'destinacao_infraestrutura' => 'Doação',
    'elegivel_leilao' => 0
];
$result_dep = mysqli_query($conn, "SELECT * FROM configuracoes_depreciacao LIMIT 1");
if ($result_dep && mysqli_num_rows($result_dep) > 0) {
    $dep_config = mysqli_fetch_assoc($result_dep);
}

$leilao_global = intval($dep_config['elegivel_leilao']);
$mapDestinacoes = [
    'Tier 1' => $dep_config['destinacao_tier1'] ?? 'Doação',
    'Tier 2' => $dep_config['destinacao_tier2'] ?? 'Doação',
    'Tier 3' => $dep_config['destinacao_tier3'] ?? 'Doação',
    'Tier 4' => $dep_config['destinacao_tier4'] ?? 'Doação',
    'Infraestrutura' => $dep_config['destinacao_infraestrutura'] ?? 'Doação'
];
$periodo_meses_depreciacao = (intval($dep_config['periodo_anos']) * 12) + intval($dep_config['periodo_meses']);

// Fetch all assets to filter in PHP
$sql = "SELECT a.*, u.nome as user_nome, u.sobrenome as user_sb, l.nome_local 
        FROM ativos a 
        LEFT JOIN usuarios u ON a.assigned_to = u.id_usuarios
        LEFT JOIN locais l ON a.id_local = l.id_local";
$result = mysqli_query($conn, $sql);

$ativos_leilao = [];

if ($result) {
    while ($m = mysqli_fetch_assoc($result)) {
        if (!$leilao_global) continue; // Leilão desativado globalmente
        
        $tier_m = $m['tier'] ?? null;
        $dest_m = $mapDestinacoes[$tier_m] ?? 'Doação';
        
        if ($dest_m !== 'Leilão') continue; // Destino não é leilão

        $v_original = floatval($m['valor']);
        $dataAtivacao = $m['dataAtivacao'];

        if (empty($dataAtivacao) || $v_original <= 0) continue; // Sem data ou sem valor

        // Calcula Depreciação
        $dat_atv = new DateTime($dataAtivacao);
        $dat_cur = new DateTime();
        $diff_atv = $dat_atv->diff($dat_cur);
        $meses_atv = ($diff_atv->y * 12) + $diff_atv->m;

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

        if ($periodo_meses_depreciacao > 0 && $v_original > 0) {
            $periodos_comp = floor($meses_atv / $periodo_meses_depreciacao);
            $dep_total = min($v_original, $v_original * ($taxa_pct / 100) * $periodos_comp);
            $percentual_dep = ($dep_total / $v_original) * 100;
        } else {
            $percentual_dep = 0;
            $dep_total = 0;
        }

        if ($percentual_dep >= 100) {
            $m['valor_residual'] = $v_original - $dep_total;
            $m['lance_inicial'] = $v_original * 0.10; // Market practice: 10% minimum starting bid for 100% depreciated assets
            $ativos_leilao[] = $m;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">
<style>
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
</style>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Gestão de Lotes - Leilão'); ?></title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/css/Nunito.css">
    <?php include 'sidebar_style.php'; ?>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0"><a
                    class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#">
                    <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-gavel"></i></div>
                    <div class="sidebar-brand-text mx-3"><span>ASSET MGT</span></div>
                </a>
                <?php include_once 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <?php include_once 'topbar.php'; ?>
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="text-dark"><i class="fas fa-boxes mr-2 text-warning"></i><?php echo __('Lotes de Leilão'); ?></h3>
                        <div>
                            <button type="button" class="btn btn-warning shadow-sm mr-2 text-dark font-weight-bold" onclick="leiloarLote()"><i class="fas fa-gavel fa-sm text-dark mr-2"></i> <?php echo __('Marcar Lote como Leiloado'); ?></button>
                            <button type="button" class="btn btn-success shadow-sm" onclick="extractBatch()"><i class="fas fa-file-excel fa-sm text-white-50 mr-2"></i> <?php echo __('Extrair Planilha Excel'); ?></button>
                        </div>
                    </div>

                    <div class="card shadow">
                        <div class="card-header py-3">
                            <p class="text-primary m-0 font-weight-bold"><?php echo __('Ativos 100% Depreciados Elegíveis'); ?></p>
                        </div>
                        <div class="card-body">
                            <?php if (!$leilao_global): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <?php echo __('A política global de Leilão está temporariamente desativada nas configurações.'); ?>
                                </div>
                            <?php else: ?>
                                <form id="exportForm" method="POST" target="_blank">
                                    <input type="hidden" name="export_leilao" value="1">
                                    <div class="table-responsive table mt-2" id="dataTable" role="grid" aria-describedby="dataTable_info">
                                    <table class="table my-0 table-hover" id="loteTable">
                                        <thead>
                                            <tr>
                                                <th><?php echo __('Sel'); ?></th>
                                                <th><?php echo __('Ativo'); ?></th>
                                                <th><?php echo __('Categoria'); ?></th>
                                                <th><?php echo __('Tag'); ?></th>
                                                <th><?php echo __('Status Atual'); ?></th>
                                                <th><?php echo __('Usuário / Local'); ?></th>
                                                <th><?php echo __('Lance Sugerido (10%)'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if (!empty($ativos_leilao)) {
                                                foreach ($ativos_leilao as $ativo) {
                                                    $usuario_nome_local = __('Disponível');
                                                    if (!empty($ativo['user_nome']) || !empty($ativo['user_sb'])) {
                                                        $usuario_nome_local = trim($ativo['user_nome'] . ' ' . $ativo['user_sb']);
                                                    } elseif (!empty($ativo['nome_local'])) {
                                                        $usuario_nome_local = trim($ativo['nome_local']);
                                                    }
                                                    
                                                    $raw_status = ucfirst(strtolower($ativo['status'] ?? 'Disponível'));
                                                    $is_assigned = (!empty($ativo['user_nome']) || !empty($ativo['user_sb']) || !empty($ativo['nome_local']));
                                                    if ($is_assigned) {
                                                        $raw_status = 'Em uso';
                                                    } elseif (in_array($raw_status, ['Em estoque', 'Disponível'])) {
                                                        $raw_status = 'Disponível';
                                                    }
                                                    
                                                    $badge_class = 'badge-secondary';
                                                    if ($raw_status === 'Disponível') {
                                                        $badge_class = 'badge-success';
                                                    } elseif ($raw_status === 'Em uso') {
                                                        $badge_class = 'badge-primary';
                                                    } elseif (in_array($raw_status, ['Em manutenção', 'Manutenção'])) {
                                                        $badge_class = 'badge-warning';
                                                    } else {
                                                        $badge_class = 'badge-danger';
                                                    }
                                                    ?>
                                                    <tr class="clickable-row" data-href="perfil_ativo.php?id=<?php echo $ativo['id_asset']; ?>">
                                                        <td onclick="event.stopPropagation();">
                                                            <input type="checkbox" name="lote[]" value="<?php echo $ativo['id_asset']; ?>">
                                                        </td>
                                                        <td class="d-flex align-items-center">
                                                            <?php
                                                            $foto = !empty($ativo['foto']) ? htmlspecialchars($ativo['foto']) : '';
                                                            if ($foto) {
                                                                echo "<img src='$foto' class='asset-thumbnail'>";
                                                            } else {
                                                                echo "<div class='asset-placeholder'><i class='fas fa-box'></i></div>";
                                                            }
                                                            echo htmlspecialchars($ativo['modelo'] ?? 'N/A');
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($ativo['categoria']); ?></td>
                                                        <td><span class="badge badge-secondary" style="font-size: 0.9em; padding: 6px 10px;"><?php echo htmlspecialchars($ativo['tag']); ?></span></td>
                                                        <td><span class="badge <?php echo $badge_class; ?>" style="font-size: 0.9em; padding: 6px 10px;"><?php echo __($raw_status); ?></span></td>
                                                        <td><?php echo htmlspecialchars($usuario_nome_local); ?></td>
                                                        <td class="font-weight-bold text-success">R$ <?php echo number_format($ativo['lance_inicial'], 2, ',', '.'); ?></td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else {
                                                echo "<tr><td colspan='7' class='text-center py-4 text-muted'>" . __('Nenhum equipamento logrou elegibilidade de leilão sob o critério de 100% de depreciação e destinação de Tier.') . "</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
        <script src="/assets/js/theme.js"></script>
        <script>
            $(document).ready(function() {
                $(document).on('click', '.clickable-row', function(e) {
                    if (!$(e.target).closest('input[type="checkbox"], button, a, .btn').length) {
                        const href = $(this).data('href');
                        if (href) {
                            window.location = href;
                        }
                    }
                });
            });

            function extractBatch() {
                if ($('input[name="lote[]"]:checked').length === 0) {
                    alert("<?php echo __('Selecione ao menos um ativo marcando a caixa [Sel] para extrair.'); ?>");
                    return;
                }
                $('#exportForm').submit();
            }

            function leiloarLote() {
                var selected = [];
                $('input[name="lote[]"]:checked').each(function() {
                    selected.push($(this).val());
                });

                if (selected.length === 0) {
                    alert("<?php echo __('Selecione ao menos um ativo marcando a caixa [Sel] para Leiloar.'); ?>");
                    return;
                }

                if (confirm("<?php echo __('Tem certeza que deseja marcar estes ativos como LEILOADOS? Esta ação transferirá os itens do inventário ativo para o histórico de Vendas/Leilão definitivamente e não pode ser desfeita.'); ?>")) {
                    $.ajax({
                        url: 'leiloar_lote.php',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({ lote: selected }),
                        success: function(resp) {
                            if (resp && resp.success) {
                                alert("<?php echo __('Lote leiloado com sucesso e transferido para o Histórico!'); ?>");
                                location.reload();
                            } else {
                                alert("Erro: " + (resp.message ? resp.message : "<?php echo __('Ocorreu um erro no servidor.'); ?>"));
                            }
                        },
                        error: function() {
                            alert("<?php echo __('Erro na comunicação com o servidor.'); ?>");
                        }
                    });
                }
            }
        </script>
</body>
</html>
