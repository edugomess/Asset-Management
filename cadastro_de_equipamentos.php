<?php
/**
 * CADASTRO DE EQUIPAMENTOS: cadastro_de_equipamentos.php
 * Interface para inclusão de novos itens ao inventário de hardware.
 * Captura dados técnicos (MAC, Tags), financeiros (Valor) e organizacionais (Centro de Custo).
 */
include 'auth.php'; // Proteção de sessão
?>
<!DOCTYPE html>
<html style="margin: 0px, 0px, 0px;margin-bottom: 0px;margin-top: 0px;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Cadastro de Equipamentos</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=3265483e434712d72c41db9eebc4c8bb">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=d6a29779d310462e7fcdde7b9a80e0db">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=5f41e73f827c7b56616237a1da13b6e2">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=19488c1c6619bc9bd5c02de5f7ffbfd4">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=193916adb9d7af47fe74d9a2270caac3">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Animated-numbers-section.css?h=f70eceb0d9266e15c95f7e63479d6265">
    <link rel="stylesheet" href="/assets/css/Bootstrap-Image-Uploader.css?h=406ba72429389f6080fdb666c60fb216">
    <link rel="stylesheet" href="/assets/css/card-image-zoom-on-hover.css?h=82e6162bc70edfde8bfd14b57fdcb3f7">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet"
        href="/assets/css/Form-Select---Full-Date---Month-Day-Year.css?h=7b6a3c2cb7894fdb77bae43c70b92224">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightpick@1.3.4/css/lightpick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="/assets/css/Map-Clean.css?h=bdd15207233b27ebc7c6fc928c71b34c">
    <link rel="stylesheet" href="/assets/css/Modern-Contact-Form.css?h=af67b929d317df499a992472a9bb8fcc">
    <link rel="stylesheet"
        href="/assets/css/Multi-Select-Dropdown-by-Jigar-Mistry.css?h=28bd9d636c700fbf60086e2bcb002efb">
    <link rel="stylesheet"
        href="/assets/css/Password-Strenght-Checker---Ambrodu-1.css?h=1af6ac373aa34a3b40f3d87a4f494eaf">
    <link rel="stylesheet"
        href="/assets/css/Password-Strenght-Checker---Ambrodu.css?h=5818638767f362b9d58a96550bd9a9a3">
    <link rel="stylesheet" href="/assets/css/Simple-footer-by-krissy.css?h=73316da5ae5ad6b51632cd2e5413f263">
    <link rel="stylesheet" href="/assets/css/TR-Form.css?h=ce0bc58b5b8027e2406229d460f4d895">
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
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top"
                    style="margin: 5px 23px;">
                    <div class="container-fluid"><button class="btn btn-link d-md-none rounded-circle mr-3"
                            id="sidebarToggleTop-1" type="button"><i class="fas fa-bars"></i></button>
                        <!-- Busca Global -->
                        <form class="form-inline d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search position-relative">
                            <div class="input-group">
                                <input class="bg-light form-control border-0 small" type="text" placeholder="Pesquisar..." id="globalSearchInput" autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" style="background: rgb(44,64,74); border: none;">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="globalSearchResults" class="dropdown-menu shadow animated--grow-in" style="width: 100%; display: none;"></div>
                        </form>
                        <ul class="navbar-nav flex-nowrap ml-auto">
                            <li class="nav-item dropdown no-arrow mx-1">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link"
                                        aria-expanded="false" data-toggle="dropdown" href="#"><span
                                            class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span><img
                                            class="border rounded-circle img-profile"
                                            src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/avatar5.jpeg'; ?>"></a>
                                    <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in">
                                        <a class="dropdown-item" href="profile.php"><i
                                                class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a>
                                        <a class="dropdown-item" href="configuracoes.php"><i
                                                class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a>
                                        <?php if ($_SESSION['nivelUsuario'] !== 'Usuário'): ?>
                                            <a class="dropdown-item" href="equipamentos.php?status=Manutencao"><i
                                                    class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>Ativos em
                                                Manutenção</a>
                                        <?php endif; ?>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="logout.php"><i
                                                class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="container-fluid">
                    <h3 class="text-dark mb-1">Cadastro de Ativo</h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="inserir_equipamento.php" method="post" enctype="multipart/form-data">

                                <!-- Start: 1-column form row -->
                                <div class="form-row">
                                    <div class="col-sm-12 col-xl-2 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Categoria</label>
                                            <select class="form-control" name="categoria" required="">
                                                <optgroup label="Categoria">

                                                    <?php
                                                    // BANCO DE DADOS: Busca as categorias disponíveis para o seletor
                                                    include 'conexao.php';

                                                    $sql = "SELECT categoria FROM categoria";
                                                    $result = $conn->query($sql);

                                                    if ($result->num_rows > 0) {
                                                        while ($row = $result->fetch_assoc()) {
                                                            echo '<option value="' . $row['categoria'] . '">' . $row['categoria'] . '</option>';
                                                        }
                                                    } else {
                                                        echo '<option value="">Nenhuma categoria encontrada</option>';
                                                    }
                                                    ?>

                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-6 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Fabricante</label>
                                            <input class="form-control" name="fabricante" type="text" placeholder="Ex: Dell"
                                                required="">
                                        </div>
                                    </div>
                                </div><!-- End: 1-column form row -->

                                <!-- Start: 2-column form row -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Modelo</label>
                                            <input class="form-control" name="modelo" type="text"
                                                placeholder="Ex: Latitude 3420" required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Tag / Service Tag</label>
                                            <input class="form-control" name="tag" type="text" placeholder="Ex: ABC123D"
                                                required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Host Name</label>
                                            <input class="form-control" name="hostName" type="text" placeholder="Ex: NOTE-001"
                                                required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Valor do Ativo (R$)</label>
                                            <input class="form-control" name="valor" type="number" step="0.01"
                                                placeholder="Ex: 999.99" required="">
                                        </div>
                                    </div>
                                </div><!-- End: 2-column form row -->

                                <!-- Start: 3-column form row -->
                                <div class="form-row">
                                    <div class="col-sm-4 col-xl-2 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">MAC Address</label>
                                            <input class="form-control" name="macAdress" type="text"
                                                placeholder="Ex: 00:00:00:00:00:00" required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-xl-1">
                                        <div class="custom-control custom-switch" style="margin-top: 30px;">
                                            <input type="hidden" name="status" value="Inativo">
                                            <input type="checkbox" class="custom-control-input" id="statusSwitch"
                                                name="status" value="Ativo" checked>
                                            <label class="custom-control-label" for="statusSwitch">Ativo</label>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 offset-xl-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Data de Cadastro</label>
                                            <input class="form-control" name="dataAtivacao" type="date"
                                                value="<?php echo date('Y-m-d'); ?>" readonly>
                                        </div>
                                    </div>
                                </div><!-- End: 3-column form row -->

                                <!-- Row 5: Imagem e Centro de Custo -->
                                <div class="form-row mt-4">
                                    <div class="col-sm-4 col-xl-5 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Imagem do Ativo</label>
                                            <input class="form-control-file d-xl-flex" name="imagem" type="file"
                                                style="height: 30px;" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Centro de Custo</label>
                                            <?php
                                            // CENTROS DE CUSTO: Busca dinâmica
                                            include_once 'conexao.php';
                                            ?>
                                            <select class="form-control" name="centroDeCusto">
                                                <option value="Nenhum">Nenhum</option>
                                                <?php
                                                $sql_cc = "SELECT nomeSetor FROM centro_de_custo ORDER BY nomeSetor ASC";
                                                $res_cc = $conn->query($sql_cc);
                                                if ($res_cc && $res_cc->num_rows > 0) {
                                                    while ($row_cc = $res_cc->fetch_assoc()) {
                                                        echo '<option value="' . $row_cc['nomeSetor'] . '" ' . $selected . '>' . $row_cc['nomeSetor'] . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div><!-- End: Row 5 -->
                                <!-- Start: 4-column form row -->
                                <div class="form-row">
                                    <div class="col-sm-3 col-xl-9 offset-xl-1" style="height: 150px;">
                                        <label class="text-gray-600 small font-weight-bold">Descrição e Observações</label>
                                        <textarea class="form-control" name="descricao"
                                            placeholder="Detalhes adicionais do equipamento..."
                                            style="height: 100px; margin-bottom: 0px;"></textarea>
                                    </div>
                                    <div class="col-xl-4 offset-xl-4"><button
                                            class="btn btn-success btn-block active text-white pulse animated btn-user"
                                            type="submit"
                                            style="background: rgb(44,64,74);border-radius: 10px;padding: 30px, 30px;border-width: 0px;height: 50px;margin-top: 50px;">Cadastrar</button>
                                    </div>

                                </div><!-- End: 4-column form row -->
                            </form><!-- End: Multi-row Form -->
                        </div>
                    </div>
                </div><!-- End: Multi-row Form -->
            </div>

        </div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightpick@1.3.4/lightpick.min.js"></script>
    <script src="/assets/js/Date-Range-Picker.js?h=1d598b35ada76eb401b3897ae4b61ccb"></script>
    <script src="/assets/js/Animated-numbers-section.js?h=a0ec092b1194013aa3c8e220b0938a52"></script>
    <script src="/assets/js/Bootstrap-Image-Uploader.js?h=2218f85124ce4687cddacceb8e123cc9"></script>
    <script src="/assets/js/DateRangePicker.js?h=e84100887465fbb69726c415c180211a"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.2.0/zxcvbn.js"></script>
    <script src="/assets/js/Multi-Select-Dropdown-by-Jigar-Mistry.js?h=45421b0ed6bd109b4f00e752ae5bf3e5"></script>
    <script src="/assets/js/Password-Strenght-Checker---Ambrodu.js?h=f40a32e3d989fd0e00bf2f0567e52e27"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
</body>

</html>