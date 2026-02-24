<?php
include 'auth.php';
include 'conexao.php';

// Fetch Cost Centers
$sql_cc = "SELECT id_centro_de_custo, nomeSetor FROM centro_de_custo ORDER BY nomeSetor ASC";
$result_cc = mysqli_query($conn, $sql_cc);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Cadastro de Licença - Asset Mgt</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=19488c1c6619bc9bd5c02de5f7ffbfd4">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=193916adb9d7af47fe74d9a2270caac3">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <?php include 'sidebar_style.php'; ?>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <a class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#">
                    <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-boxes"></i></div>
                    <div class="sidebar-brand-text mx-3"><span>ASSET MGT</span></div>
                </a>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper" style="min-height: 100vh;">
            <div id="content" style="flex: 1 0 auto;">
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top"
                    style="margin: 23px;">
                    <div class="container-fluid"><button class="btn btn-link d-md-none rounded-circle mr-3"
                            id="sidebarToggleTop-1" type="button"><i class="fas fa-bars"></i></button>
                        <ul class="navbar-nav flex-nowrap ml-auto">
                            <li class="nav-item dropdown no-arrow mx-1">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link"
                                        aria-expanded="false" data-toggle="dropdown" href="#"><span
                                            class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span><img
                                            class="border rounded-circle img-profile"
                                            src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/Captura%20de%20Tela%202021-08-04%20às%2012.25.13.png?h=fcfb924f0ac1ab5f595f029bf526e62d'; ?>"></a>
                                    <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in"><a
                                            class="dropdown-item" href="profile.php"><i
                                                class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a><a
                                            class="dropdown-item" href="configuracoes.php"><i
                                                class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a>
                                        <div class="dropdown-divider"></div><a class="dropdown-item" href="login.php"><i
                                                class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="container-fluid">
                    <h3 class="text-dark mb-1">Cadastro de Licença</h3>
                </div>
                <form action="inserir_licenca.php" method="post">
                    <div class="form-row">
                        <div class="col-sm-6 col-xl-4 offset-xl-1">
                            <div class="form-group">
                                <label class="text-gray-600 small font-weight-bold">Software / Aplicação</label>
                                <input class="form-control" name="software" type="text"
                                    placeholder="Ex: Microsoft Office 365" required="">
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-4 offset-xl-1">
                            <div class="form-group">
                                <label class="text-gray-600 small font-weight-bold">Fabricante</label>
                                <input class="form-control" name="fabricante" type="text" placeholder="Ex: Microsoft"
                                    required="">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-sm-6 col-xl-4 offset-xl-1">
                            <div class="form-group">
                                <label class="text-gray-600 small font-weight-bold">Tipo de Licença</label>
                                <select class="form-control" name="tipo">
                                    <option value="Assinatura">Assinatura</option>
                                    <option value="Vitalícia">Vitalícia</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-4 offset-xl-1">
                            <div class="form-group">
                                <label class="text-gray-600 small font-weight-bold">Chave de Licença</label>
                                <input class="form-control" name="chave" type="text"
                                    placeholder="XXXXX-XXXXX-XXXXX-XXXXX">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-sm-4 col-xl-2 offset-xl-1">
                            <div class="form-group">
                                <label class="text-gray-600 small font-weight-bold">Quantidade Seats</label>
                                <input class="form-control" name="quantidade_total" type="number" value="1" min="1">
                            </div>
                        </div>
                        <div class="col-sm-4 col-xl-2">
                            <div class="form-group">
                                <label class="text-gray-600 small font-weight-bold">Valor Unitário</label>
                                <input class="form-control" name="valor_unitario" type="number" step="0.01"
                                    placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-xl-2">
                            <label class="text-gray-600 small font-weight-bold">Centro de Custo</label>
                            <select class="form-control" name="id_centro_custo">
                                <option value="">Nenhum</option>
                                <?php
                                while ($row_cc = mysqli_fetch_assoc($result_cc)) {
                                    echo "<option value='" . $row_cc['id_centro_de_custo'] . "'>" . htmlspecialchars($row_cc['nomeSetor']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-xl-2">
                            <label class="text-gray-600 small font-weight-bold">Data de Aquisição</label>
                            <input class="form-control" name="data_aquisicao" type="date"
                                value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-xl-2">
                            <label class="text-gray-600 small font-weight-bold">Data de Expiração</label>
                            <input class="form-control" name="data_expiracao" type="date">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-xl-4 offset-xl-4">
                            <button class="btn btn-success btn-block active text-white pulse animated btn-user"
                                type="submit"
                                style="background: rgb(44,64,74);border-radius: 10px;padding: 30px, 30px;border-width: 0px;height: 50px;margin-top: 50px;">Cadastrar
                                Licença</button>
                        </div>
                    </div>
                </form>
            </div>
            <footer class="bg-white sticky-footer">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>DEGB&nbsp;Copyright © 2015-2024</span>
                    </div>
                </div>
            </footer>
        </div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
</body>

</html>