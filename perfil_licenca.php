<?php
/**
 * PERFIL DA LICENÇA: perfil_licenca.php
 * Visualização detalhada de um software/licença.
 */
include_once 'auth.php';
include_once 'conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: licencas.php");
    exit();
}

// 1. Buscar detalhes da licença
$sql_lic = "SELECT l.*, c.nomeSetor 
            FROM licencas l 
            LEFT JOIN centro_de_custo c ON l.id_centro_custo = c.id_centro_de_custo
            WHERE l.id_licenca = '$id'";
$result_lic = mysqli_query($conn, $sql_lic);
$licenca = mysqli_fetch_assoc($result_lic);

if (!$licenca) {
    header("Location: licencas.php");
    exit();
}

// 2. Lógica de Utilização
$total = intval($licenca['quantidade_total']);
$uso = intval($licenca['quantidade_uso']);
$uso_percent = ($total > 0) ? round(($uso / $total) * 100, 1) : 0;
$prog_class = ($uso_percent >= 90) ? 'bg-danger' : (($uso_percent >= 70) ? 'bg-warning' : 'bg-success');

// 3. UI Helpers
$status_class = ($licenca['status'] === 'Ativa') ? 'success' : (($licenca['status'] === 'Expirada') ? 'danger' : 'secondary');
$excedente = ($uso > $total);
?>
<!DOCTYPE html>
<html lang="<?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'pt-BR') ? 'pt-br' : 'en'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Perfil da Licença'); ?> - Asset MGT</title>
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
        .software-icon { font-size: 4rem; margin-bottom: 15px; opacity: 0.9; }
        .detail-label { font-size: 0.8rem; color: #858796; text-transform: uppercase; font-weight: 700; margin-bottom: 2px; }
        .detail-value { font-size: 1.1rem; color: #2c404a; font-weight: 500; margin-bottom: 15px; }
        .info-card { background: #fff; padding: 25px; border-radius: 12px; margin-bottom: 20px; }
        .btn-action { border-radius: 10px; padding: 12px 25px; font-weight: 600; transition: all 0.3s ease; }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .card-shadow { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-shadow:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important; }
        .usage-container { background: #f8f9fc; border-radius: 10px; padding: 20px; border-left: 4px solid #4e73df; }
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
                        <h3 class="text-dark mb-0"><?php echo __('Perfil da Licença'); ?></h3>
                        <div class="d-flex gap-2" style="gap: 10px;">
                            <a class="btn btn-dark btn-sm" href="licencas.php">
                                <i class="fas fa-arrow-left fa-sm mr-1"></i><?php echo __('Voltar'); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-4 animate__animated animate__fadeInLeft" style="animation-delay: 0.1s;">
                            <!-- Header Card -->
                            <div class="card shadow card-shadow profile-card mb-4">
                                <div class="profile-header">
                                    <div class="software-icon">
                                        <i class="fas fa-file-code"></i>
                                    </div>
                                    <h4 class="font-weight-bold mb-1"><?php echo htmlspecialchars($licenca['software']); ?></h4>
                                    <p class="mb-3 opacity-75"><?php echo htmlspecialchars($licenca['fabricante']); ?></p>
                                    <span class="status-badge badge-<?php echo $status_class; ?>">
                                        <?php echo __(ucfirst($licenca['status'])); ?>
                                    </span>
                                </div>
                                <div class="card-body bg-light">
                                    <div class="usage-container shadow-sm mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="small font-weight-bold text-gray-800"><?php echo __('Uso de Assentos'); ?></span>
                                            <span class="badge <?php echo $prog_class; ?>"><?php echo $uso; ?> / <?php echo $total; ?></span>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar <?php echo $prog_class; ?>" role="progressbar" style="width: <?php echo $uso_percent; ?>%"></div>
                                        </div>
                                        <div class="text-right mt-1">
                                            <small class="text-muted"><?php echo $uso_percent; ?>% <?php echo __('atribuído'); ?></small>
                                        </div>
                                        <?php if($excedente): ?>
                                            <div class="alert alert-danger p-1 mt-2 mb-0 small text-center">
                                                <i class="fas fa-exclamation-triangle mr-1"></i><?php echo __('Limite de assentos excedido!'); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Fornecedor -->
                            <div class="info-card shadow card-shadow mb-4">
                                <h6 class="font-weight-bold text-primary mb-3">
                                    <i class="fas fa-handshake mr-2"></i><?php echo __('Fornecedor'); ?>
                                </h6>
                                <div class="detail-value mb-0">
                                    <?php 
                                    if (!empty($licenca['fornecedor'])) {
                                        $fname = mysqli_real_escape_string($conn, $licenca['fornecedor']);
                                        $res_f = mysqli_query($conn, "SELECT id_fornecedor FROM fornecedor WHERE nomeEmpresa = '$fname' LIMIT 1");
                                        if ($res_f && $row_f = mysqli_fetch_assoc($res_f)) {
                                            echo '<a href="perfil_fornecedor.php?id='.$row_f['id_fornecedor'].'" class="font-weight-bold">'.htmlspecialchars($licenca['fornecedor']).'</a>';
                                        } else {
                                            echo htmlspecialchars($licenca['fornecedor']);
                                        }
                                    } else {
                                        echo '<span class="text-muted">'.__('Nenhum fornecedor vinculado').'</span>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- Ações -->
                            <div class="info-card shadow card-shadow mb-4 text-center">
                                <h6 class="font-weight-bold text-primary mb-3"><?php echo __('Ações Rápidas'); ?></h6>
                                <div class="d-flex flex-column gap-2" style="gap: 10px;">
                                    <a href="editar_licenca.php?id=<?php echo $id; ?>" class="btn btn-warning btn-action">
                                        <i class="fas fa-edit mr-2"></i><?php echo __('Editar Licença'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <!-- Detalhes da Licença -->
                            <div class="info-card shadow card-shadow animate__animated animate__fadeInRight" style="animation-delay: 0.2s;">
                                <h6 class="font-weight-bold text-primary mb-4">
                                    <i class="fas fa-info-circle mr-2"></i><?php echo __('Informações da Licença'); ?>
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Tipo de Licença'); ?></div>
                                        <div class="detail-value"><?php echo __($licenca['tipo']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Chave de Licença'); ?></div>
                                        <div class="detail-value">
                                            <code id="licenseKey" class="bg-light p-1 rounded"><?php echo htmlspecialchars($licenca['chave']); ?></code>
                                            <button class="btn btn-link btn-sm p-0 ml-2" onclick="copyKey()">
                                                <i class="fas fa-copy text-muted"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Data de Aquisição'); ?></div>
                                        <div class="detail-value"><?php echo $licenca['data_aquisicao'] ? date('d/m/Y', strtotime($licenca['data_aquisicao'])) : '-'; ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Data de Expiração'); ?></div>
                                        <div class="detail-value <?php echo ($licenca['status'] === 'Expirada') ? 'text-danger font-weight-bold' : ''; ?>">
                                            <?php echo $licenca['data_expiracao'] ? date('d/m/Y', strtotime($licenca['data_expiracao'])) : __('Vitalícia'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Centro de Custo'); ?></div>
                                        <div class="detail-value text-primary font-weight-bold">
                                            <?php 
                                            if (!empty($licenca['nomeSetor'])) {
                                                echo '<a href="perfil_centro_de_custo.php?id='.$licenca['id_centro_custo'].'">'.htmlspecialchars($licenca['nomeSetor']).'</a>';
                                            } else {
                                                echo '<span class="text-muted small">'.__('Global / Comum').'</span>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-value text-success font-weight-bold">
                                            <?php 
                                            $curr_lang = $_SESSION['language'] ?? 'pt-BR';
                                            echo __('R$') . ' ' . (($curr_lang == 'en-US') ? number_format($licenca['valor_unitario'], 2, '.', ',') : number_format($licenca['valor_unitario'], 2, ',', '.')); 
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quem está utilizando -->
                            <div class="info-card shadow card-shadow animate__animated animate__fadeInRight" style="animation-delay: 0.3s;">
                                <h6 class="font-weight-bold text-primary mb-4">
                                    <i class="fas fa-users mr-2"></i><?php echo __('Quem está utilizando'); ?>
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead class="bg-light">
                                            <tr>
                                                <th><?php echo __('Usuário'); ?></th>
                                                <th><?php echo __('E-mail'); ?></th>
                                                <th><?php echo __('Data de Atribuição'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql_atrib = "SELECT u.nome, u.sobrenome, u.email, u.id_usuarios, al.data_atribuicao 
                                                         FROM atribuicoes_licencas al 
                                                         JOIN usuarios u ON al.id_usuario = u.id_usuarios 
                                                         WHERE al.id_licenca = $id 
                                                         ORDER BY al.data_atribuicao DESC";
                                            $res_atrib = mysqli_query($conn, $sql_atrib);
                                            
                                            if (mysqli_num_rows($res_atrib) > 0) {
                                                while ($at = mysqli_fetch_assoc($res_atrib)) {
                                                    echo '<tr>';
                                                    echo '<td><a href="perfil_usuario.php?id='.$at['id_usuarios'].'" class="font-weight-bold">'.htmlspecialchars($at['nome'].' '.$at['sobrenome']).'</a></td>';
                                                    echo '<td>'.htmlspecialchars($at['email']).'</td>';
                                                    echo '<td>'.date('d/m/Y', strtotime($at['data_atribuicao'])).'</td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="3" class="text-center text-muted py-4">'.__('Nenhuma atribuição encontrada.').'</td></tr>';
                                            }
                                            ?>
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
    <script>
        function copyKey() {
            const key = document.getElementById('licenseKey').innerText;
            navigator.clipboard.writeText(key).then(() => {
                alert("<?php echo __('Chave copiada para a área de transferência!'); ?>");
            });
        }
    </script>
</body>

</html>
