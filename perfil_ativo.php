<?php include 'performance_header.php'; ?>
<?php
/**
 * PERFIL DO ATIVO: perfil_ativo.php
 * Visualização detalhada e prêmium de um equipamento/ativo.
 * Integra lógica de depreciação, doação e histórico.
 */
include_once 'auth.php'; // Proteção de sessão
include_once 'conexao.php'; // Banco de Dados

// Verificação de permissão: Apenas Admin e Suporte podem editar ativos
if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    header("Location: index.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: equipamentos.php");
    exit();
}

// 1. Buscar detalhes do ativo
$sql_ativo = "SELECT a.*, u.nome AS user_nome, u.sobrenome AS user_sobrenome, 
             l.nome_local, l.tipo_local, l.id_parent_local,
             p.tag AS parent_tag, p.modelo AS parent_modelo,
             m.id_manutencao, m.tipo_manutencao, m.categoria_upgrade, m.data_inicio AS manutencao_data, m.observacoes AS manutencao_desc 
             FROM ativos a 
             LEFT JOIN usuarios u ON a.assigned_to = u.id_usuarios
             LEFT JOIN locais l ON a.id_local = l.id_local
             LEFT JOIN ativos p ON a.parent_asset_id = p.id_asset
             LEFT JOIN manutencao m ON a.id_asset = m.id_asset AND m.status_manutencao = 'Em Manutenção'
             WHERE a.id_asset = '$id'";
$result_ativo = mysqli_query($conn, $sql_ativo);
$ativo = mysqli_fetch_assoc($result_ativo);

if (!$ativo) {
    header("Location: equipamentos.php");
    exit();
}

// 1.5 Calculando dados para QR Code (global)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']);
$path = ($path == '/' || $path == '\\') ? '' : $path;
$base_url = $protocol . $host . $path;
$qr_data = $base_url . "/v.php?id=" . $id;

// 2. Lógica de Depreciação e Doação (Migrada de detalhes_do_equipamento.php)
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

$data_ativacao = new DateTime($ativo['dataAtivacao']);
$data_atual = new DateTime();
$diff = $data_ativacao->diff($data_atual);

$valor_original = floatval($ativo['valor']);
$taxa_pct = floatval($dep_config['taxa_depreciacao']);
$periodo_total_meses = (intval($dep_config['periodo_anos']) * 12) + intval($dep_config['periodo_meses']);

if ($periodo_total_meses > 0 && $valor_original > 0) {
    $meses_ativos = ($diff->y * 12) + $diff->m;
    $periodos_completos = floor($meses_ativos / $periodo_total_meses);
    $depreciacao_total = min($valor_original, $valor_original * ($taxa_pct / 100) * $periodos_completos);
    $valor_atual = max(0, $valor_original - $depreciacao_total);
    $percentual_depreciado = min(100, round(($depreciacao_total / $valor_original) * 100, 1));
} else {
    $depreciacao_total = 0;
    $valor_atual = $valor_original;
    $percentual_depreciado = 0;
}

// Elegibilidade para doação
$doacao_habilitada = intval($dep_config['elegivel_doacao']);
$tempo_min_doacao_meses = (intval($dep_config['tempo_doacao_anos']) * 12) + intval($dep_config['tempo_doacao_meses']);
$meses_desde_cadastro = ($diff->y * 12) + $diff->m;

$categoria_ativo = $ativo['categoria'];
$cat_elegivel = 1;
$result_cat_eleg = mysqli_query($conn, "SELECT elegivel_doacao FROM categoria_doacao WHERE categoria = '" . mysqli_real_escape_string($conn, $categoria_ativo) . "' LIMIT 1");
if ($result_cat_eleg && mysqli_num_rows($result_cat_eleg) > 0) {
    $row_cat_eleg = mysqli_fetch_assoc($result_cat_eleg);
    $cat_elegivel = intval($row_cat_eleg['elegivel_doacao']);
}

if (!$doacao_habilitada) {
    $status_doacao = __('Doação Desativada');
    $cor_doacao = "text-secondary";
} elseif (!$cat_elegivel) {
    $status_doacao = __('Categoria não elegível');
    $cor_doacao = "text-warning";
} elseif (empty($ativo['assigned_to'])) {
    $status_doacao = __('Aguardando Atribuição');
    $cor_doacao = "text-warning";
} elseif ($meses_desde_cadastro >= $tempo_min_doacao_meses) {
    $status_doacao = __('Elegível para Doação');
    $cor_doacao = "text-success";
} else {
    $restante_meses = $tempo_min_doacao_meses - $meses_desde_cadastro;
    $status_doacao = __('Bloqueado (Carência)');
    $cor_doacao = "text-danger";
}

// 3. Função para buscar o path do local de forma recursiva
function getLocalPath($conn, $id_local)
{
    $sql = "SELECT id_local, nome_local, id_parent_local FROM locais WHERE id_local = $id_local";
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        $parent = $row['id_parent_local'] ? getLocalPath($conn, $row['id_parent_local']) . ' > ' : '';
        return $parent . $row['nome_local'];
    }
    return '';
}

// 4. Buscar ativos filhos (componentes vinculados)
$sql_filhos = "SELECT id_asset, tag, modelo, categoria, status FROM ativos WHERE parent_asset_id = $id";
$res_filhos = $conn->query($sql_filhos);

// 3. UI Helpers
$display_status = $ativo['status'];
$is_assigned = (!empty($ativo['assigned_to']) || !empty($ativo['id_local']));

if ($display_status !== 'Em manutenção' && $is_assigned) {
    $display_status = 'Em uso';
}

$status_class = 'secondary';
if (in_array($display_status, ['Ativo', 'Disponível', 'Disponivel'])) {
    $status_class = 'success';
} elseif ($display_status === 'Em uso') {
    $status_class = 'primary';
} elseif (in_array($display_status, ['Em manutenção', 'Manutenção', 'Manutencao'])) {
    $status_class = 'warning';
} else {
    $status_class = 'danger';
}
$raw_status = $display_status; // Keep for backward compatibility if needed
$foto = !empty($ativo['imagem']) ? htmlspecialchars($ativo['imagem']) : '/assets/img/no-image.png';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Perfil do Ativo'); ?> - Asset MGT</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=3265483e434712d72c41db9eebc4c8bb">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=d6a29779d310462e7fcdde7b9a80e0db">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=5f41e73f827c7b56616237a1da13b6e2">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <?php renderPerformanceHints(); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // --- FUNÇÃO DE IMPRESSÃO (DEFINIDA NO TOPO PARA SEGURANÇA) ---
        function printAssetTag() {
            const assetId = <?php echo $id; ?>;
            const printUrl = `imprimir_etiqueta.php?id=${assetId}`;

            let printFrame = document.getElementById('print_iframe');
            if (!printFrame) {
                printFrame = document.createElement('iframe');
                printFrame.id = 'print_iframe';
                printFrame.style.display = 'none';
                document.body.appendChild(printFrame);
            }

            const btn = document.querySelector('button[onclick="printAssetTag()"]');
            if (btn) {
                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> ' + <?php echo json_encode(__('Gerando...')); ?>;
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }, 3000);
            }

            printFrame.src = printUrl;
        }

        document.addEventListener("DOMContentLoaded", function () {
            const qrContainer = document.getElementById("qrcode");
            if (qrContainer) {
                new QRCode(qrContainer, {
                    text: <?php echo json_encode($qr_data); ?>,
                    width: 150,
                    height: 150,
                    colorDark: "#2c404a",
                    colorLight: "#ffffff",
                    correctLevel: (typeof QRCode !== 'undefined' && QRCode.CorrectLevel) ? QRCode.CorrectLevel.H : 2
                });
                setTimeout(() => {
                    const qrImg = qrContainer.querySelector('img');
                    if (qrImg) qrImg.classList.add('img-fluid');
                }, 500);
            }
        });
    </script>
    <?php include_once 'sidebar_style.php'; ?>
    <style>
        .profile-card {
            border-radius: 15px;
            overflow: hidden;
            border: none;
        }

        .profile-header {
            background: linear-gradient(135deg, #2c404a 0%, #3e5b69 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .profile-img-container {
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
            border: 5px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            overflow: hidden;
            background: white;
        }

        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #858796;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .detail-value {
            font-size: 1.1rem;
            color: #2c404a;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .info-card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .btn-action {
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .card-shadow {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        .financial-summary {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 15px;
            margin-top: 10px;
            border-left: 4px solid #4e73df;
        }
    </style>
</head>

<body id="page-top">
    <?php startNProgress(); ?>
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include_once 'sidebar_brand.php'; ?>
                <?php include_once 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column premium-page-fade" id="content-wrapper">
            <div id="content">
                <?php include_once 'topbar.php'; ?>
                <div class="container-fluid">
                    <div
                        class="d-sm-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
                        <h3 class="text-dark mb-0"><?php echo __('Perfil do Ativo'); ?></h3>
                        <div class="d-flex gap-2" style="gap: 10px;">
                            <button class="btn btn-danger btn-sm" onclick="gerarPDF()">
                                <i class="fas fa-file-pdf mr-1"></i><?php echo __('PDF'); ?>
                            </button>
                            <a class="btn btn-dark btn-sm" href="equipamentos.php">
                                <i class="fas fa-arrow-left fa-sm mr-1"></i><?php echo __('Voltar'); ?>
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 animate__animated animate__fadeInLeft" style="animation-delay: 0.1s;">
                            <!-- Header Card -->
                            <div class="card shadow card-shadow profile-card mb-4">
                                <div class="profile-header">
                                    <div class="profile-img-container shadow">
                                        <img src="<?php echo $foto; ?>" class="profile-img"
                                            alt="<?php echo htmlspecialchars($ativo['modelo']); ?>">
                                    </div>
                                    <h4 class="font-weight-bold mb-1"><?php echo htmlspecialchars($ativo['modelo']); ?>
                                    </h4>
                                    <p class="mb-3 opacity-75"><?php echo htmlspecialchars($ativo['tag']); ?></p>
                                    <span class="status-badge badge-<?php echo $status_class; ?>">
                                        <?php echo __(ucfirst($ativo['status'])); ?>
                                    </span>

                                    <?php if ($ativo['status'] == 'Em manutenção'): ?>
                                        <div class="mt-3 p-2 bg-warning text-dark text-center font-weight-bold rounded shadow-sm border border-dark"
                                            style="font-size: 1.1rem; letter-spacing: 1px;">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            <?php echo __('EM MANUTENÇÃO'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body bg-light">
                                    <div class="detail-label text-center"><?php echo __('Atribuído a'); ?></div>
                                    <div class="detail-value text-center mb-0">
                                        <?php if (!empty($ativo['assigned_to'])): ?>
                                            <a href="perfil_usuario.php?id=<?php echo $ativo['assigned_to']; ?>"
                                                class="font-weight-bold text-primary">
                                                <i
                                                    class="fas fa-user mr-1"></i><?php echo htmlspecialchars($ativo['user_nome'] . ' ' . $ativo['user_sobrenome']); ?>
                                            </a>
                                        <?php elseif (!empty($ativo['id_local'])): ?>
                                            <a href="locais.php" class="font-weight-bold text-success">
                                                <i
                                                    class="fas fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars($ativo['nome_local']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?php echo __('Disponível'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Identificação Visual (QR Code) -->
                            <div class="info-card shadow card-shadow mb-4 text-center">
                                <h6 class="font-weight-bold text-primary mb-3"><?php echo __('Identificação Visual'); ?>
                                </h6>
                                <div class="p-2 bg-white d-inline-block rounded shadow-sm mb-3">
                                    <div id="qrcode"></div>
                                </div>
                                <div class="mt-0">
                                    <span class="badge badge-dark p-2" id="tag_badge_code"
                                        style="font-size: 1rem; border-radius: 8px; letter-spacing: 1px;">
                                        <?php echo htmlspecialchars($ativo['tag']); ?>
                                    </span>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-outline-primary btn-sm btn-block" onclick="printAssetTag()"
                                        style="border-radius: 8px;">
                                        <i class="fas fa-print mr-1"></i><?php echo __('Imprimir Etiqueta'); ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Ações -->
                            <div class="info-card shadow card-shadow mb-4 text-center">
                                <h6 class="font-weight-bold text-primary mb-3"><?php echo __('Ações Rápidas'); ?></h6>
                                <div class="d-flex flex-column gap-2" style="gap: 10px;">
                                    <a href="editar_ativo.php?id=<?php echo $id; ?>" class="btn btn-warning btn-action">
                                        <i class="fas fa-edit mr-2"></i><?php echo __('Editar Ativo'); ?>
                                    </a>

                                    <?php if (!empty($ativo['assigned_to']) || !empty($ativo['id_local'])): ?>
                                        <button class="btn btn-danger btn-action"
                                            onclick="unassignUser(<?php echo $id; ?>)">
                                            <i class="fas fa-minus-circle mr-2"></i><?php echo __('Liberar Ativo'); ?>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-success btn-action"
                                            onclick="openAssignModal(<?php echo $id; ?>)">
                                            <i
                                                class="fas fa-plus-circle mr-2"></i><?php echo __('Atribuir Responsável / Local'); ?>
                                        </button>
                                    <?php endif; ?>

                                    <?php if (empty($ativo['manutencao_data'])): ?>
                                        <button class="btn btn-info btn-action" style="background: #2c404a; border: none;"
                                            onclick="sendToMaintenance(<?php echo $id; ?>)">
                                            <i class="fas fa-tools mr-2"></i><?php echo __('Enviar Manutenção'); ?>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-success btn-action"
                                            onclick="releaseFromMaintenance(<?php echo $id; ?>)">
                                            <i class="fas fa-check-circle mr-2"></i><?php echo __('Liberar Manutenção'); ?>
                                        </button>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <!-- Especificações Técnicas -->
                            <div class="info-card shadow card-shadow animate__animated animate__fadeInRight"
                                style="animation-delay: 0.2s;">
                                <h6 class="font-weight-bold text-primary mb-4">
                                    <i class="fas fa-microchip mr-2"></i><?php echo __('Especificações Técnicas'); ?>
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="detail-label"><?php echo __('Fabricante'); ?></div>
                                        <div class="detail-value"><?php echo htmlspecialchars($ativo['fabricante'] ?: '-'); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="detail-label"><?php echo __('Categoria'); ?></div>
                                        <div class="detail-value"><span class="badge badge-light border px-2 py-1" style="background: #eef2f7; color: #2c404a; font-weight: 600; font-size: 0.8rem; text-transform: uppercase;"><?php echo htmlspecialchars($ativo['categoria']); ?></span></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="detail-label"><?php echo __('Hostname'); ?></div>
                                        <div class="detail-value text-dark font-weight-bold"><?php echo htmlspecialchars($ativo['hostName'] ?: '-'); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="detail-label"><?php echo __('Número de Série'); ?></div>
                                        <div class="detail-value text-dark font-weight-bold"><?php echo htmlspecialchars($ativo['numero_serie'] ?: '-'); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="detail-label"><?php echo __('Endereço MAC'); ?></div>
                                        <div class="detail-value"><?php echo htmlspecialchars($ativo['macAdress'] ?: '-'); ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="detail-label"><?php echo __('Service Tag / Certificado'); ?></div>
                                        <div class="detail-value"><?php echo htmlspecialchars($ativo['tag'] ?: '-'); ?></div>
                                    </div>
                                    
                                    <?php if (!empty($ativo['processador'])): ?>
                                    <div class="col-md-4">
                                        <div class="detail-label"><?php echo __('Processador'); ?></div>
                                        <div class="detail-value text-dark"><i class="fas fa-microchip mr-1 small text-muted"></i><?php echo htmlspecialchars($ativo['processador']); ?></div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($ativo['memoria'])): ?>
                                    <div class="col-md-4">
                                        <div class="detail-label"><?php echo __('Memória RAM'); ?></div>
                                        <div class="detail-value text-dark"><i class="fas fa-memory mr-1 small text-muted"></i><?php echo htmlspecialchars($ativo['memoria']); ?></div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($ativo['armazenamento'])): ?>
                                    <div class="col-md-4">
                                        <div class="detail-label"><?php echo __('Armazenamento'); ?></div>
                                        <div class="detail-value text-dark"><i class="fas fa-hdd mr-1 small text-muted"></i><?php echo htmlspecialchars($ativo['armazenamento']); ?> (<?php echo htmlspecialchars($ativo['tipo_armazenamento'] ?: 'SSD'); ?>)</div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($ativo['categoria'] == 'Monitor' && !empty($ativo['polegadas'])): ?>
                                    <div class="col-md-4">
                                        <div class="detail-label"><?php echo __('Tamanho da Tela'); ?></div>
                                        <div class="detail-value text-dark"><i class="fas fa-desktop mr-1 small text-muted"></i><?php echo htmlspecialchars($ativo['polegadas']); ?></div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($ativo['categoria'] == 'Smartphone'): ?>
                                        <?php if (!empty($ativo['imei'])): ?>
                                        <div class="col-md-4">
                                            <div class="detail-label"><?php echo __('IMEI'); ?></div>
                                            <div class="detail-value text-dark"><i class="fas fa-barcode mr-1 small text-muted"></i><?php echo htmlspecialchars($ativo['imei']); ?></div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($ativo['sim_card'])): ?>
                                        <div class="col-md-4">
                                            <div class="detail-label"><?php echo __('SIM Card'); ?></div>
                                            <div class="detail-value text-dark"><i class="fas fa-sim-card mr-1 small text-muted"></i><?php echo htmlspecialchars($ativo['sim_card']); ?></div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($ativo['categoria'] == 'Impressora' && !empty($ativo['is_scanner'])): ?>
                                    <div class="col-md-4">
                                        <div class="detail-label"><?php echo __('Tipo de Impressora'); ?></div>
                                        <div class="detail-value text-dark"><i class="fas fa-print mr-1 small text-muted"></i><?php echo ($ativo['is_scanner'] == 'Sim') ? __('Multifuncional') : __('Simples'); ?></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Nova Seção: Atribuição e Localização -->
                            <div class="info-card shadow card-shadow animate__animated animate__fadeInRight"
                                style="animation-delay: 0.22s;">
                                <h6 class="text-primary font-weight-bold mb-4"><i
                                        class="fas fa-map-marker-alt mr-2"></i><?php echo __('Responsabilidade e Localização'); ?>
                                </h6>
                                <div class="p-3 bg-light rounded border">
                                    <?php if (!empty($ativo['user_nome']) || !empty($ativo['id_local'])): ?>
                                            <?php if (!empty($ativo['user_nome'])): ?>
                                                    <p class="mb-1"><strong><?php echo __('Tipo:'); ?></strong> <span
                                                            class="badge badge-primary"><?php echo __('Responsabilidade Individual'); ?></span>
                                                    </p>
                                                    <p class="<?php echo !empty($ativo['id_local']) ? 'mb-2' : 'mb-0'; ?>">
                                                        <strong><?php echo __('Usuário:'); ?></strong>
                                                        <a
                                                            href="perfil_usuario.php?id=<?php echo $ativo['assigned_to']; ?>"><?php echo htmlspecialchars($ativo['user_nome'] . ' ' . $ativo['user_sobrenome']); ?></a>
                                                    </p>
                                            <?php endif; ?>

                                            <?php if (!empty($ativo['id_local'])): ?>
                                                    <?php if (!empty($ativo['user_nome']))
                                                        echo '<hr class="my-2">'; ?>
                                                    <p class="mb-1"><strong><?php echo __('Tipo:'); ?></strong> <span
                                                            class="badge badge-success"><?php echo __('Responsabilidade Coletiva / Local'); ?></span>
                                                    </p>
                                                    <p class="mb-0"><strong><?php echo __('Local:'); ?></strong>
                                                        <span
                                                            class="text-dark"><?php echo getLocalPath($conn, $ativo['id_local']); ?></span>
                                                    </p>
                                            <?php endif; ?>
                                    <?php else: ?>
                                            <p class="mb-0 text-center py-2">
                                                <span
                                                    class="badge badge-secondary"><?php echo __('Disponível / Estoque'); ?></span>
                                            </p>
                                    <?php endif; ?>

                                    <?php if (!empty($ativo['parent_asset_id'])): ?>
                                            <hr class="my-2">
                                            <p class="mb-0"><strong><?php echo __('Vínculo:'); ?></strong>
                                                <?php echo __('Este ativo faz parte de '); ?>
                                                <a href="perfil_ativo.php?id=<?php echo $ativo['parent_asset_id']; ?>">
                                                    <strong><?php echo $ativo['parent_tag']; ?></strong>
                                                    (<?php echo $ativo['parent_modelo']; ?>)
                                                </a>
                                            </p>
                                    <?php endif; ?>
                                </div>
                            </div>



                            <!-- Financeiro e Vida Útil (Values and Status) -->
                            <div class="info-card shadow card-shadow animate__animated animate__fadeInRight"
                                style="animation-delay: 0.3s;">
                                <h6 class="font-weight-bold text-primary mb-4">
                                    <i class="fas fa-chart-line mr-2"></i><?php echo __('Valores e Status'); ?>
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="detail-label"><?php echo __('Data de Cadastro'); ?></div>
                                        <div class="detail-value">
                                            <?php 
                                            // Prevenção de erro 30/11/-0001
                                            $data_cad = $ativo['dataAtivacao'];
                                            if ($data_cad && $data_cad != '0000-00-00' && $data_cad != '1970-01-01' && strtotime($data_cad) > 0) {
                                                echo date('d/m/Y', strtotime($data_cad)); 
                                            } else {
                                                echo '<span class="text-muted italic small">' . __('Não informada') . '</span>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="detail-label"><?php echo __('Centro de Custo'); ?></div>
                                        <div class="detail-value text-primary font-weight-bold">
                                            <?php
                                            $cc_name = $ativo['centroDeCusto'];
                                            if (!empty($cc_name)) {
                                                $res_cc = mysqli_query($conn, "SELECT id_centro_de_custo FROM centro_de_custo WHERE nomeSetor = '" . mysqli_real_escape_string($conn, $cc_name) . "' LIMIT 1");
                                                if ($res_cc && $row_cc = mysqli_fetch_assoc($res_cc)) {
                                                    echo '<a href="perfil_centro_de_custo.php?id=' . $row_cc['id_centro_de_custo'] . '">' . htmlspecialchars($cc_name) . '</a>';
                                                } else {
                                                    echo htmlspecialchars($cc_name);
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="detail-label"><?php echo __('Nível de Atribuição'); ?></div>
                                        <div class="detail-value font-weight-bold">
                                            <?php
                                            if (!empty($ativo['tier'])) {
                                                echo '<span class="badge badge-dark py-1 px-2" style="background: #2c404a;">' . htmlspecialchars($ativo['tier']) . '</span>';
                                                if (!empty($ativo['setor'])) {
                                                    echo '<span class="ml-2 text-muted" style="font-size: 0.9rem;">(' . htmlspecialchars($ativo['setor']) . ')</span>';
                                                }
                                            } else {
                                                echo htmlspecialchars($ativo['setor'] ?: '-');
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Fornecedor'); ?></div>
                                        <div class="detail-value text-primary font-weight-bold">
                                            <?php
                                            if (!empty($ativo['fornecedor'])) {
                                                // Tenta buscar o ID do fornecedor para linkar
                                                $fname = mysqli_real_escape_string($conn, $ativo['fornecedor']);
                                                $res_f = mysqli_query($conn, "SELECT id_fornecedor FROM fornecedor WHERE nomeEmpresa = '$fname' LIMIT 1");
                                                if ($res_f && $row_f = mysqli_fetch_assoc($res_f)) {
                                                    echo '<a href="perfil_fornecedor.php?id=' . $row_f['id_fornecedor'] . '">' . htmlspecialchars($ativo['fornecedor']) . '</a>';
                                                } else {
                                                    echo htmlspecialchars($ativo['fornecedor']);
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Valor Original'); ?></div>
                                        <div class="detail-value">R$
                                            <?php echo number_format($valor_original, 2, ',', '.'); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Valor Atual'); ?></div>
                                        <div class="detail-value text-success font-weight-bold">R$
                                            <?php echo number_format($valor_atual, 2, ',', '.'); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Taxa de Depreciação'); ?></div>
                                        <div class="detail-value">
                                            <?php echo number_format($taxa_pct, 2, ',', '.'); ?>%
                                            <small class="text-muted"><?php echo __('a cada'); ?>
                                                <?php echo intval($dep_config['periodo_anos']); ?>
                                                <?php echo __('ano(s)'); ?> <?php echo __('e'); ?>
                                                <?php echo intval($dep_config['periodo_meses']); ?>
                                                <?php echo __('mês(es)'); ?></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Nota Fiscal/Chave de Acesso'); ?></div>
                                        <div class="detail-value" style="word-break: break-all;">
                                            <span
                                                id="nfNumber"><?php echo htmlspecialchars($ativo['numero_nota_fiscal'] ?? '-'); ?></span>
                                            <?php if (!empty($ativo['numero_nota_fiscal'])): ?>
                                                    <button onclick="copyNF('<?php echo $ativo['numero_nota_fiscal']; ?>')"
                                                        class="btn btn-sm btn-link text-primary p-0 ml-2"
                                                        title="<?php echo __('Copiar Chave'); ?>">
                                                        <i id="copyIcon" class="fas fa-copy"></i>
                                                    </button>
                                            <?php endif; ?>
                                            <?php if (!empty($ativo['anexo_nota_fiscal'])): ?>
                                                    <a href="<?php echo htmlspecialchars($ativo['anexo_nota_fiscal']); ?>"
                                                        target="_blank" class="badge badge-primary ml-2">
                                                        <i class="fas fa-download mr-1"></i><?php echo __('Anexo'); ?>
                                                    </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Depreciação Acumulada'); ?></div>
                                        <div class="detail-value text-danger font-weight-bold">
                                            R$ <?php echo number_format($depreciacao_total, 2, ',', '.'); ?>
                                            <span
                                                class="badge badge-danger ml-1">(<?php echo number_format($percentual_depreciado, 1, ',', '.'); ?>%)</span>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <div class="detail-label"><?php echo __('Status de Doação'); ?></div>
                                        <div class="detail-value py-2 px-3 rounded <?php echo strpos($cor_doacao, 'success') !== false ? 'bg-light-success' : (strpos($cor_doacao, 'danger') !== false ? 'bg-light-danger' : 'bg-light'); ?>"
                                            style="border-left: 4px solid currentColor; display: flex; justify-content: space-between; align-items: center;">
                                            <span
                                                class="font-weight-bold <?php echo $cor_doacao; ?>"><?php echo $status_doacao; ?></span>
                                            <?php if ($status_doacao === __('Elegível para Doação')): ?>
                                                    <button class="btn btn-success btn-sm px-3"
                                                        onclick="sellAsset(<?php echo $id; ?>)"
                                                        style="border-radius: 20px; font-weight: 600;">
                                                        <i class="fas fa-hand-holding-heart mr-1"></i><?php echo __('Doar'); ?>
                                                    </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Observações Adicionais -->
                            <?php if (!empty($ativo['descricao'])): ?>
                                    <div class="info-card shadow card-shadow">
                                        <h6 class="font-weight-bold text-primary mb-3">
                                            <i class="fas fa-sticky-note mr-2"></i><?php echo __('Observações Adicionais'); ?>
                                        </h6>
                                        <div class="p-3 bg-light rounded shadow-sm">
                                            <?php echo nl2br(htmlspecialchars($ativo['descricao'])); ?>
                                        </div>
                                    </div>
                            <?php endif; ?>

                            <!-- Linha do Tempo Técnica (Upgrades e Reparos Concluídos) -->
                            <?php
                            $sql_tecnico = "SELECT data_fim, tipo_manutencao, categoria_upgrade, item_trocado, detalhes_update, observacoes 
                                             FROM manutencao 
                                             WHERE id_asset = ? AND (tipo_manutencao = 'Upgrade' OR tipo_manutencao = 'Reparo') AND status_manutencao = 'Concluído'
                                             ORDER BY data_fim DESC";
                            $stmt_tec = $conn->prepare($sql_tecnico);
                            $stmt_tec->bind_param('i', $id);
                            $stmt_tec->execute();
                            $res_tec = $stmt_tec->get_result();

                            if ($res_tec->num_rows > 0):
                                ?>
                                    <div class="info-card shadow card-shadow border-left-primary">
                                        <h6 class="font-weight-bold text-primary mb-3">
                                            <i class="fas fa-history mr-2"></i><?php echo __('Linha do Tempo Técnica'); ?>
                                        </h6>
                                        <div class="row">
                                            <?php while ($tec = $res_tec->fetch_assoc()):
                                                $is_upgrade = ($tec['tipo_manutencao'] === 'Upgrade');
                                                $badge_class = $is_upgrade ? 'badge-primary' : 'badge-warning';
                                                $border_inner = $is_upgrade ? 'border-left-primary' : 'border-left-warning';
                                                $icon_inner = $is_upgrade ? 'fa-arrow-up' : 'fa-wrench';
                                                $title_text = $is_upgrade ? $tec['categoria_upgrade'] : __('Reparo Finalizado');
                                                $main_info = $is_upgrade ? $tec['item_trocado'] : $tec['observacoes'];
                                                ?>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="p-3 bg-light rounded shadow-sm <?php echo $border_inner; ?>"
                                                            style="border-left-width: 4px; transition: all 0.3s ease;">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <span class="badge <?php echo $badge_class; ?> px-2 py-1"
                                                                    style="border-radius: 6px; font-size: 0.75rem;">
                                                                    <i
                                                                        class="fas <?php echo $icon_inner; ?> mr-1"></i><?php echo htmlspecialchars($title_text); ?>
                                                                </span>
                                                                <small class="text-muted font-weight-bold">
                                                                    <i
                                                                        class="far fa-calendar-alt mr-1"></i><?php echo date('d/m/Y', strtotime($tec['data_fim'])); ?>
                                                                </small>
                                                            </div>
                                                            <div class="text-dark font-weight-bold" style="font-size: 0.9rem;">
                                                                <?php echo htmlspecialchars($main_info); ?>
                                                                <?php if ($is_upgrade && !empty($tec['detalhes_update'])): ?>
                                                                        <span class="text-muted font-weight-normal small"> -
                                                                            <?php echo htmlspecialchars($tec['detalhes_update']); ?></span>
                                                                <?php endif; ?>
                                                                <?php if (!$is_upgrade && !empty($tec['item_trocado'])): ?>
                                                                        <div class="text-muted font-weight-normal smallest mt-1">
                                                                            <i class="fas fa-microchip mr-1"></i><?php echo __('Peças'); ?>:
                                                                            <?php echo htmlspecialchars($tec['item_trocado']); ?>
                                                                        </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                            <?php endif;
                            $stmt_tec->close(); ?>

                            <!-- Manutenção (Se houver) -->
                            <?php if ($ativo['manutencao_desc'] || isset($ativo['tipo_manutencao'])): ?>
                                    <div class="info-card shadow card-shadow border-left-warning">
                                        <h6 class="font-weight-bold text-warning mb-3">
                                            <i class="fas fa-tools mr-2"></i><?php echo __('Detalhes da Manutenção'); ?>
                                        </h6>
                                        <p class="mb-2 text-muted small"><?php echo __('Início em'); ?>:
                                            <?php echo date('d/m/Y H:i', strtotime($ativo['manutencao_data'])); ?></p>

                                        <?php
                                        // Buscar dados estendidos da manutenção
                                        $stmt_ext = $conn->prepare("SELECT tipo_manutencao, categoria_upgrade, item_trocado, detalhes_update, valor_upgrade, observacoes FROM manutencao WHERE id_asset = ? AND status_manutencao = 'Em Manutenção' LIMIT 1");
                                        $stmt_ext->bind_param('i', $id);
                                        $stmt_ext->execute();
                                        $res_ext = $stmt_ext->get_result();
                                        if ($row_ext = $res_ext->fetch_assoc()):
                                            $is_upgrade = ($row_ext['tipo_manutencao'] === 'Upgrade');
                                            ?>
                                                <div class="mb-3">
                                                    <?php
                                                    $badge_class = 'badge-warning';
                                                    $icon = 'fa-wrench';
                                                    if ($row_ext['tipo_manutencao'] === 'Upgrade') {
                                                        $badge_class = 'badge-primary';
                                                        $icon = 'fa-arrow-up';
                                                    } elseif ($row_ext['tipo_manutencao'] === 'Insumo') {
                                                        $badge_class = 'badge-success';
                                                        $icon = 'fa-fill-drip';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?> p-2" style="border-radius: 6px;">
                                                        <i class="fas <?php echo $icon; ?> mr-1"></i>
                                                        <?php echo __($row_ext['tipo_manutencao']); ?>
                                                    </span>
                                                </div>

                                                <?php if (!empty($row_ext['observacoes'])): ?>
                                                        <div class="detail-label small"><?php echo __('Descrição'); ?></div>
                                                        <div class="p-2 bg-light rounded small mb-2">
                                                            <?php echo nl2br(htmlspecialchars($row_ext['observacoes'])); ?></div>
                                                <?php endif; ?>

                                                <?php if ($row_ext['tipo_manutencao'] === 'Upgrade'): ?>
                                                        <div class="detail-label small"><?php echo __('Categoria'); ?></div>
                                                        <div class="p-2 bg-light rounded small mb-2 font-weight-bold">
                                                            <?php echo htmlspecialchars($row_ext['categoria_upgrade']); ?></div>

                                                        <?php if ($row_ext['categoria_upgrade'] === 'Memória'): ?>
                                                                <div class="detail-label small"><?php echo __('Módulo Instalado'); ?></div>
                                                                <div class="p-2 bg-light rounded small mb-2 text-primary">
                                                                    <?php echo htmlspecialchars($row_ext['item_trocado']); ?></div>
                                                        <?php elseif ($row_ext['categoria_upgrade'] === 'Armazenamento'): ?>
                                                                <div class="detail-label small"><?php echo __('Tipo de Disco'); ?></div>
                                                                <div class="p-2 bg-light rounded small mb-2 text-primary">
                                                                    <?php echo htmlspecialchars($row_ext['detalhes_update']); ?></div>
                                                        <?php endif; ?>

                                                        <?php if ($row_ext['valor_upgrade'] > 0): ?>
                                                                <div class="detail-label small"><?php echo __('Investimento'); ?></div>
                                                                <div class="p-2 bg-light rounded small mb-2 text-success font-weight-bold">R$
                                                                    <?php echo number_format($row_ext['valor_upgrade'], 2, ',', '.'); ?></div>
                                                        <?php endif; ?>
                                                <?php elseif ($row_ext['tipo_manutencao'] === 'Insumo'): ?>
                                                        <div class="detail-label small"><?php echo __('Suprimento'); ?></div>
                                                        <div class="p-2 bg-light rounded small mb-2 text-success font-weight-bold">
                                                            <i
                                                                class="fas fa-box-open mr-1"></i><?php echo htmlspecialchars($row_ext['item_trocado']); ?>
                                                        </div>
                                                <?php else: ?>
                                                        <?php if (!empty($row_ext['item_trocado'])): ?>
                                                                <div class="detail-label small"><?php echo __('Itens Trocados'); ?></div>
                                                                <div class="p-2 bg-light rounded small mb-2 border-left-info">
                                                                    <?php echo nl2br(htmlspecialchars($row_ext['item_trocado'])); ?></div>
                                                        <?php endif; ?>
                                                <?php endif; ?>

                                                <!-- Botão de Conclusão Dinâmico -->
                                                <div class="mt-4 pt-3 border-top">
                                                    <button class="btn btn-success btn-block font-weight-bold"
                                                        onclick="releaseMaintenance(<?php echo $id; ?>)"
                                                        style="border-radius: 12px; padding: 12px; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);">
                                                        <i class="fas fa-check-double mr-2"></i>
                                                        <?php
                                                        $btn_label = __('Concluir Manutenção');
                                                        if ($row_ext['tipo_manutencao'] === 'Reparo')
                                                            $btn_label = __('Concluir Reparo');
                                                        elseif ($row_ext['tipo_manutencao'] === 'Upgrade')
                                                            $btn_label = __('Concluir Upgrade');
                                                        elseif ($row_ext['tipo_manutencao'] === 'Insumo')
                                                            $btn_label = __('Concluir Insumo');
                                                        echo $btn_label;
                                                        ?>
                                                    </button>
                                                </div>
                                        <?php endif; ?>
                                    </div>
                            <?php endif; ?>

                            <!-- Histórico -->
                            <div class="info-card shadow card-shadow">
                                <h6 class="font-weight-bold text-primary mb-4">
                                    <i class="fas fa-history mr-2"></i><?php echo __('Histórico de Eventos'); ?>
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th><?php echo __('Data'); ?></th>
                                                <th><?php echo __('Ação'); ?></th>
                                                <th><?php echo __('Detalhes'); ?></th>
                                                <th><?php echo __('Responsável'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql_hist = "SELECT h.*, u.nome AS user_nome, u.sobrenome AS user_sobrenome 
                                                         FROM historico_ativos h 
                                                         LEFT JOIN usuarios u ON h.usuario_id = u.id_usuarios 
                                                         WHERE h.ativo_id = $id 
                                                         ORDER BY h.data_evento DESC";
                                            $res_hist = $conn->query($sql_hist);
                                            $has_creation = false;
                                            $history_rows = [];
                                            while ($h = $res_hist->fetch_assoc()) {
                                                if ($h['acao'] === 'Criação')
                                                    $has_creation = true;
                                                $history_rows[] = $h;
                                            }

                                            // Se não existe evento de criação, adicionamos um virtual no final
                                            if (!$has_creation) {
                                                $history_rows[] = [
                                                    'data_evento' => $ativo['dataAtivacao'],
                                                    'acao' => 'Criação',
                                                    'detalhes' => 'Ativo registrado no sistema (data de ativação).',
                                                    'user_nome' => __('Sistema')
                                                ];
                                            }

                                            foreach ($history_rows as $h):
                                                ?>
                                                    <tr>
                                                        <td class="text-nowrap">
                                                            <?php echo date('d/m/Y H:i', strtotime($h['data_evento'])); ?></td>
                                                        <td class="font-weight-bold"><?php echo __($h['acao']); ?></td>
                                                        <td><?php echo __($h['detalhes']); ?></td>
                                                        <td><?php echo htmlspecialchars(trim(($h['user_nome'] ?? '') . ' ' . ($h['user_sobrenome'] ?? '')) ?: __('Sistema')); ?>
                                                        </td>
                                                    </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Coluna Direita: Componentes Vinculados e Outros -->
                        <div class="col-lg-4 animate__animated animate__fadeInRight" style="animation-delay: 0.35s;">
                            <?php if ($res_filhos->num_rows > 0): ?>
                                    <div class="card shadow mb-4">
                                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                            <h6 class="text-primary font-weight-bold m-0">
                                                <?php echo __('Componentes / Ativos Vinculados'); ?></h6>
                                            <span class="badge badge-info"><?php echo $res_filhos->num_rows; ?></span>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead class="bg-light small">
                                                        <tr>
                                                            <th><?php echo __('Ativo'); ?></th>
                                                            <th><?php echo __('Status'); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while ($f = $res_filhos->fetch_assoc()): ?>
                                                                <tr style="cursor: pointer;"
                                                                    onclick="window.location='perfil_ativo.php?id=<?php echo $f['id_asset']; ?>'">
                                                                    <td>
                                                                        <div class="font-weight-bold"><?php echo $f['tag']; ?></div>
                                                                        <div class="small text-muted"><?php echo $f['modelo']; ?></div>
                                                                    </td>
                                                                    <td>
                                                                        <span
                                                                            class="badge badge-<?php echo ($f['status'] == 'Em uso') ? 'success' : 'warning'; ?> small">
                                                                            <?php echo __($f['status']); ?>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div> <!-- table-responsive -->
                                        </div> <!-- card-body -->
                                    </div> <!-- card -->
                            <?php endif; ?>
                        </div> <!-- col-lg-8 -->
                    </div> <!-- row -->
                </div> <!-- container-fluid -->
            </div> <!-- content -->
            <footer class="bg-white sticky-footer">
                <div class="container my-auto">
                    <div class="text-center my-auto copyright"><span>Copyright © Asset MGT 2026</span></div>
                </div>
            </footer>
        </div> <!-- content-wrapper -->
        <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div> <!-- wrapper -->

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="/assets/js/global_search.js" defer></script>

    <script>
        // --- UTILITÁRIOS ---
        function copyNF(text) {
            const icon = document.getElementById('copyIcon');
            navigator.clipboard.writeText(text).then(() => {
                icon.classList.replace('fa-copy', 'fa-check');
                icon.classList.add('text-success');
                setTimeout(() => {
                    icon.classList.replace('fa-check', 'fa-copy');
                    icon.classList.remove('text-success');
                }, 2000);
            });
        }

        function gerarPDF() {
            const body = document.querySelector('#content');
            const opt = {
                margin: 10,
                filename: 'perfil_ativo_<?php echo $ativo['tag']; ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(body).save();
        }

        // --- ATIVOS ACTIONS REPLACEMENT ---
        // Funções de manutenção e atribuição movidas para modals_ativos_shared.php


        function sellAsset(assetId) {
            Swal.fire({
                title: '<?php echo __('Confirmar Doação?'); ?>',
                text: "<?php echo __('Tem certeza que deseja processar a doação deste ativo?'); ?>",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: '<?php echo __('Sim, doar'); ?>',
                cancelButtonText: '<?php echo __('Cancelar'); ?>'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('doar_ativo.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_asset: assetId })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('<?php echo __('Sucesso!'); ?>', '<?php echo __('Ativo doado com sucesso.'); ?>', 'success')
                            .then(() => window.location.href = 'ativos_doados.php');
                        } else {
                            Swal.fire('Error', '<?php echo __('Erro ao processar doação.'); ?>', 'error');
                        }
                    });
                }
            });
        }


    </script>

    <?php include 'modals_ativos_shared.php'; ?>

    <?php include 'performance_footer.php'; ?>
</body>
</html>
