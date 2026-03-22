<?php
/**
 * PERFIL DO ATIVO: perfil_ativo.php
 * Visualização detalhada e prêmium de um equipamento/ativo.
 * Integra lógica de depreciação, doação e histórico.
 */
include_once 'auth.php';
include_once 'conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: equipamentos.php");
    exit();
}

// 1. Buscar detalhes do ativo
$sql_ativo = "SELECT a.*, u.nome AS user_nome, u.sobrenome AS user_sobrenome, m.observacoes AS manutencao_desc, m.data_inicio AS manutencao_data 
             FROM ativos a 
             LEFT JOIN usuarios u ON a.assigned_to = u.id_usuarios
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
                    <div class="d-sm-flex justify-content-between align-items-center mb-4">
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
                        <div class="col-lg-4">
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
                                        <?php if($ativo['assigned_to']): ?>
                                            <a href="perfil_usuario.php?id=<?php echo $ativo['assigned_to']; ?>" class="font-weight-bold text-primary">
                                                <?php echo htmlspecialchars($ativo['user_nome'] . ' ' . $ativo['user_sobrenome']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?php echo __('Disponível'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Ações -->
                            <div class="info-card shadow card-shadow mb-4 text-center">
                                <h6 class="font-weight-bold text-primary mb-3"><?php echo __('Ações Rápidas'); ?></h6>
                                <div class="d-flex flex-column gap-2" style="gap: 10px;">
                                    <a href="editar_ativo.php?id=<?php echo $id; ?>" class="btn btn-warning btn-action">
                                        <i class="fas fa-edit mr-2"></i><?php echo __('Editar Ativo'); ?>
                                    </a>
                                    <?php if (empty($ativo['manutencao_data'])): ?>
                                        <button class="btn btn-info btn-action" style="background: #2c404a; border: none;" data-toggle="modal" data-target="#maintenanceModal">
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
                            <div class="info-card shadow card-shadow">
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

                            <!-- Financeiro e Vida Útil (Values and Status) -->
                            <div class="info-card shadow card-shadow">
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
                                        <div class="detail-value"><?php echo htmlspecialchars($ativo['centroDeCusto']); ?></div>
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
                                                    echo '<a href="perfil_fornecedor.php?id='.$row_f['id_fornecedor'].'">'.htmlspecialchars($ativo['fornecedor']).'</a>';
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
                                            <small class="text-muted">a cada <?php echo intval($dep_config['periodo_anos']); ?> ano(s) e <?php echo intval($dep_config['periodo_meses']); ?> mês(es)</small>
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

                            <!-- Manutenção (Se houver) -->
                            <?php if ($ativo['manutencao_desc']): ?>
                                <div class="info-card shadow card-shadow border-left-warning">
                                    <h6 class="font-weight-bold text-warning mb-3">
                                        <i class="fas fa-tools mr-2"></i><?php echo __('Detalhes da Manutenção'); ?>
                                    </h6>
                                    <p class="mb-1 text-muted small"><?php echo __('Início em'); ?>: <?php echo date('d/m/Y H:i', strtotime($ativo['manutencao_data'])); ?></p>
                                    <div class="p-3 bg-light rounded small">
                                        <?php echo nl2br(htmlspecialchars($ativo['manutencao_desc'])); ?>
                                    </div>
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
                                            while($h = $res_hist->fetch_assoc()) {
                                                if ($h['acao'] === 'Criação') $has_creation = true;
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

                                            foreach($history_rows as $h):
                                            ?>
                                                <tr>
                                                    <td class="text-nowrap"><?php echo date('d/m/Y H:i', strtotime($h['data_evento'])); ?></td>
                                                    <td class="font-weight-bold"><?php echo htmlspecialchars($h['acao']); ?></td>
                                                    <td><?php echo htmlspecialchars($h['detalhes']); ?></td>
                                                    <td><?php echo htmlspecialchars(trim(($h['user_nome'] ?? '') . ' ' . ($h['user_sobrenome'] ?? '')) ?: __('Sistema')); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script>
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

        function sellAsset(assetId) {
            if (confirm("<?php echo __('Tem certeza que deseja doar este ativo?'); ?>")) {
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
                        alert("<?php echo __('Ativo doado com sucesso!'); ?>");
                        window.location.href = 'ativos_doados.php';
                    } else {
                        alert("<?php echo __('Erro ao processar doação!'); ?>");
                    }
                })
                .catch(error => {
                    alert("<?php echo __('Erro ao processar doação!'); ?>");
                });
            }
        }

        function releaseMaintenance(assetId) {
            if (confirm("<?php echo __('Deseja liberar este ativo da manutenção?'); ?>")) {
                const formData = new FormData();
                formData.append('action', 'release_maintenance');
                formData.append('id_asset', assetId);

                fetch('ajax_ativos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || "<?php echo __('Erro ao liberar da manutenção.'); ?>");
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("<?php echo __('Erro de rede.'); ?>");
                });
            }
        }

        $(document).ready(function() {
            $('#confirmMaintenance').on('click', function() {
                const reason = $('#maintenanceReason').val().trim();
                if (!reason) {
                    alert("<?php echo __('Por favor, informe o motivo da manutenção.'); ?>");
                    return;
                }

                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i><?php echo __('Enviando...'); ?>');

                const formData = new FormData();
                formData.append('action', 'send_to_maintenance');
                formData.append('id_asset', '<?php echo $id; ?>');
                formData.append('observacoes', reason);

                fetch('ajax_ativos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || "<?php echo __('Erro ao enviar para manutenção.'); ?>");
                        $(this).prop('disabled', false).html('<?php echo __('Confirmar'); ?>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("<?php echo __('Erro de rede ou permissão.'); ?>");
                    $(this).prop('disabled', false).html('<?php echo __('Confirmar'); ?>');
                });
            });
        });
    </script>

    <!-- Modal de Manutenção -->
    <div class="modal fade" id="maintenanceModal" tabindex="-1" role="dialog" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 15px; border: none; overflow: hidden;">
                <div class="modal-header" style="background: #2c404a; color: white;">
                    <h5 class="modal-title" id="maintenanceModalLabel"><?php echo __('Solicitar Manutenção'); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3"><?php echo __('Descreva o motivo ou problema encontrado no ativo para registro no histórico.'); ?></p>
                    <textarea id="maintenanceReason" class="form-control" rows="4" placeholder="<?php echo __('Ex: Tela trincada, cooler barulhento...'); ?>" style="border-radius: 10px;"></textarea>
                </div>
                <div class="modal-footer" style="background: #f8f9fc;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 10px;"><?php echo __('Cancelar'); ?></button>
                    <button type="button" id="confirmMaintenance" class="btn btn-primary" style="background: #2c404a; border: none; border-radius: 10px;"><?php echo __('Confirmar'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
</body>

</html>
