<?php
/**
 * PERFIL DO CENTRO DE CUSTO: perfil_centro_de_custo.php
 * Visualização detalhada de um centro de custo, listando usuários, ativos e licenças vinculadas.
 */
include_once 'auth.php';
include_once 'conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: centro_de_custo.php");
    exit();
}

// 1. Buscar detalhes do Centro de Custo
$sql_cc = "SELECT * FROM centro_de_custo WHERE id_centro_de_custo = $id";
$result_cc = mysqli_query($conn, $sql_cc);
$cc = mysqli_fetch_assoc($result_cc);

if (!$cc) {
    header("Location: centro_de_custo.php");
    exit();
}

$nome_cc = $cc['nomeSetor'];
$foto = !empty($cc['imagem']) ? htmlspecialchars($cc['imagem']) : '/assets/img/dogs/image2.jpeg';

// 2. Buscar Usuários Vinculados (por nome do setor)
$sql_users = "SELECT * FROM usuarios WHERE centroDeCusto = '" . mysqli_real_escape_string($conn, $nome_cc) . "' ORDER BY nome ASC";
$result_users = mysqli_query($conn, $sql_users);

// 3. Buscar Ativos Vinculados (por nome do setor)
$sql_assets = "SELECT * FROM ativos WHERE centroDeCusto = '" . mysqli_real_escape_string($conn, $nome_cc) . "' ORDER BY modelo ASC";
$result_assets = mysqli_query($conn, $sql_assets);

// 4. Buscar Licenças Vinculadas (por ID)
$sql_lic = "SELECT * FROM licencas WHERE id_centro_custo = $id ORDER BY software ASC";
$result_lic = mysqli_query($conn, $sql_lic);

// 5. Estatísticas Rápidas
$total_users = mysqli_num_rows($result_users);
$total_assets = mysqli_num_rows($result_assets);
$total_lic = mysqli_num_rows($result_lic);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Perfil do Centro de Custo'); ?> - Asset MGT</title>
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
        .profile-img-container { width: 120px; height: 120px; margin: 0 auto 20px; border: 4px solid rgba(255,255,255,0.2); border-radius: 20px; overflow: hidden; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; }
        .profile-icon { font-size: 3.5rem; color: white; }
        .detail-label { font-size: 0.8rem; color: #858796; text-transform: uppercase; font-weight: 800; margin-bottom: 2px; }
        .detail-value { font-size: 1.1rem; color: #2c404a; font-weight: 500; margin-bottom: 15px; }
        .info-card { background: #fff; padding: 25px; border-radius: 12px; margin-bottom: 20px; }
        .btn-action { border-radius: 10px; padding: 12px 25px; font-weight: 600; transition: all 0.3s ease; }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .card-shadow { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-shadow:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important; }
        .stats-badge { font-size: 1.2rem; font-weight: 700; color: #2c404a; }
        .stats-label { font-size: 0.75rem; color: #858796; text-transform: uppercase; font-weight: 800; }
        .nav-tabs-premium { border: none; background: #f8f9fc; border-radius: 12px; padding: 5px; display: inline-flex; margin-bottom: 20px; }
        .nav-tabs-premium .nav-link { border: none; color: #000000 !important; font-weight: 800 !important; padding: 10px 20px; border-radius: 10px; transition: all 0.3s ease; margin: 0 2px; }
        .nav-tabs-premium .nav-link:hover { color: #2c404a; background: rgba(0,0,0,0.05); }
        .nav-tabs-premium .nav-link.active { color: white !important; background: #2c404a !important; box-shadow: 0 4px 10px rgba(0,0,0,0.2); font-weight: 800 !important; }
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
                        <h3 class="text-dark mb-0"><?php echo __('Perfil do Centro de Custo'); ?></h3>
                        <a class="btn btn-dark btn-sm" href="centro_de_custo.php">
                            <i class="fas fa-arrow-left fa-sm mr-1"></i><?php echo __('Voltar'); ?>
                        </a>
                    </div>

                    <div class="row">
                        <!-- Coluna Esquerda: Identificação -->
                        <div class="col-lg-4 animate__animated animate__fadeInLeft" style="animation-delay: 0.1s;">
                            <div class="card shadow card-shadow profile-card mb-4">
                                <div class="profile-header">
                                    <div class="profile-img-container shadow">
                                        <i class="fas fa-building profile-icon"></i>
                                    </div>
                                    <h4 class="font-weight-bold mb-1"><?php echo htmlspecialchars($cc['nomeSetor']); ?></h4>
                                    <p class="mb-3 opacity-75">#<?php echo htmlspecialchars($cc['codigo']); ?></p>
                                    <span class="badge badge-<?php echo $cc['status'] === 'Ativo' ? 'success' : 'secondary'; ?> px-3 py-2" style="border-radius: 20px;">
                                        <?php echo __($cc['status'] ?? 'Ativo'); ?>
                                    </span>
                                </div>
                                <div class="card-body bg-light">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="stats-badge"><?php echo $total_users; ?></div>
                                            <div class="stats-label"><?php echo __('Usuários'); ?></div>
                                        </div>
                                        <div class="col-4 border-left">
                                            <div class="stats-badge"><?php echo $total_assets; ?></div>
                                            <div class="stats-label"><?php echo __('Ativos'); ?></div>
                                        </div>
                                        <div class="col-4 border-left">
                                            <div class="stats-badge"><?php echo $total_lic; ?></div>
                                            <div class="stats-label"><?php echo __('Licenças'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="info-card shadow card-shadow">
                                <h6 class="font-weight-bold text-primary mb-4">
                                    <i class="fas fa-info-circle mr-2"></i><?php echo __('Detalhes Técnicos'); ?>
                                </h6>
                                <div class="detail-label"><?php echo __('Código'); ?></div>
                                <div class="detail-value"><?php echo htmlspecialchars($cc['codigo']); ?></div>
                                
                                <div class="detail-label"><?php echo __('Unidade'); ?></div>
                                <div class="detail-value"><?php echo htmlspecialchars($cc['unidade'] ?? '-'); ?></div>
                                
                                <div class="detail-label"><?php echo __('Ramal'); ?></div>
                                <div class="detail-value"><?php echo htmlspecialchars($cc['ramal'] ?? '-'); ?></div>

                                <div class="detail-label"><?php echo __('Gestor Responsável'); ?></div>
                                <div class="detail-value mb-1"><?php echo htmlspecialchars($cc['gestor'] ?? '-'); ?></div>
                                <div class="small text-muted mb-3"><?php echo htmlspecialchars($cc['emailGestor'] ?? ''); ?></div>

                                <a href="editar_centro_de_custo.php?id=<?php echo $id; ?>" class="btn btn-warning btn-block btn-action">
                                    <i class="fas fa-edit mr-2"></i><?php echo __('Editar Cadastro'); ?>
                                </a>
                            </div>
                        </div>

                        <!-- Coluna Direita: Listagens -->
                        <div class="col-lg-8">
                            <div class="info-card shadow card-shadow mb-4 animate__animated animate__fadeInRight" style="animation-delay: 0.2s; padding: 0; overflow: hidden;">
                                <div class="bg-light p-3 border-bottom">
                                    <ul class="nav nav-pills nav-tabs-premium" id="ccTabs" style="margin-bottom: 0; background: #eaecf4;">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="users-tab" data-toggle="tab" href="#users" role="tab"><?php echo __('Usuários'); ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="assets-tab" data-toggle="tab" href="#assets" role="tab"><?php echo __('Ativos'); ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="licenses-tab" data-toggle="tab" href="#licenses" role="tab"><?php echo __('Licenças'); ?></a>
                                    </li>
                                </ul>
                                </div>
                                <div class="tab-content p-4" id="ccTabsContent">
                                    <!-- Usuários Tab -->
                                    <div class="tab-pane fade show active" id="users" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-sm">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo __('Nome'); ?></th>
                                                        <th><?php echo __('E-mail'); ?></th>
                                                        <th><?php echo __('Função'); ?></th>
                                                        <th><?php echo __('Status'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if ($total_users > 0): ?>
                                                        <?php while($u = mysqli_fetch_assoc($result_users)): ?>
                                                            <tr>
                                                                <td><a href="perfil_usuario.php?id=<?php echo $u['id_usuarios']; ?>" class="font-weight-bold text-dark"><?php echo htmlspecialchars($u['nome'] . ' ' . $u['sobrenome']); ?></a></td>
                                                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                                                <td><?php echo htmlspecialchars($u['funcao']); ?></td>
                                                                <td><span class="badge badge-<?php echo ($u['status'] == 'Ativo' ? 'success' : 'danger'); ?>"><?php echo __($u['status']); ?></span></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    <?php else: ?>
                                                        <tr><td colspan="4" class="text-center text-muted py-4"><?php echo __('Nenhum usuário vinculado.'); ?></td></tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Ativos Tab -->
                                    <div class="tab-pane fade" id="assets" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-sm">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo __('Tag'); ?></th>
                                                        <th><?php echo __('Modelo'); ?></th>
                                                        <th><?php echo __('Categoria'); ?></th>
                                                        <th><?php echo __('Status'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if ($total_assets > 0): ?>
                                                        <?php while($a = mysqli_fetch_assoc($result_assets)): ?>
                                                            <tr>
                                                                <td><a href="perfil_ativo.php?id=<?php echo $a['id_asset']; ?>" class="font-weight-bold text-dark"><?php echo htmlspecialchars($a['tag']); ?></a></td>
                                                                <td><?php echo htmlspecialchars($a['modelo']); ?></td>
                                                                <td><?php echo htmlspecialchars($a['categoria']); ?></td>
                                                                <td><span class="badge badge-<?php echo ($a['status'] == 'Ativo' ? 'success' : 'danger'); ?>"><?php echo __($a['status']); ?></span></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    <?php else: ?>
                                                        <tr><td colspan="4" class="text-center text-muted py-4"><?php echo __('Nenhum ativo vinculado.'); ?></td></tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Licenças Tab -->
                                    <div class="tab-pane fade" id="licenses" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-sm">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo __('Software'); ?></th>
                                                        <th><?php echo __('Tipo'); ?></th>
                                                        <th><?php echo __('Uso'); ?></th>
                                                        <th><?php echo __('Status'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if ($total_lic > 0): ?>
                                                        <?php while($l = mysqli_fetch_assoc($result_lic)): ?>
                                                            <tr>
                                                                <td><a href="perfil_licenca.php?id=<?php echo $l['id_licenca']; ?>" class="font-weight-bold text-dark"><?php echo htmlspecialchars($l['software']); ?></a></td>
                                                                <td><?php echo __($l['tipo']); ?></td>
                                                                <td><?php echo $l['quantidade_uso'] . ' / ' . $l['quantidade_total']; ?></td>
                                                                <td><span class="badge badge-<?php echo ($l['status'] == 'Ativa' ? 'success' : 'danger'); ?>"><?php echo __($l['status']); ?></span></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    <?php else: ?>
                                                        <tr><td colspan="4" class="text-center text-muted py-4"><?php echo __('Nenhuma licença vinculada.'); ?></td></tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Descrição -->
                            <?php if (!empty($cc['descricao'])): ?>
                                <div class="info-card shadow card-shadow animate__animated animate__fadeInRight" style="animation-delay: 0.3s;">
                                    <h6 class="font-weight-bold text-primary mb-3">
                                        <i class="fas fa-align-left mr-2"></i><?php echo __('Descrição do Setor'); ?>
                                    </h6>
                                    <div class="p-3 bg-light rounded shadow-sm">
                                        <?php echo nl2br(htmlspecialchars($cc['descricao'])); ?>
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
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
</body>

</html>
