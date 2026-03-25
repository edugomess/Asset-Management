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
function getLocalPath($conn, $id_local) {
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
$status_class = ($ativo['status'] === 'Ativo') ? 'success' : (($ativo['status'] === 'Manutencao' || $ativo['status'] === 'Manutenção') ? 'warning' : 'danger');
$foto = !empty($ativo['imagem']) ? htmlspecialchars($ativo['imagem']) : '/assets/img/no-image.png';
?>
<!DOCTYPE html>
<html lang="<?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'pt-BR') ? 'pt-br' : 'en'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Perfil do Ativo'); ?> - Asset MGT</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=3265483e434712d72c41db9eebc4c8bb">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=d6a29779d310462e7fcdde7b9a80e0db">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=5f41e73f827c7b56616237a1da13b6e2">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <?php include_once 'sidebar_style.php'; ?>
    <style>
        .profile-card { border-radius: 15px; overflow: hidden; border: none; }
        .profile-header { background: linear-gradient(135deg, #2c404a 0%, #3e5b69 100%); color: white; padding: 40px 20px; text-align: center; }
        .profile-img-container { width: 150px; height: 150px; margin: 0 auto 20px; border: 5px solid rgba(255,255,255,0.2); border-radius: 15px; overflow: hidden; background: white; }
        .profile-img { width: 100%; height: 100%; object-fit: cover; }
        .detail-label { font-size: 0.8rem; color: #858796; text-transform: uppercase; font-weight: 700; margin-bottom: 2px; }
        .detail-value { font-size: 1.1rem; color: #2c404a; font-weight: 500; margin-bottom: 15px; }
        .info-card { background: #fff; padding: 25px; border-radius: 12px; margin-bottom: 20px; }
        .btn-action { border-radius: 10px; padding: 12px 25px; font-weight: 600; transition: all 0.3s ease; }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .card-shadow { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-shadow:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important; }
        .financial-summary { background: #f8f9fc; border-radius: 10px; padding: 15px; margin-top: 10px; border-left: 4px solid #4e73df; }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0" style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include_once 'sidebar_brand.php'; ?>
                <?php include_once 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <?php include_once 'topbar.php'; ?>
                <div class="container-fluid">
                    <div class="d-sm-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
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
                                        <img src="<?php echo $foto; ?>" class="profile-img" alt="<?php echo htmlspecialchars($ativo['modelo']); ?>">
                                    </div>
                                    <h4 class="font-weight-bold mb-1"><?php echo htmlspecialchars($ativo['modelo']); ?></h4>
                                    <p class="mb-3 opacity-75"><?php echo htmlspecialchars($ativo['tag']); ?></p>
                                    <span class="status-badge badge-<?php echo $status_class; ?>">
                                        <?php echo __(ucfirst($ativo['status'])); ?>
                                    </span>
                                </div>
                                <div class="card-body bg-light">
                                    <div class="detail-label text-center"><?php echo __('Atribuído a'); ?></div>
                                    <div class="detail-value text-center mb-0">
                                        <?php if ($ativo['assigned_to']): ?>
                                                <a href="perfil_usuario.php?id=<?php echo $ativo['assigned_to']; ?>" class="font-weight-bold text-primary">
                                                    <?php echo htmlspecialchars($ativo['user_nome'] . ' ' . $ativo['user_sobrenome']); ?>
                                                </a>
                                        <?php else: ?>
                                                <span class="badge badge-secondary"><?php echo __('Disponível'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Identificação Visual (QR Code) -->
                            <div class="info-card shadow card-shadow mb-4 text-center">
                                <h6 class="font-weight-bold text-primary mb-3"><?php echo __('Identificação Visual'); ?></h6>
                                <div class="p-2 bg-white d-inline-block rounded shadow-sm mb-3">
                                    <div id="qrcode"></div>
                                    <?php
                                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                                    $host = $_SERVER['HTTP_HOST'];
                                    $path = dirname($_SERVER['PHP_SELF']);
                                    $path = ($path == '/' || $path == '\\') ? '' : $path;
                                    $base_url = $protocol . $host . $path;
                                    $qr_data = $base_url . "/v.php?id=" . $id;
                                    ?>
                                </div>
                                <div class="mt-0">
                                    <span class="badge badge-dark p-2" id="tag_badge_code" style="font-size: 1rem; border-radius: 8px; letter-spacing: 1px;">
                                        <?php echo htmlspecialchars($ativo['tag']); ?>
                                    </span>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-outline-primary btn-sm btn-block" onclick="printAssetTag()" style="border-radius: 8px;">
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
                                    
                                    <?php if ($ativo['assigned_to']): ?>
                                        <button class="btn btn-danger btn-action" onclick="unassignAsset(<?php echo $id; ?>)">
                                            <i class="fas fa-user-minus mr-2"></i><?php echo __('Liberar Ativo'); ?>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-success btn-action" onclick="openAssignModal()">
                                            <i class="fas fa-user-plus mr-2"></i><?php echo __('Atribuir Responsável'); ?>
                                        </button>
                                    <?php endif; ?>

                                    <?php if (empty($ativo['manutencao_data'])): ?>
                                            <button class="btn btn-info btn-action" style="background: #2c404a; border: none;" onclick="openMaintenanceModal()">
                                                <i class="fas fa-tools mr-2"></i><?php echo __('Enviar Manutenção'); ?>
                                            </button>
                                    <?php else: ?>
                                            <button class="btn btn-success btn-action" onclick="releaseMaintenance(<?php echo $id; ?>)">
                                                <i class="fas fa-check-circle mr-2"></i><?php echo __('Liberar Manutenção'); ?>
                                            </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <!-- Especificações Técnicas -->
                            <div class="info-card shadow card-shadow animate__animated animate__fadeInRight" style="animation-delay: 0.2s;">
                                <h6 class="font-weight-bold text-primary mb-4">
                                    <i class="fas fa-microchip mr-2"></i><?php echo __('Especificações Técnicas'); ?>
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Fabricante'); ?></div>
                                        <div class="detail-value"><?php echo htmlspecialchars($ativo['fabricante']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Categoria'); ?></div>
                                        <div class="detail-value"><?php echo htmlspecialchars($ativo['categoria']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Hostname'); ?></div>
                                        <div class="detail-value"><?php echo htmlspecialchars($ativo['hostName']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Endereço MAC'); ?></div>
                                        <div class="detail-value"><?php echo htmlspecialchars($ativo['macAdress']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Service Tag / Certificado'); ?></div>
                                        <div class="detail-value"><?php echo htmlspecialchars($ativo['tag']); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nova Seção: Atribuição e Localização -->
                            <div class="info-card shadow card-shadow animate__animated animate__fadeInRight" style="animation-delay: 0.22s;">
                                <h6 class="text-primary font-weight-bold mb-4"><i class="fas fa-map-marker-alt mr-2"></i><?php echo __('Responsabilidade e Localização'); ?></h6>
                                <div class="p-3 bg-light rounded border">
                                    <?php if ($ativo['assigned_type'] == 'Usuario'): ?>
                                        <p class="mb-1"><strong><?php echo __('Tipo:'); ?></strong> <span class="badge badge-primary"><?php echo __('Responsabilidade Individual'); ?></span></p>
                                        <p class="mb-0"><strong><?php echo __('Usuário:'); ?></strong> 
                                            <?php if (!empty($ativo['user_nome'])): ?>
                                                <a href="perfil_usuario.php?id=<?php echo $ativo['assigned_to']; ?>"><?php echo htmlspecialchars($ativo['user_nome'] . ' ' . $ativo['user_sobrenome']); ?></a>
                                            <?php else: ?>
                                                <span class="text-muted"><?php echo __('Disponível / Estoque'); ?></span>
                                            <?php endif; ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="mb-1"><strong><?php echo __('Tipo:'); ?></strong> <span class="badge badge-success"><?php echo __('Responsabilidade Coletiva (Infraestrutura)'); ?></span></p>
                                        <p class="mb-0"><strong><?php echo __('Local:'); ?></strong> 
                                            <?php if (!empty($ativo['id_local'])): ?>
                                                <span class="text-dark"><?php echo getLocalPath($conn, $ativo['id_local']); ?></span>
                                            <?php else: ?>
                                                <span class="text-danger"><?php echo __('Local não definido'); ?></span>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($ativo['parent_asset_id'])): ?>
                                        <hr class="my-2">
                                        <p class="mb-0"><strong><?php echo __('Vínculo:'); ?></strong> 
                                            <?php echo __('Este ativo faz parte de '); ?> 
                                            <a href="perfil_ativo.php?id=<?php echo $ativo['parent_asset_id']; ?>">
                                                <strong><?php echo $ativo['parent_tag']; ?></strong> (<?php echo $ativo['parent_modelo']; ?>)
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Hardware Specifications (Conditional) -->
                            <?php 
                            $categorias_computacionais = ['Notebook', 'Desktop', 'Servidores', 'Workstation'];
                            $show_hardware = (in_array($ativo['categoria'], $categorias_computacionais) && (!empty($ativo['memoria']) || !empty($ativo['processador']) || !empty($ativo['armazenamento']) || !empty($ativo['gpu']))) ||
                                            ($ativo['categoria'] == 'Monitor' && !empty($ativo['polegadas'])) ||
                                            ($ativo['categoria'] == 'Impressora' && !empty($ativo['is_scanner']));
                            
                            if ($show_hardware): 
                            ?>
                                    <div class="info-card shadow card-shadow animate__animated animate__fadeInRight" style="animation-delay: 0.25s; border-left: 4px solid #2c404a;">
                                        <h6 class="font-weight-bold text-primary mb-4">
                                            <i class="fas fa-server mr-2"></i><?php echo __('Especificações de Hardware'); ?>
                                        </h6>
                                            <div class="row">
                                                <?php if (in_array($ativo['categoria'], $categorias_computacionais)): ?>
                                            <div class="col-md-3">
                                                <div class="detail-label"><?php echo __('Memória RAM'); ?></div>
                                                <div class="detail-value text-dark"><?php echo htmlspecialchars($ativo['memoria'] ?: '-'); ?></div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="detail-label"><?php echo __('Processador'); ?></div>
                                                <div class="detail-value text-dark"><?php echo htmlspecialchars($ativo['processador'] ?: '-'); ?></div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="detail-label"><?php echo __('Capacidade'); ?></div>
                                                <div class="detail-value text-dark"><?php echo htmlspecialchars($ativo['armazenamento'] ?: '-'); ?></div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="detail-label"><?php echo __('Tipo Disco'); ?></div>
                                                <div class="detail-value text-dark"><?php echo htmlspecialchars($ativo['tipo_armazenamento'] ?: '-'); ?></div>
                                            <?php endif; ?>
                                            </div>
                                                <?php if (in_array($ativo['categoria'], $categorias_computacionais) && !empty($ativo['gpu'])): ?>
                                                        <div class="col-md-12 mt-3">
                                                            <div class="divider mb-2"></div>
                                                            <div class="detail-label"><?php echo __('Placa Gráfica (GPU)'); ?></div>
                                                            <div class="detail-value text-dark font-weight-bold">
                                                                <i class="fas fa-video mr-2 text-primary"></i><?php echo htmlspecialchars($ativo['gpu']); ?>
                                                            </div>
                                                        </div>
                                                <?php endif; ?>

                                                <?php if ($ativo['categoria'] == 'Monitor' && !empty($ativo['polegadas'])): ?>
                                                        <div class="col-md-12 mt-3">
                                                            <div class="divider mb-2"></div>
                                                            <div class="detail-label"><?php echo __('Polegadas (Tamanho da Tela)'); ?></div>
                                                            <div class="detail-value text-dark font-weight-bold" style="font-size: 1.1rem;">
                                                                <i class="fas fa-desktop mr-2 text-primary"></i><?php echo htmlspecialchars($ativo['polegadas']); ?>
                                                            </div>
                                                        </div>
                                                <?php endif; ?>

                                                <?php if ($ativo['categoria'] == 'Impressora' && !empty($ativo['is_scanner'])): ?>
                                                        <div class="col-md-12 mt-3">
                                                            <div class="divider mb-2"></div>
                                                            <div class="detail-label"><?php echo __('Funcionalidade'); ?></div>
                                                            <div class="detail-value text-dark font-weight-bold">
                                                                <i class="fas fa-print mr-2 text-primary"></i><?php echo ($ativo['is_scanner'] == 'Sim') ? __('Multifuncional (C/ Scanner)') : __('Impressora'); ?>
                                                            </div>
                                                        </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                <?php endif; ?>

                            <!-- Financeiro e Vida Útil (Values and Status) -->
                            <div class="info-card shadow card-shadow animate__animated animate__fadeInRight" style="animation-delay: 0.3s;">
                                <h6 class="font-weight-bold text-primary mb-4">
                                    <i class="fas fa-chart-line mr-2"></i><?php echo __('Valores e Status'); ?>
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Data de Cadastro'); ?></div>
                                        <div class="detail-value"><?php echo date('d/m/Y', strtotime($ativo['dataAtivacao'])); ?></div>
                                    </div>
                                    <div class="col-md-6">
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
                                    <div class="col-md-6">
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
                                        <div class="detail-value">R$ <?php echo number_format($valor_original, 2, ',', '.'); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Valor Atual'); ?></div>
                                        <div class="detail-value text-success font-weight-bold">R$ <?php echo number_format($valor_atual, 2, ',', '.'); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Taxa de Depreciação'); ?></div>
                                        <div class="detail-value">
                                            <?php echo number_format($taxa_pct, 2, ',', '.'); ?>% 
                                            <small class="text-muted"><?php echo __('a cada'); ?> <?php echo intval($dep_config['periodo_anos']); ?> <?php echo __('ano(s)'); ?> <?php echo __('e'); ?> <?php echo intval($dep_config['periodo_meses']); ?> <?php echo __('mês(es)'); ?></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Nota Fiscal/Chave de Acesso'); ?></div>
                                        <div class="detail-value" style="word-break: break-all;">
                                            <span id="nfNumber"><?php echo htmlspecialchars($ativo['numero_nota_fiscal'] ?? '-'); ?></span>
                                            <?php if (!empty($ativo['numero_nota_fiscal'])): ?>
                                                    <button onclick="copyNF('<?php echo $ativo['numero_nota_fiscal']; ?>')" class="btn btn-sm btn-link text-primary p-0 ml-2" title="<?php echo __('Copiar Chave'); ?>">
                                                        <i id="copyIcon" class="fas fa-copy"></i>
                                                    </button>
                                            <?php endif; ?>
                                            <?php if (!empty($ativo['anexo_nota_fiscal'])): ?>
                                                    <a href="<?php echo htmlspecialchars($ativo['anexo_nota_fiscal']); ?>" target="_blank" class="badge badge-primary ml-2">
                                                        <i class="fas fa-download mr-1"></i><?php echo __('Anexo'); ?>
                                                    </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Depreciação Acumulada'); ?></div>
                                        <div class="detail-value text-danger font-weight-bold">
                                            R$ <?php echo number_format($depreciacao_total, 2, ',', '.'); ?> 
                                            <span class="badge badge-danger ml-1">(<?php echo number_format($percentual_depreciado, 1, ',', '.'); ?>%)</span>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <div class="detail-label"><?php echo __('Status de Doação'); ?></div>
                                        <div class="detail-value py-2 px-3 rounded <?php echo strpos($cor_doacao, 'success') !== false ? 'bg-light-success' : (strpos($cor_doacao, 'danger') !== false ? 'bg-light-danger' : 'bg-light'); ?>" style="border-left: 4px solid currentColor; display: flex; justify-content: space-between; align-items: center;">
                                            <span class="font-weight-bold <?php echo $cor_doacao; ?>"><?php echo $status_doacao; ?></span>
                                            <?php if ($status_doacao === __('Elegível para Doação')): ?>
                                                    <button class="btn btn-success btn-sm px-3" onclick="sellAsset(<?php echo $id; ?>)" style="border-radius: 20px; font-weight: 600;">
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
                                                        <div class="p-3 bg-light rounded shadow-sm <?php echo $border_inner; ?>" style="border-left-width: 4px; transition: all 0.3s ease;">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <span class="badge <?php echo $badge_class; ?> px-2 py-1" style="border-radius: 6px; font-size: 0.75rem;">
                                                                    <i class="fas <?php echo $icon_inner; ?> mr-1"></i><?php echo htmlspecialchars($title_text); ?>
                                                                </span>
                                                                <small class="text-muted font-weight-bold">
                                                                    <i class="far fa-calendar-alt mr-1"></i><?php echo date('d/m/Y', strtotime($tec['data_fim'])); ?>
                                                                </small>
                                                            </div>
                                                            <div class="text-dark font-weight-bold" style="font-size: 0.9rem;">
                                                                <?php echo htmlspecialchars($main_info); ?>
                                                                <?php if ($is_upgrade && !empty($tec['detalhes_update'])): ?>
                                                                        <span class="text-muted font-weight-normal small"> - <?php echo htmlspecialchars($tec['detalhes_update']); ?></span>
                                                                <?php endif; ?>
                                                                <?php if (!$is_upgrade && !empty($tec['item_trocado'])): ?>
                                                                        <div class="text-muted font-weight-normal smallest mt-1">
                                                                            <i class="fas fa-microchip mr-1"></i><?php echo __('Peças'); ?>: <?php echo htmlspecialchars($tec['item_trocado']); ?>
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
                                        <p class="mb-2 text-muted small"><?php echo __('Início em'); ?>: <?php echo date('d/m/Y H:i', strtotime($ativo['manutencao_data'])); ?></p>
                                    
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
                                                        <div class="p-2 bg-light rounded small mb-2"><?php echo nl2br(htmlspecialchars($row_ext['observacoes'])); ?></div>
                                                <?php endif; ?>

                                                <?php if ($row_ext['tipo_manutencao'] === 'Upgrade'): ?>
                                                        <div class="detail-label small"><?php echo __('Categoria'); ?></div>
                                                        <div class="p-2 bg-light rounded small mb-2 font-weight-bold"><?php echo htmlspecialchars($row_ext['categoria_upgrade']); ?></div>

                                                        <?php if ($row_ext['categoria_upgrade'] === 'Memória'): ?>
                                                                <div class="detail-label small"><?php echo __('Módulo Instalado'); ?></div>
                                                                <div class="p-2 bg-light rounded small mb-2 text-primary"><?php echo htmlspecialchars($row_ext['item_trocado']); ?></div>
                                                        <?php elseif ($row_ext['categoria_upgrade'] === 'Armazenamento'): ?>
                                                                <div class="detail-label small"><?php echo __('Tipo de Disco'); ?></div>
                                                                <div class="p-2 bg-light rounded small mb-2 text-primary"><?php echo htmlspecialchars($row_ext['detalhes_update']); ?></div>
                                                        <?php endif; ?>

                                                        <?php if ($row_ext['valor_upgrade'] > 0): ?>
                                                                <div class="detail-label small"><?php echo __('Investimento'); ?></div>
                                                                <div class="p-2 bg-light rounded small mb-2 text-success font-weight-bold">R$ <?php echo number_format($row_ext['valor_upgrade'], 2, ',', '.'); ?></div>
                                                        <?php endif; ?>
                                                <?php elseif ($row_ext['tipo_manutencao'] === 'Insumo'): ?>
                                                        <div class="detail-label small"><?php echo __('Suprimento'); ?></div>
                                                        <div class="p-2 bg-light rounded small mb-2 text-success font-weight-bold">
                                                            <i class="fas fa-box-open mr-1"></i><?php echo htmlspecialchars($row_ext['item_trocado']); ?>
                                                        </div>
                                                <?php else: ?>
                                                        <?php if (!empty($row_ext['item_trocado'])): ?>
                                                                <div class="detail-label small"><?php echo __('Itens Trocados'); ?></div>
                                                                <div class="p-2 bg-light rounded small mb-2 border-left-info"><?php echo nl2br(htmlspecialchars($row_ext['item_trocado'])); ?></div>
                                                        <?php endif; ?>
                                                <?php endif; ?>

                                                <!-- Botão de Conclusão Dinâmico -->
                                                <div class="mt-4 pt-3 border-top">
                                                    <button class="btn btn-success btn-block font-weight-bold" onclick="releaseMaintenance(<?php echo $id; ?>)" style="border-radius: 12px; padding: 12px; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);">
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
                                                        <td class="text-nowrap"><?php echo date('d/m/Y H:i', strtotime($h['data_evento'])); ?></td>
                                                        <td class="font-weight-bold"><?php echo __($h['acao']); ?></td>
                                                        <td><?php echo __($h['detalhes']); ?></td>
                                                        <td><?php echo htmlspecialchars(trim(($h['user_nome'] ?? '') . ' ' . ($h['user_sobrenome'] ?? '')) ?: __('Sistema')); ?></td>
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
                                        <h6 class="text-primary font-weight-bold m-0"><?php echo __('Componentes / Ativos Vinculados'); ?></h6>
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
                                                    <?php while($f = $res_filhos->fetch_assoc()): ?>
                                                        <tr style="cursor: pointer;" onclick="window.location='perfil_ativo.php?id=<?php echo $f['id_asset']; ?>'">
                                                            <td>
                                                                <div class="font-weight-bold"><?php echo $f['tag']; ?></div>
                                                                <div class="small text-muted"><?php echo $f['modelo']; ?></div>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-<?php echo ($f['status'] == 'Em uso') ? 'success' : 'warning'; ?> small">
                                                                    <?php echo __($f['status']); ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="/assets/js/theme.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
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

        // --- MANUTENÇÃO ---
        function openMaintenanceModal() {
            $('#maintenanceReason, #itemTrocado, #upgradeDescription, #supplyDescription, #upgradeValue').val('');
            $('#upgradeCategory, #ramModule, #diskType, #supplyType').val('');
            $('#ramFields, #storageFields').hide();
            $('#typeInsumo').prop('checked', true);
            $('#repairFields, #upgradeFields').hide();
            $('#supplyFields').show();
            $('#maintenanceModal').modal('show');
        }

        function releaseMaintenance(assetId) {
            if (confirm("<?php echo __('Deseja liberar este ativo da manutenção?'); ?>")) {
                const formData = new FormData();
                formData.append('action', 'release_maintenance');
                formData.append('id_asset', assetId);

                fetch('ajax_ativos.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert("<?php echo __('Erro:'); ?> " + (data.message || "<?php echo __('Erro ao liberar da manutenção.'); ?>"));
                    }
                });
            }
        }

        // --- ATRIBUIÇÃO ---
        function openAssignModal() {
            $('#userSearchInput').val('');
            $('#userSearchResults').hide().empty();
            $('#selectedUserInfo').hide();
            $('#btnConfirmAssign').prop('disabled', true);
            $('#assignModal').modal('show');
        }

        function unassignAsset(id) {
            if (confirm("<?php echo __('Deseja realmente remover o responsável atual e liberar este ativo?'); ?>")) {
                $.post('ajax_ativos.php', { action: 'unassign', id_asset: id }, function(res) {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert("<?php echo __('Erro:'); ?> " + res.message);
                    }
                }, 'json');
            }
        }

        function sellAsset(assetId) {
            if (confirm("<?php echo __('Tem certeza que deseja doar este ativo?'); ?>")) {
                fetch('doar_ativo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_asset: assetId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("<?php echo __('Ativo doado com sucesso!'); ?>");
                        window.location.href = 'ativos_doados.php';
                    } else {
                        alert("<?php echo __('Erro ao processar doação!'); ?>");
                    }
                });
            }
        }

        function printAssetTag() {
            const qrImg = document.querySelector('#qrcode img').src;
            const tagText = document.getElementById('tag_badge_code').innerText;
            const assetModel = <?php echo json_encode($ativo['modelo']); ?>;
            
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

        $(document).ready(function() {
            // Inicializar QR Code
            const qrContainer = document.getElementById("qrcode");
            if (qrContainer) {
                new QRCode(qrContainer, {
                    text: <?php echo json_encode($qr_data); ?>,
                    width: 150,
                    height: 150,
                    colorDark : "#2c404a",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.Level ? QRCode.Level.H : 2
                });
                setTimeout(() => {
                    const qrImg = qrContainer.querySelector('img');
                    if (qrImg) qrImg.classList.add('img-fluid');
                }, 500);
            }

            // --- Lógica de Busca de Usuário ---
            var searchTimeout;
            $('#userSearchInput').on('input', function() {
                var query = $(this).val();
                clearTimeout(searchTimeout);
                if (query.length < 2) { $('#userSearchResults').hide().empty(); return; }

                searchTimeout = setTimeout(function() {
                    $.get('ajax_buscar_usuario.php', { query: query }, function(users) {
                        var html = '';
                        if (users.length > 0) {
                            users.forEach(function(user) {
                                html += `<a href="#" class="list-group-item list-group-item-action select-user" 
                                            data-id="${user.id}" data-name="${user.nome_completo}" data-email="${user.email}">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1 font-weight-bold">${user.nome_completo}</h6>
                                                <small class="text-primary font-weight-bold">${user.funcao || ''}</small>
                                            </div>
                                            <p class="mb-1 small text-muted">${user.email}</p>
                                        </a>`;
                            });
                        } else {
                            html = `<div class="list-group-item text-center text-muted"><?php echo __('Nenhum usuário encontrado.'); ?></div>`;
                        }
                        $('#userSearchResults').show().html(html);
                    }, 'json');
                }, 300);
            });

            $(document).on('click', '.select-user', function(e) {
                e.preventDefault();
                $('#selectedUserId').val($(this).data('id'));
                $('#selectedUserName').text($(this).data('name'));
                $('#selectedUserEmail').text($(this).data('email'));
                $('#selectedUserInfo').fadeIn();
                $('#userSearchResults').fadeOut();
                $('#btnConfirmAssign').prop('disabled', false);
            });

            $('#btnConfirmAssign').on('click', function() {
                const id_user = $('#selectedUserId').val();
                const $btn = $(this);
                if (!id_user) return;

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i><?php echo __('Processando...'); ?>');

                $.post('ajax_ativos.php', {
                    action: 'assign',
                    id_asset: <?php echo $id; ?>,
                    id_usuario: id_user
                }, function(res) {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert("<?php echo __('Erro ao atribuir:'); ?> " + res.message);
                        $btn.prop('disabled', false).text("<?php echo __('Confirmar Atribuição'); ?>");
                    }
                }, 'json');
            });

            // --- Lógica Manutenção (Modal) ---
            $('input[name="maintenanceType"]').on('change', function() {
                const type = $(this).val();
                $('#repairFields, #upgradeFields, #supplyFields').hide();
                if (type === 'Reparo') $('#repairFields').slideDown();
                else if (type === 'Upgrade') $('#upgradeFields').slideDown();
                else if (type === 'Insumo') $('#supplyFields').slideDown();
            });

            $('#upgradeCategory').on('change', function() {
                const category = $(this).val();
                if (category === 'Memória') { $('#ramFields').slideDown(); $('#storageFields').slideUp(); }
                else if (category === 'Armazenamento') { $('#ramFields').slideUp(); $('#storageFields').slideDown(); }
                else { $('#ramFields, #storageFields').slideUp(); }
            });

            $('#confirmMaintenance').on('click', function() {
                const type = $('input[name="maintenanceType"]:checked').val();
                let reason = '', item = '', update = '', cat_upgrade = '', val_upgrade = 0;

                if (type === 'Reparo') {
                    reason = $('#maintenanceReason').val().trim();
                    item = $('#itemTrocado').val().trim();
                    if (!reason) { alert("<?php echo __('Por favor, informe o motivo do reparo.'); ?>"); return; }
                } else if (type === 'Upgrade') {
                    cat_upgrade = $('#upgradeCategory').val();
                    reason = $('#upgradeDescription').val().trim();
                    if (!cat_upgrade) { alert("<?php echo __('Por favor, selecione a categoria do upgrade.'); ?>"); return; }
                    if (cat_upgrade === 'Memória') item = $('#ramModule').val();
                    else if (cat_upgrade === 'Armazenamento') { item = $('#diskType').val(); update = $('#upgradeValue').val(); }
                } else if (type === 'Insumo') {
                    reason = $('#supplyDescription').val().trim();
                    item = $('#supplyType').val();
                    if (!item) { alert("<?php echo __('Por favor, selecione o tipo de insumo.'); ?>"); return; }
                }

                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i><?php echo __('Enviando...'); ?>');

                const formData = new FormData();
                formData.append('action', 'send_to_maintenance');
                formData.append('id_asset', '<?php echo $id; ?>');
                formData.append('tipo_manutencao', type);
                formData.append('observacoes', reason);
                formData.append('item_trocado', item);
                formData.append('detalhes_update', update);
                formData.append('categoria_upgrade', cat_upgrade);
                formData.append('valor_upgrade', val_upgrade);

                fetch('ajax_ativos.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) location.reload();
                    else {
                        alert(data.message || "<?php echo __('Erro ao enviar para manutenção.'); ?>");
                        $btn.prop('disabled', false).html("<?php echo __('Confirmar'); ?>");
                    }
                })
                .catch(e => {
                    alert("<?php echo __('Erro de rede.'); ?>");
                    $btn.prop('disabled', false).html("<?php echo __('Confirmar'); ?>");
                });
            });
        });
    </script>

    <!-- MODAL: ATRIBUIÇÃO -->
    <div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: #2c404a; color: white; border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus mr-2"></i><?php echo __('Atribuir Responsável'); ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-gray-700 small mb-2"><?php echo __('Buscar Usuário'); ?></label>
                        <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                            <div class="input-group-prepend"><span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span></div>
                            <input type="text" id="userSearchInput" class="form-control border-left-0" style="height: 45px;" placeholder="<?php echo __('Digite nome, sobrenome ou email...'); ?>">
                        </div>
                    </div>
                    <div id="userSearchResults" class="list-group list-group-flush shadow-sm rounded" style="max-height: 250px; overflow-y: auto; display: none; border: 1px solid #e3e6f0;"></div>
                    <div id="selectedUserInfo" class="mt-4 p-3 border rounded bg-light" style="display: none;">
                        <input type="hidden" id="selectedUserId">
                        <div class="detail-label small text-uppercase font-weight-bold mb-1"><?php echo __('Usuário Selecionado'); ?></div>
                        <div class="font-weight-bold text-primary mb-1" id="selectedUserName"></div>
                        <div class="small text-muted" id="selectedUserEmail"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal"><?php echo __('Cancelar'); ?></button>
                    <button type="button" class="btn btn-success px-4" id="btnConfirmAssign" disabled style="background: #2c404a; border: none;"><?php echo __('Confirmar Atribuição'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: MANUTENÇÃO -->
    <div class="modal fade" id="maintenanceModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 15px; border: none; overflow: hidden;">
                <div class="modal-header" style="background: #2c404a; color: white;">
                    <h5 class="modal-title"><?php echo __('Solicitar Manutenção'); ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4"><?php echo __('Selecione o fluxo técnico para registro da manutenção.'); ?></p>
                    <div class="row text-center mb-4">
                        <?php $types = [['id'=>'Insumo', 'icon'=>'fa-fill-drip', 'color'=>'text-success', 'label'=>__('Suprimento')], ['id'=>'Reparo', 'icon'=>'fa-wrench', 'color'=>'text-warning', 'label'=>__('Reparo')], ['id'=>'Upgrade', 'icon'=>'fa-arrow-up', 'color'=>'text-primary', 'label'=>__('Upgrade')]]; 
                        foreach($types as $t): ?>
                            <div class="col-4 px-1">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="type<?php echo $t['id']; ?>" name="maintenanceType" class="custom-control-input" value="<?php echo $t['id']; ?>" <?php echo $t['id']=='Insumo'?'checked':''; ?>>
                                    <label class="custom-control-label font-weight-bold text-dark" for="type<?php echo $t['id']; ?>" style="cursor: pointer; font-size: 0.85rem;">
                                        <i class="fas <?php echo $t['icon']; ?> d-block mb-1 <?php echo $t['color']; ?>"></i> <?php echo $t['label']; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="repairFields" style="display: none;">
                        <div class="form-group mb-3">
                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Motivo / Problema'); ?></label>
                            <textarea id="maintenanceReason" class="form-control" rows="3" placeholder="<?php echo __('Ex: Teclado falhando, carregador com defeito...'); ?>"></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Peças Trocadas (opcional)'); ?></label>
                            <textarea id="itemTrocado" class="form-control" rows="2" placeholder="<?php echo __('Liste os componentes substituídos...'); ?>"></textarea>
                        </div>
                    </div>
                    <div id="upgradeFields" style="display: none;">
                        <div class="form-group mb-3">
                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Categoria do Upgrade'); ?></label>
                            <select id="upgradeCategory" class="form-control">
                                <option value=""><?php echo __('Selecione uma categoria...'); ?></option>
                                <option value="Memória"><?php echo __('Memória RAM'); ?></option>
                                <option value="Armazenamento"><?php echo __('Armazenamento (Disco)'); ?></option>
                                <option value="Outro"><?php echo __('Outros'); ?></option>
                            </select>
                        </div>
                        <div id="ramFields" style="display: none;">
                            <div class="form-group mb-3">
                                <label class="text-gray-600 small font-weight-bold"><?php echo __('Módulo Selecionado'); ?></label>
                                <select id="ramModule" class="form-control">
                                    <?php foreach(['4GB DDR4', '8GB DDR4', '16GB DDR4', '32GB DDR4', '8GB DDR5', '16GB DDR5', '32GB DDR5'] as $ram) echo "<option value='$ram'>$ram</option>"; ?>
                                </select>
                            </div>
                        </div>
                        <div id="storageFields" style="display: none;">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group mb-3">
                                        <label class="text-gray-600 small font-weight-bold"><?php echo __('Tecnologia de Disco'); ?></label>
                                        <select id="diskType" class="form-control">
                                            <option value="SSD SATA">SSD SATA</option><option value="SSD NVMe">SSD NVMe</option><option value="HDD">HDD</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group mb-3">
                                        <label class="text-gray-600 small font-weight-bold"><?php echo __('Capacidade'); ?></label>
                                        <select id="upgradeValue" class="form-control">
                                            <?php foreach(['120GB', '240GB', '480GB', '960GB', '256GB', '512GB', '1TB', '2TB'] as $cap) echo "<option value='$cap'>$cap</option>"; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Observações do Upgrade'); ?></label>
                            <textarea id="upgradeDescription" class="form-control" rows="2" placeholder="<?php echo __('Detalhes adicionais do upgrade...'); ?>"></textarea>
                        </div>
                    </div>
                    <div id="supplyFields">
                        <div class="form-group mb-3">
                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Tipo de Insumo'); ?></label>
                            <select id="supplyType" class="form-control">
                                <option value=""><?php echo __('Selecione o insumo...'); ?></option>
                                <?php foreach(['Toner', 'Difusor', 'Cartucho de Tinta', 'Cilindro / Drum', 'Fita de Impressão', 'Kit Fusor', 'Outro'] as $ins) echo "<option value='$ins'>".__($ins)."</option>"; ?>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Observações'); ?></label>
                            <textarea id="supplyDescription" class="form-control" rows="2" placeholder="<?php echo __('Ex: Troca do Toner Preto - Unidade A'); ?>"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fc;">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal"><?php echo __('Cancelar'); ?></button>
                    <button type="button" id="confirmMaintenance" class="btn btn-primary px-4"><?php echo __('Confirmar'); ?></button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
< / b o d y > < / h t m l >  
 