<?php
/**
 * PERFIL DO FORNECEDOR: perfil_fornecedor.php
 * Visualização detalhada de um fornecedor específico com layout premium.
 */
include_once 'auth.php';
include_once 'conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: fornecedores.php");
    exit();
}

$sql = "SELECT * FROM fornecedor WHERE id_fornecedor = $id";
$result = $conn->query($sql);
$fornecedor = $result->fetch_assoc();

if (!$fornecedor) {
    header("Location: fornecedores.php");
    exit();
}

$status_label = ($fornecedor['status'] == 'Ativo') ? 'success' : 'danger';
$foto = !empty($fornecedor['imagem']) ? htmlspecialchars($fornecedor['imagem']) : '/assets/img/no-image.png';
?>
<!DOCTYPE html>
<html lang="<?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'pt-BR') ? 'pt-br' : 'en'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Perfil do Fornecedor'); ?> - Asset MGT</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=3265483e434712d72c41db9eebc4c8bb">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=d6a29779d310462e7fcdde7b9a80e0db">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=5f41e73f827c7b56616237a1da13b6e2">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
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
            border-radius: 50%;
            overflow: hidden;
            background: white;
        }
        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .detail-label {
            font-size: 0.9rem;
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
        .badge-status {
            /* Now using global .status-badge */
        }
        .btn-action {
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
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
                        <h3 class="text-dark mb-0"><?php echo __('Perfil do Fornecedor'); ?></h3>
                        <a class="btn btn-dark btn-sm d-none d-sm-inline-block" href="fornecedores.php">
                            <i class="fas fa-arrow-left fa-sm text-white-50 mr-2"></i><?php echo __('Voltar para Lista'); ?>
                        </a>
                    </div>
                    
                    <div class="row">
                        <!-- Coluna Direita: Header e Identificação -->
                        <div class="col-lg-4">
                            <div class="card shadow profile-card mb-4">
                                <div class="profile-header">
                                    <div class="profile-img-container shadow">
                                        <img src="<?php echo $foto; ?>" class="profile-img">
                                    </div>
                                    <h4 class="font-weight-bold mb-1"><?php echo htmlspecialchars($fornecedor['nomeEmpresa']); ?></h4>
                                    <p class="mb-3 opacity-75"><?php echo htmlspecialchars($fornecedor['servico']); ?></p>
                                    <span class="status-badge badge-<?php echo $status_label; ?>">
                                        <?php echo __($fornecedor['status']); ?>
                                    </span>
                                </div>
                                <div class="card-body text-center bg-light">
                                    <div class="row no-gutters">
                                        <div class="col-12 py-3">
                                            <div class="detail-label"><?php echo __('CNPJ'); ?></div>
                                            <div class="detail-value mb-0"><?php echo htmlspecialchars($fornecedor['cnpj']); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="info-card shadow mb-4 text-center">
                                <h6 class="font-weight-bold text-primary mb-3"><?php echo __('Ações Rápidas'); ?></h6>
                                <div class="d-flex flex-column gap-2" style="gap: 10px;">
                                    <a href="editar_fornecedor.php?id=<?php echo $id; ?>" class="btn btn-warning btn-action">
                                        <i class="fas fa-edit mr-2"></i><?php echo __('Editar Dados'); ?>
                                    </a>
                                    <a href="apagar_fornecedor.php?id=<?php echo $id; ?>" class="btn btn-danger btn-action" onclick="return confirm('<?php echo __('Tem certeza que deseja excluir este fornecedor?'); ?>');">
                                        <i class="fas fa-trash mr-2"></i><?php echo __('Excluir Fornecedor'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Coluna Esquerda: Detalhes de Contato e Outros -->
                        <div class="col-lg-8">
                            <div class="info-card shadow">
                                <h6 class="font-weight-bold text-primary mb-4">
                                    <i class="fas fa-info-circle mr-2"></i><?php echo __('Informações de Contato'); ?>
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="detail-label"><i class="fas fa-envelope mr-1"></i> <?php echo __('E-mail'); ?></div>
                                        <div class="detail-value">
                                            <a href="mailto:<?php echo htmlspecialchars($fornecedor['email']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($fornecedor['email']); ?>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><i class="fas fa-phone mr-1"></i> <?php echo __('Telefone / WhatsApp'); ?></div>
                                        <div class="detail-value"><?php echo htmlspecialchars($fornecedor['telefone']); ?></div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="detail-label"><i class="fas fa-globe mr-1"></i> <?php echo __('Website'); ?></div>
                                        <div class="detail-value">
                                            <?php if(!empty($fornecedor['site'])): ?>
                                                <a href="<?php echo htmlspecialchars($fornecedor['site']); ?>" target="_blank" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($fornecedor['site']); ?> <i class="fas fa-external-link-alt ml-1" style="font-size: 0.8rem;"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted italic"><?php echo __('Não informado'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-top pt-4 mt-2">
                                    <h6 class="font-weight-bold text-primary mb-3">
                                        <i class="fas fa-tools mr-2"></i><?php echo __('Serviços e Especialidades'); ?>
                                    </h6>
                                    <div class="detail-value">
                                        <p class="mb-0"><?php echo htmlspecialchars($fornecedor['servico']); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Exemplo de Ativos Relacionados (Futuro) -->
                            <div class="info-card shadow opacity-50">
                                <h6 class="font-weight-bold text-muted mb-3">
                                    <i class="fas fa-boxes mr-2"></i><?php echo __('Ativos Adquiridos / Suportados'); ?>
                                </h6>
                                <p class="text-center py-4 mb-0"><?php echo __('Funcionalidade em desenvolvimento...'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
</body>

</html>
