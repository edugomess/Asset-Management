<?php
/**
 * PERFIL DO USUÁRIO: perfil_usuario.php
 * Visualização detalhada de um colaborador com layout premium.
 */
include_once 'auth.php';
include_once 'conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['id_usuarios'];

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

$sql = "SELECT * FROM usuarios WHERE id_usuarios = $id";
$result = $conn->query($sql);
$usuario = $result->fetch_assoc();

if (!$usuario) {
    header("Location: usuarios.php");
    exit();
}

$status_label = ($usuario['status'] == 'Ativo') ? 'success' : 'danger';
$foto = !empty($usuario['foto_perfil']) ? htmlspecialchars($usuario['foto_perfil']) : '/assets/img/avatars/avatar1.jpeg';
$nome_completo = htmlspecialchars($usuario['nome'] . ' ' . $usuario['sobrenome']);
?>
<!DOCTYPE html>
<html lang="<?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'pt-BR') ? 'pt-br' : 'en'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Perfil do Usuário'); ?> - Asset MGT</title>
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
        .card-shadow { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-shadow:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important; }
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
                    <div class="d-sm-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
                        <h3 class="text-dark mb-0"><?php echo __('Perfil do Usuário'); ?></h3>
                        <a class="btn btn-dark btn-sm d-none d-sm-inline-block" href="usuarios.php">
                            <i class="fas fa-arrow-left fa-sm text-white-50 mr-2"></i><?php echo __('Voltar para Lista'); ?>
                        </a>
                    </div>
                    
                    <div class="row">
                        <!-- Coluna Esquerda: Header e Identificação -->
                        <div class="col-lg-4 animate__animated animate__fadeInLeft" style="animation-delay: 0.1s;">
                            <div class="card shadow profile-card mb-4">
                                <div class="profile-header">
                                    <div class="profile-img-container shadow">
                                        <img src="<?php echo $foto; ?>" class="profile-img" alt="<?php echo $nome_completo; ?>">
                                    </div>
                                    <h4 class="font-weight-bold mb-1"><?php echo $nome_completo; ?></h4>
                                    <p class="mb-3 opacity-75">@<?php echo htmlspecialchars($usuario['usuarioAD']); ?></p>
                                    <span class="status-badge badge-<?php echo $status_label; ?>">
                                        <?php echo __($usuario['status']); ?>
                                    </span>
                                </div>
                                <div class="card-body text-center bg-light">
                                    <div class="row no-gutters">
                                        <div class="col-12 py-3">
                                            <div class="detail-label"><?php echo __('Nível de Acesso'); ?></div>
                                            <div class="detail-value mb-0">
                                                <span class="status-badge" style="background: #2c404a; color: #fff;">
                                                    <?php echo __($usuario['nivelUsuario']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="info-card shadow card-shadow mb-4 text-center">
                                <h6 class="font-weight-bold text-primary mb-3"><?php echo __('Ações Rápidas'); ?></h6>
                                <div class="d-flex flex-column gap-2" style="gap: 10px;">
                                    <a href="editar_usuario.php?id=<?php echo $id; ?>" class="btn btn-warning btn-action">
                                        <i class="fas fa-edit mr-2"></i><?php echo __('Editar Dados'); ?>
                                    </a>
                                    <a href="apagar_usuario.php?id=<?php echo $id; ?>" class="btn btn-danger btn-action" onclick="return confirm('<?php echo __('Tem certeza que deseja excluir este usuário?'); ?>');">
                                        <i class="fas fa-trash mr-2"></i><?php echo __('Excluir Usuário'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Coluna Direita: Detalhes Profissionais e Contato -->
                        <div class="col-lg-8">
                            <div class="info-card shadow card-shadow animate__animated animate__fadeInRight" style="animation-delay: 0.2s;">
                                <h6 class="font-weight-bold text-primary mb-4">
                                    <i class="fas fa-id-card mr-2"></i><?php echo __('Informações Profissionais'); ?>
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Função / Cargo'); ?></div>
                                        <div class="detail-value"><?php echo htmlspecialchars($usuario['funcao']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('Centro de Custo'); ?></div>
                                        <div class="detail-value text-primary font-weight-bold">
                                            <?php 
                                            $cc_name = $usuario['centroDeCusto'];
                                            if (!empty($cc_name)) {
                                                $cc_name_escaped = mysqli_real_escape_string($conn, $cc_name);
                                                $res_cc = mysqli_query($conn, "SELECT id_centro_de_custo FROM centro_de_custo WHERE nomeSetor = '$cc_name_escaped' LIMIT 1");
                                                if ($res_cc && $row_cc = mysqli_fetch_assoc($res_cc)) {
                                                    echo '<a href="perfil_centro_de_custo.php?id='.$row_cc['id_centro_de_custo'].'">'.htmlspecialchars($cc_name).'</a>';
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
                                        <div class="detail-label"><?php echo __('Unidade'); ?></div>
                                        <div class="detail-value"><?php echo htmlspecialchars($usuario['unidade']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-label"><?php echo __('E-mail'); ?></div>
                                        <div class="detail-value">
                                            <a href="mailto:<?php echo htmlspecialchars($usuario['email']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($usuario['email']); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Ativos em Posse -->
                            <div class="info-card shadow card-shadow mb-4 animate__animated animate__fadeInRight" style="animation-delay: 0.3s;">
                                <h6 class="font-weight-bold text-primary mb-4">
                                    <i class="fas fa-laptop mr-2"></i><?php echo __('Ativos em Posse'); ?>
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th><?php echo __('Modelo'); ?></th>
                                                <th><?php echo __('Tag'); ?></th>
                                                <th><?php echo __('Status'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql_assets = "SELECT * FROM ativos WHERE assigned_to = $id";
                                            $res_assets = $conn->query($sql_assets);
                                            $can_click = ($_SESSION['nivelUsuario'] === 'Admin' || $_SESSION['nivelUsuario'] === 'Suporte');
                                            if ($res_assets && $res_assets->num_rows > 0):
                                                while ($asset = $res_assets->fetch_assoc()):
                                            ?>
                                                <tr <?php if ($can_click): ?>
                                                    onclick="location.href='perfil_ativo.php?id=<?php echo $asset['id_asset']; ?>'" 
                                                    onkeypress="if(event.key==='Enter')location.href='perfil_ativo.php?id=<?php echo $asset['id_asset']; ?>'"
                                                    style="cursor: pointer;" class="clickable-row"
                                                    <?php else: ?>
                                                    class="no-click-row"
                                                    <?php endif; ?>>
                                                    <td><?php echo htmlspecialchars($asset['modelo']); ?></td>
                                                    <td>
                                                        <?php if ($can_click): ?>
                                                            <a href="perfil_ativo.php?id=<?php echo $asset['id_asset']; ?>">
                                                                <?php echo htmlspecialchars($asset['tag']); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <?php echo htmlspecialchars($asset['tag']); ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge <?php echo ($asset['status'] === 'Ativo') ? 'badge-success' : 'badge-danger'; ?>">
                                                            <?php echo __($asset['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php 
                                                endwhile;
                                            else:
                                            ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-3">
                                                        <?php echo __('Nenhum ativo atribuído a este usuário.'); ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Licenças em Posse (Licenses Assigned) -->
                            <div class="info-card shadow card-shadow animate__animated animate__fadeInRight" style="animation-delay: 0.4s;">
                                <h6 class="font-weight-bold text-primary mb-4">
                                    <i class="fas fa-key mr-2"></i><?php echo __('Licenças em Posse'); ?>
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th><?php echo __('Software'); ?></th>
                                                <th><?php echo __('Fabricante'); ?></th>
                                                <th><?php echo __('Data Atribuição'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql_lic = "SELECT l.id_licenca, l.software, l.fabricante, al.data_atribuicao 
                                                       FROM atribuicoes_licencas al 
                                                       JOIN licencas l ON al.id_licenca = l.id_licenca 
                                                       WHERE al.id_usuario = $id 
                                                       ORDER BY al.data_atribuicao DESC";
                                            $res_lic = $conn->query($sql_lic);
                                            $can_click = ($_SESSION['nivelUsuario'] === 'Admin' || $_SESSION['nivelUsuario'] === 'Suporte');
                                            if ($res_lic && $res_lic->num_rows > 0):
                                                while ($lic = $res_lic->fetch_assoc()):
                                            ?>
                                                <tr <?php if ($can_click) echo 'onclick="location.href=\'perfil_licenca.php?id=' . $lic['id_licenca'] . '\'" style="cursor: pointer;" class="clickable-row"'; else echo 'class="no-click-row"'; ?>>
                                                    <td><strong><?php echo htmlspecialchars($lic['software']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($lic['fabricante']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($lic['data_atribuicao'])); ?></td>
                                                </tr>
                                            <?php 
                                                endwhile;
                                            else:
                                            ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-3">
                                                        <?php echo __('Nenhuma licença atribuída.'); ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
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
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
</body>

</html>
