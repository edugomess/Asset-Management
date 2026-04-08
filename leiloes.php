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

// Constants for strings used multiple times to satisfy SonarQube
if (!defined('STATUS_DISPONIVEL')) {
    define('STATUS_DISPONIVEL', 'Disponível');
}
if (!defined('STATUS_EM_USO')) {
    define('STATUS_EM_USO', 'Em uso');
}
if (!defined('DESTINO_LEILAO')) {
    define('DESTINO_LEILAO', 'Leilão');
}
if (!defined('DESTINO_DOACAO')) {
    define('DESTINO_DOACAO', 'Doação');
}

// POST Export CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_leilao'])) {
    $ids = $_POST['lote'] ?? [];
    if (!empty($ids)) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Lote_Leilao_' . date('Ymd_His') . '.csv"');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        
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
                $status = ucfirst(strtolower($row['status'] ?? STATUS_DISPONIVEL));
                $is_assigned = (!empty($row['user_nome']) || !empty($row['user_sb']) || !empty($row['nome_local']));
                if ($is_assigned) {
                    $status = STATUS_EM_USO;
                } elseif (in_array($status, ['Em estoque', STATUS_DISPONIVEL])) {
                    $status = STATUS_DISPONIVEL;
                }
                
                $usuario = __(STATUS_DISPONIVEL);
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
    'destinacao_tier1' => DESTINO_DOACAO,
    'destinacao_tier2' => DESTINO_DOACAO,
    'destinacao_tier3' => DESTINO_DOACAO,
    'destinacao_tier4' => DESTINO_DOACAO,
    'destinacao_infraestrutura' => DESTINO_DOACAO,
    'elegivel_leilao' => 0
];
$result_dep = mysqli_query($conn, "SELECT * FROM configuracoes_depreciacao LIMIT 1");
if ($result_dep && mysqli_num_rows($result_dep) > 0) {
    $dep_config = mysqli_fetch_assoc($result_dep);
}

$leilao_global = intval($dep_config['elegivel_leilao'] ?? 0);
$mapDestinacoes = [
    'Tier 1' => $dep_config['destinacao_tier1'] ?? DESTINO_DOACAO,
    'Tier 2' => $dep_config['destinacao_tier2'] ?? DESTINO_DOACAO,
    'Tier 3' => $dep_config['destinacao_tier3'] ?? DESTINO_DOACAO,
    'Tier 4' => $dep_config['destinacao_tier4'] ?? DESTINO_DOACAO,
    'Infraestrutura' => $dep_config['destinacao_infraestrutura'] ?? DESTINO_DOACAO
];
$periodo_meses_depreciacao = (intval($dep_config['periodo_anos'] ?? 1) * 12) + intval($dep_config['periodo_meses'] ?? 0);

// Fetch all assets to filter in PHP
$sql = "SELECT a.*, u.nome as user_nome, u.sobrenome as user_sb, l.nome_local
        FROM ativos a
        LEFT JOIN usuarios u ON a.assigned_to = u.id_usuarios
        LEFT JOIN locais l ON a.id_local = l.id_local";
$result = mysqli_query($conn, $sql);

$ativos_leilao = [];

if ($result) {
    while ($m = mysqli_fetch_assoc($result)) {
        if (!$leilao_global) {
             continue; // Leilão desativado globalmente
        }
        
        $tier_m = $m['tier'] ?? null;
        $dest_m = $mapDestinacoes[$tier_m] ?? DESTINO_DOACAO;
        
        if ($dest_m !== DESTINO_LEILAO) {
             continue; // Destino não é leilão
        }

        $v_original = floatval($m['valor']);
        $dataAtivacao = $m['dataAtivacao'];

        if (empty($dataAtivacao) || $v_original <= 0) {
             continue; // Sem data ou sem valor
        }

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
    .btn-action {
        width: 35px;
        height: 35px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin: 0 2px;
        transition: all 0.2s;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
    <?php include_once 'sidebar_style.php'; ?>
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
                        <h3 class="text-dark"><i class="fas fa-boxes mr-2 text-warning"></i><?php echo __('Gestão de Lotes de Leilão'); ?></h3>
                        <div class="d-flex align-items-center">
                            <?php if (isset($_SESSION['active_lote'])): ?>
                                <div class="alert alert-info py-2 px-3 mb-0 mr-3 d-flex align-items-center shadow-sm">
                                    <i class="fas fa-box-open mr-2 text-primary"></i>
                                    <div>
                                        <small class="text-muted d-block"><?php echo __('Lote Ativo'); ?></small>
                                        <strong class="text-primary"><?php echo htmlspecialchars($_SESSION['active_lote']['nome']); ?></strong>
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger ml-3" onclick="closeLote(<?php echo $_SESSION['active_lote']['id']; ?>)" title="<?php echo __('Fechar Lote'); ?>"><i class="fas fa-lock"></i> <?php echo __('Fechar'); ?></button>
                                </div>
                                <button type="button" class="btn btn-primary shadow-sm mr-2" onclick="addToBatch(<?php echo $_SESSION['active_lote']['id']; ?>)">
                                    <i class="fas fa-plus-circle fa-sm mr-2"></i> <?php echo __('Adicionar ao Lote'); ?>
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-warning shadow-sm mr-2 text-dark font-weight-bold" onclick="createLote()">
                                    <i class="fas fa-plus-square fa-sm text-dark mr-2"></i> <?php echo __('Gerar Novo Lote'); ?>
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-success shadow-sm" onclick="extractBatch()"><i class="fas fa-file-excel fa-sm text-white-50 mr-2"></i> <?php echo __('Extrair Planilha'); ?></button>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="text-primary m-0 font-weight-bold"><?php echo __('Ativos 100% Depreciados Elegíveis'); ?></h6>
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
                                                <th style="width: 40px;"><?php echo __('Sel'); ?></th>
                                                <th><?php echo __('Ativo'); ?></th>
                                                <th><?php echo __('Categoria'); ?></th>
                                                <th><?php echo __('Tag'); ?></th>
                                                <th><?php echo __('Status Atual'); ?></th>
                                                <th><?php echo __('Usuário / Local'); ?></th>
                                                <th><?php echo __('Lance Sugerido'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if (!empty($ativos_leilao)) {
                                                foreach ($ativos_leilao as $ativo) {
                                                    // Skip assets already in a batch
                                                    if ($ativo['id_lote']) {
                                                        continue;
                                                    }

                                                    $usuario_nome_local = __(STATUS_DISPONIVEL);
                                                    if (!empty($ativo['user_nome']) || !empty($ativo['user_sb'])) {
                                                        $usuario_nome_local = trim($ativo['user_nome'] . ' ' . $ativo['user_sb']);
                                                    } elseif (!empty($ativo['nome_local'])) {
                                                        $usuario_nome_local = trim($ativo['nome_local']);
                                                    }
                                                    
                                                    $raw_status = ucfirst(strtolower($ativo['status'] ?? STATUS_DISPONIVEL));
                                                    $is_assigned = (!empty($ativo['user_nome']) || !empty($ativo['user_sb']) || !empty($ativo['nome_local']));
                                                    if ($is_assigned) {
                                                        $raw_status = STATUS_EM_USO;
                                                    } elseif (in_array($raw_status, ['Em estoque', STATUS_DISPONIVEL])) {
                                                        $raw_status = STATUS_DISPONIVEL;
                                                    }
                                                    
                                                    $badge_class = 'badge-secondary';
                                                    if ($raw_status === STATUS_DISPONIVEL) {
                                                        $badge_class = 'badge-success';
                                                    } elseif ($raw_status === STATUS_EM_USO) {
                                                        $badge_class = 'badge-primary';
                                                    } elseif (in_array($raw_status, ['Em manutenção', 'Manutenção'])) {
                                                        $badge_class = 'badge-warning';
                                                    } else {
                                                        $badge_class = 'badge-danger';
                                                    }
                                                    ?>
                                                    <tr class="clickable-row" data-href="perfil_ativo.php?id=<?php echo $ativo['id_asset']; ?>" onkeydown="if(event.keyCode==13) window.location='perfil_ativo.php?id=<?php echo $ativo['id_asset']; ?>'">
                                                        <td onclick="event.stopPropagation();">
                                                            <input type="checkbox" name="lote[]" value="<?php echo $ativo['id_asset']; ?>" data-status="<?php echo $raw_status; ?>" aria-label="<?php echo __('Selecionar ativo'); ?>">
                                                        </td>
                                                        <td class="d-flex align-items-center">
                                                            <?php
                                                            $foto = !empty($ativo['foto']) ? htmlspecialchars($ativo['foto']) : '';
                                                            if ($foto) {
                                                                echo "<img src='$foto' class='asset-thumbnail' alt='Asset photo'>";
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
                                                echo "<tr><td colspan='7' class='text-center py-4 text-muted'>" . __('Nenhum equipamento logrou elegibilidade de leilão.') . "</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Lotes Fechados -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-light">
                            <h6 class="text-secondary m-0 font-weight-bold"><i class="fas fa-lock mr-2"></i><?php echo __('Lotes Fechados (Aguardando Leilão)'); ?></h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th>ID</th>
                                            <th><?php echo __('Nome do Lote'); ?></th>
                                            <th><?php echo __('Criação'); ?></th>
                                            <th><?php echo __('Itens'); ?></th>
                                            <th><?php echo __('Valor Estimado'); ?></th>
                                            <th class="text-center"><?php echo __('Ações'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $resLotes = mysqli_query($conn, "
                                            SELECT l.*, COUNT(a.id_asset) as qtd, SUM(a.valor) as total_valor 
                                            FROM lotes_leilao l
                                            LEFT JOIN ativos a ON l.id_lote = a.id_lote
                                            WHERE l.status = 'Fechado'
                                            GROUP BY l.id_lote
                                            ORDER BY l.data_criacao DESC
                                        ");
                                        if (mysqli_num_rows($resLotes) > 0):
                                            while($l = mysqli_fetch_assoc($resLotes)):
                                        ?>
                                            <tr>
                                                <td>#<?php echo $l['id_lote']; ?></td>
                                                <td><strong><?php echo htmlspecialchars($l['nome_lote']); ?></strong></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($l['data_criacao'])); ?></td>
                                                <td><?php echo $l['qtd']; ?> <?php echo __('itens'); ?></td>
                                                <td class="text-success font-weight-bold">R$ <?php echo number_format($l['total_valor'] * 0.1, 2, ',', '.'); ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-warning btn-action mr-1" onclick="editLote(<?php echo $l['id_lote']; ?>, '<?php echo addslashes($l['nome_lote']); ?>')" title="<?php echo __('Editar Lote'); ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-primary btn-action" style="background: #2c404a; border: none;" onclick="auctionLote(<?php echo $l['id_lote']; ?>)" title="<?php echo __('Finalizar Leilão'); ?>">
                                                        <i class="fas fa-gavel text-white"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php 
                                            endwhile;
                                        else:
                                            echo "<tr><td colspan='6' class='text-center py-3 text-muted'>" . __('Nenhum lote fechado no momento.') . "</td></tr>";
                                        endif;
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

            </div>
            
            <!-- Modal Editar Lote (Standard System Layout) -->
            <div class="modal fade" id="modalEditarLote" tabindex="-1" role="dialog" aria-hidden="true" style="backdrop-filter: blur(4px);">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                        <div class="modal-header border-0 p-4" style="background: #2c404a; color: white;">
                            <div class="d-flex align-items-center">
                                <div class="bg-white rounded-circle p-2 mr-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-edit text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title font-weight-bold mb-0"><?php echo __('Gerenciar Itens do Lote'); ?></h5>
                                    <small class="text-white-50"><?php echo __('Edite o nome ou remova ativos deste lote.'); ?></small>
                                </div>
                            </div>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-4 bg-light">
                            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                                <div class="card-body">
                                    <label for="editNomeLote" class="font-weight-bold text-dark mb-2"><?php echo __('Nome do Lote'); ?></label>
                                    <div class="input-group">
                                        <input type="text" id="editNomeLote" class="form-control" style="border-radius: 8px 0 0 8px;">
                                        <div class="input-group-append">
                                            <button class="btn btn-success px-4" onclick="renameLote()" style="border-radius: 0 8px 8px 0; font-weight: bold; border: none;">
                                                <?php echo __('Renomear'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                                <div class="card-body p-0">
                                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                        <h6 class="font-weight-bold mb-0 text-dark"><?php echo __('Itens Incluídos'); ?></h6>
                                    </div>
                                    <div class="table-responsive" style="max-height: 350px;">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="border-top-0"><?php echo __('Ativo'); ?></th>
                                                    <th class="border-top-0"><?php echo __('Tag'); ?></th>
                                                    <th class="border-top-0"><?php echo __('Valor'); ?></th>
                                                    <th class="border-top-0 text-center"><?php echo __('Ação'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody id="listItensLote">
                                                <!-- Carregado via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 bg-white d-flex flex-column">
                            <button type="button" class="btn btn-primary btn-block p-3 font-weight-bold shadow-sm mb-2" onclick="reopenLote()" style="border-radius: 12px; background: #f39c12; border: none; color: white;">
                                <i class="fas fa-unlock-alt mr-2"></i><?php echo __('Reabrir Lote para mais adições'); ?>
                            </button>
                            <button type="button" class="btn btn-link text-muted font-weight-bold btn-block m-0" data-dismiss="modal">
                                <?php echo __('Fechar e Voltar'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    Swal.fire({
                        icon: 'warning',
                        title: '<?php echo __('Atenção'); ?>',
                        text: '<?php echo __('Selecione ao menos um ativo para extrair.'); ?>',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }
                $('#exportForm').submit();
            }

            function createLote() {
                Swal.fire({
                    title: '<?php echo __('Gerar Novo Lote'); ?>',
                    input: 'text',
                    inputLabel: '<?php echo __('Nome do Lote'); ?>',
                    inputPlaceholder: '<?php echo __('Ex: Lote TI Abril'); ?>',
                    showCancelButton: true,
                    confirmButtonText: '<?php echo __('Gerar'); ?>',
                    cancelButtonText: '<?php echo __('Cancelar'); ?>'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('ajax_lote.php', { action: 'create', nome_lote: result.value }, function(resp) {
                            if (resp.success) {
                                location.reload();
                            } else {
                                Swal.fire('Erro', resp.message, 'error');
                            }
                        });
                    }
                });
            }

            function addToBatch(id_lote) {
                var selected = [];
                var hasAssigned = false;
                
                $('input[name="lote[]"]:checked').each(function() {
                    selected.push($(this).val());
                    if ($(this).data('status') !== 'Disponível') {
                        hasAssigned = true;
                    }
                });

                if (selected.length === 0) {
                    Swal.fire('Atencão', 'Selecione ao menos um ativo.', 'warning');
                    return;
                }

                if (hasAssigned) {
                    Swal.fire('Erro', 'Todos os ativos devem estar desatribuídos (Disponíveis).', 'error');
                    return;
                }

                $.post('ajax_lote.php', { action: 'add_assets', id_lote: id_lote, assets: selected }, function(resp) {
                    if (resp.success) {
                        Swal.fire('Sucesso', 'Itens adicionados ao lote com sucesso.', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Erro', resp.message, 'error');
                    }
                });
            }

            function closeLote(id_lote) {
                Swal.fire({
                    title: '<?php echo __('Fechar Lote?'); ?>',
                    text: '<?php echo __('Isso impedirá a adição de novos itens a este lote e o deixará pronto para leilão.'); ?>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<?php echo __('Sim, fechar'); ?>'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('ajax_lote.php', { action: 'close', id_lote: id_lote }, function(resp) {
                            if (resp.success) {
                                location.reload();
                            } else {
                                Swal.fire('Erro', resp.message, 'error');
                            }
                        });
                    }
                });
            }

            function auctionLote(id_lote) {
                Swal.fire({
                    title: '<?php echo __('Finalizar Leilão?'); ?>',
                    text: '<?php echo __('Esta ação é definitiva e transferirá todos os itens do lote para o histórico de Venda/Leilão.'); ?>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<?php echo __('Confirmar'); ?>',
                    confirmButtonColor: '#f39c12'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('ajax_lote.php', { action: 'auction', id_lote: id_lote }, function(resp) {
                            if (resp.success) {
                                Swal.fire('Sucesso', 'Lote finalizado e leiloado.', 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Erro', resp.message, 'error');
                            }
                        });
                    }
                });
            }

            let currentEditLoteId = 0;

            function editLote(id, nome) {
                currentEditLoteId = id;
                $('#editNomeLote').val(nome);
                loadLoteItems(id);
                $('#modalEditarLote').modal('show');
            }

            function loadLoteItems(id) {
                $.post('ajax_lote.php', { action: 'get_items', id_lote: id }, function(resp) {
                    if (resp.success) {
                        let html = '';
                        resp.items.forEach(item => {
                            html += `<tr>
                                <td>${item.modelo}</td>
                                <td><span class="badge badge-secondary">${item.tag}</span></td>
                                <td>R$ ${parseFloat(item.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                                <td class="text-center">
                                    <button class="btn btn-outline-danger btn-sm" onclick="removeItemFromBatch(${item.id_asset})" title="Remover do Lote">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>`;
                        });
                        if (resp.items.length === 0) {
                            html = '<tr><td colspan="4" class="text-center py-3">Nenhum item neste lote.</td></tr>';
                        }
                        $('#listItensLote').html(html);
                    }
                });
            }

            function removeItemFromBatch(id_asset) {
                Swal.fire({
                    title: 'Remover Item?',
                    text: 'O item voltará para a lista de ativos elegíveis.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, remover'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('ajax_lote.php', { action: 'remove_item', id_asset: id_asset }, function(resp) {
                            if (resp.success) {
                                loadLoteItems(currentEditLoteId);
                            } else {
                                Swal.fire('Erro', resp.message, 'error');
                            }
                        });
                    }
                });
            }

            function renameLote() {
                const novoNome = $('#editNomeLote').val();
                if (!novoNome) return;

                $.post('ajax_lote.php', { action: 'rename', id_lote: currentEditLoteId, nome_lote: novoNome }, function(resp) {
                    if (resp.success) {
                        location.reload();
                    } else {
                        Swal.fire('Erro', resp.message, 'error');
                    }
                });
            }

            function reopenLote() {
                Swal.fire({
                    title: 'Reabrir Lote?',
                    text: 'O lote voltará a ser o Lote Ativo selecionado no topo da página para que você adicione novos itens.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, reabrir'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('ajax_lote.php', { action: 'reopen', id_lote: currentEditLoteId }, function(resp) {
                            if (resp.success) {
                                location.reload();
                            } else {
                                Swal.fire('Erro', resp.message, 'error');
                            }
                        });
                    }
                });
            }
        </script>
</body>
</html>
