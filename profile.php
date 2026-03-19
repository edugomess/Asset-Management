<?php
/**
 * PERFIL DO USUÁRIO: profile.php
 * Exibe dados pessoais, ativos atribuídos e licenças vinculadas ao usuário.
 */
include 'auth.php';
include 'conexao.php';

// RECUPERAÇÃO DE DADOS: Suporta visualização de outros perfis (Admin) ou o próprio (Usuário comum)
$id_usuario = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['id_usuarios'];

// Restrição para nível "Usuário": só pode ver o próprio perfil
if ($_SESSION['nivelUsuario'] === 'Usuário') {
    $id_usuario = $_SESSION['id_usuarios'];
}

$sql_user = "SELECT * FROM usuarios WHERE id_usuarios = $id_usuario";
$result_user = mysqli_query($conn, $sql_user);
$user_data = mysqli_fetch_assoc($result_user);

// BUSCA DE ATIVOS: Recupera todos os equipamentos que estão atualmente sob responsabilidade do usuário
$sql_assets = "SELECT * FROM ativos WHERE assigned_to = $id_usuario";
$result_assets = mysqli_query($conn, $sql_assets);

// BUSCA DE LICENÇAS: Recupera softwares vinculados via tabela de junção (n:n)
$sql_lic = "SELECT l.* FROM licencas l 
            JOIN atribuicoes_licencas al ON l.id_licenca = al.id_licenca 
            WHERE al.id_usuario = $id_usuario";
$result_lic = mysqli_query($conn, $sql_lic);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Perfil'); ?> - Asset MGT</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=f3d9abe8d5aa7831c01bfaa2a1563712">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=41e93b37bc495fd67938799bb3a6adaf">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <?php include 'sidebar_style.php'; ?>
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
                <div class="container-fluid">
                    <h3 class="text-dark mb-4"><?php echo __('Perfil do Usuário'); ?></h3>
                    <div class="row mb-3">
                        <div class="col-lg-4">
                            <div class="card mb-3">
                                <div class="card-body text-center shadow"><img class="rounded-circle mb-3 mt-4"
                                        src="<?php echo !empty($user_data['foto_perfil']) ? htmlspecialchars($user_data['foto_perfil']) : '/assets/img/avatars/avatar5.jpeg'; ?>"
                                        width="160" height="160">
                                    <div class="mb-3">
                                        <a href="editar_usuario.php?id=<?php echo $id_usuario; ?>"
                                            class="btn btn-primary btn-sm" style="background: rgb(44,64,74);">
                                            <?php echo ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') ? __('Alterar Foto') : __('Alterar Foto / Editar'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="row">
                                <div class="col">
                                    <div class="card shadow mb-3">
                                        <div class="card-header py-3">
                                            <p class="text-primary m-0 font-weight-bold"><?php echo __('Detalhes do Usuário'); ?></p>
                                        </div>
                                        <div class="card-body">
                                            <form>
                                                <div class="form-row">
                                                    <div class="col">
                                                        <div class="form-group"><label for="username"><strong><?php echo __('Usuário AD'); ?></strong></label><input class="form-control"
                                                                type="text"
                                                                value="<?php echo htmlspecialchars($user_data['usuarioAD']); ?>"
                                                                readonly></div>
                                                    </div>
                                                    <div class="col">
                                                        <div class="form-group"><label
                                                                for="email"><strong><?php echo __('Email'); ?></strong></label><input
                                                                class="form-control" type="email"
                                                                value="<?php echo htmlspecialchars($user_data['email']); ?>"
                                                                readonly></div>
                                                    </div>
                                                </div>
                                                <div class="form-row">
                                                    <div class="col">
                                                        <div class="form-group"><label
                                                                for="first_name"><strong><?php echo __('Nome'); ?></strong></label><input
                                                                class="form-control" type="text"
                                                                value="<?php echo htmlspecialchars($user_data['nome']); ?>"
                                                                readonly></div>
                                                    </div>
                                                    <div class="col">
                                                        <div class="form-group"><label
                                                                for="last_name"><strong><?php echo __('Sobrenome'); ?></strong></label><input
                                                                class="form-control" type="text"
                                                                value="<?php echo htmlspecialchars($user_data['sobrenome']); ?>"
                                                                readonly></div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="card shadow mb-4">
                                        <div class="card-header py-3">
                                            <h6 class="text-primary font-weight-bold m-0"><?php echo __('Ativos Atribuídos'); ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered" width="100%" cellspacing="0">
                                                    <thead>
                                                        <tr>
                                                            <th><?php echo __('Tag'); ?></th>
                                                            <th><?php echo __('Categoria'); ?></th>
                                                            <th><?php echo __('Fabricante'); ?></th>
                                                            <th><?php echo __('Modelo'); ?></th>
                                                            <th><?php echo __('HostName'); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        if (mysqli_num_rows($result_assets) > 0) {
                                                            while ($asset = mysqli_fetch_assoc($result_assets)) {
                                                                echo "<tr>";
                                                                echo "<td>" . htmlspecialchars($asset['tag']) . "</td>";
                                                                echo "<td>" . htmlspecialchars($asset['categoria']) . "</td>";
                                                                echo "<td>" . htmlspecialchars($asset['fabricante']) . "</td>";
                                                                echo "<td>" . htmlspecialchars($asset['modelo']) . "</td>";
                                                                echo "<td>" . htmlspecialchars($asset['hostName']) . "</td>";
                                                                echo "</tr>";
                                                            }
                                                        } else {
                                                            echo "<tr><td colspan='5'>" . __('Nenhum ativo atribuído.') . "</td></tr>";
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card shadow mb-4">
                                        <div class="card-header py-3">
                                            <h6 class="text-primary font-weight-bold m-0"><?php echo __('Licenças Atribuídas'); ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered" width="100%" cellspacing="0">
                                                    <thead>
                                                        <tr>
                                                            <th><?php echo __('Software'); ?></th>
                                                            <th><?php echo __('Fabricante'); ?></th>
                                                            <th><?php echo __('Tipo'); ?></th>
                                                            <th><?php echo __('Chave'); ?></th>
                                                            <th><?php echo __('Expiração'); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        if (mysqli_num_rows($result_lic) > 0) {
                                                            while ($lic = mysqli_fetch_assoc($result_lic)) {
                                                                echo "<tr>";
                                                                echo "<td><strong>" . htmlspecialchars($lic['software']) . "</strong></td>";
                                                                echo "<td>" . htmlspecialchars($lic['fabricante']) . "</td>";
                                                                echo "<td>" . htmlspecialchars($lic['tipo']) . "</td>";
                                                                echo "<td><code class='text-muted'>********</code></td>";
                                                                echo "<td>" . ($lic['data_expiracao'] ? date('d/m/Y', strtotime($lic['data_expiracao'])) : 'N/A') . "</td>";
                                                                echo "</tr>";
                                                            }
                                                        } else {
                                                            echo "<tr><td colspan='5'>" . __('Nenhuma licença atribuída.') . "</td></tr>";
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
            </div>

        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/theme.js"></script>
    <script src="/assets/js/global_search.js"></script>
</body>

</html>