<?php
include_once 'auth.php';
include_once 'conexao.php';
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$id = intval($id);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Blank Page - Brand</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">

    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=f3d9abe8d5aa7831c01bfaa2a1563712">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=41e93b37bc495fd67938799bb3a6adaf">
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
            <div class="container-fluid d-flex flex-column p-0"><a
                    class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#">
                    <div class="sidebar-brand-icon rotate-n-15"><svg xmlns="http://www.w3.org/2000/svg" width="1em"
                            height="1em" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icon-tabler-layout-distribute-horizontal"
                            style="width: 30px;height: 30px;">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <line x1="4" y1="4" x2="20" y2="4"></line>
                            <line x1="4" y1="20" x2="20" y2="20"></line>
                            <rect x="6" y="9" width="12" height="6" rx="2"></rect>
                        </svg></div>
                    <div class="sidebar-brand-text mx-3"><span>ASSET MGT</span></div>
                </a>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content" style="margin: 0px;">
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top"
                    style="margin: 23px;">
                    <div class="container-fluid"><button class="btn btn-link d-md-none rounded-circle mr-3"
                            id="sidebarToggleTop-1" type="button"><i class="fas fa-bars"></i></button>
                        <ul class="navbar-nav flex-nowrap ml-auto">
                            <li class="nav-item dropdown no-arrow mx-1">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link"
                                        aria-expanded="false" data-toggle="dropdown" href="#"></a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-list animated--grow-in">
                                        <h6 class="dropdown-header">alerts center</h6><a
                                            class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="mr-3">
                                                <div class="bg-primary icon-circle"><i
                                                        class="fas fa-file-alt text-white"></i></div>
                                            </div>
                                            <div><span class="small text-gray-500">December 12, 2019</span>
                                                <p>A new monthly report is ready to download!</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="mr-3">
                                                <div class="bg-success icon-circle"><i
                                                        class="fas fa-donate text-white"></i></div>
                                            </div>
                                            <div><span class="small text-gray-500">December 7, 2019</span>
                                                <p>$290.29 has been deposited into your account!</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="mr-3">
                                                <div class="bg-warning icon-circle"><i
                                                        class="fas fa-exclamation-triangle text-white"></i></div>
                                            </div>
                                            <div><span class="small text-gray-500">December 2, 2019</span>
                                                <p>Spending Alert: We've noticed unusually high spending for your
                                                    account.</p>
                                            </div>
                                        </a><a class="dropdown-item text-center small text-gray-500" href="#">Show All
                                            Alerts</a>
                                    </div>
                                </div>
                            </li>
                            <li class="nav-item dropdown no-arrow mx-1">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link"
                                        aria-expanded="false" data-toggle="dropdown" href="#"></a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-list animated--grow-in">
                                        <h6 class="dropdown-header">alerts center</h6><a
                                            class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle"
                                                    src="/assets/img/avatars/avatar4.jpeg?h=fefb30b61c8459a66bd338b7d790c3d5">
                                                <div class="bg-success status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>Hi there! I am wondering if you can
                                                        help me with a problem I've been having.</span></div>
                                                <p class="small text-gray-500 mb-0">Emily Fowler - 58m</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle"
                                                    src="/assets/img/avatars/avatar2.jpeg?h=5d142be9441885f0935b84cf739d4112">
                                                <div class="status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>I have the photos that you ordered last
                                                        month!</span></div>
                                                <p class="small text-gray-500 mb-0">Jae Chun - 1d</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle"
                                                    src="/assets/img/avatars/avatar3.jpeg?h=c5166867f10a4e454b5b2ae8d63268b3">
                                                <div class="bg-warning status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>Last month's report looks great, I am
                                                        very happy with the progress so far, keep up the good
                                                        work!</span></div>
                                                <p class="small text-gray-500 mb-0">Morgan Alvarez - 2d</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle"
                                                    src="/assets/img/avatars/avatar5.jpeg?h=35dc45edbcda6b3fc752dab2b0f082ea">
                                                <div class="bg-success status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>Am I a good boy? The reason I ask is
                                                        because someone told me that people say this to all dogs, even
                                                        if they aren't good...</span></div>
                                                <p class="small text-gray-500 mb-0">Chicken the Dog · 2w</p>
                                            </div>
                                        </a><a class="dropdown-item text-center small text-gray-500" href="#">Show All
                                            Alerts</a>
                                    </div>
                                </div>
                                <div class="shadow dropdown-list dropdown-menu dropdown-menu-right"
                                    aria-labelledby="alertsDropdown"></div>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="container-fluid">
                    <h3 class="text-dark mb-1">Editar Usuário</h3>
                </div><!-- Start: TR Form -->
                <form class="register-form">
                    <fieldset></fieldset>
                </form><!-- End: TR Form -->
                <!-- Start: Multi-row Form -->
                <form action="update_usuario.php" method="post" enctype="multipart/form-data">
                    <?php
                    $sql = "SELECT * FROM usuarios WHERE id_usuarios = '$id'";
                    $result = mysqli_query($conn, $sql);
                    while ($array = mysqli_fetch_array($result)) {
                        echo "<input type='hidden' name='id_usuarios' value='" . $array['id_usuarios'] . "'>";

                        $nome = $array['nome'];
                        $sobrenome = $array['sobrenome'];
                        $usuarioAD = $array['usuarioAD'];
                        $funcao = $array['funcao'];
                        $dataNascimento = $array['dataNascimento'];
                        $email = $array['email'];
                        $centroDeCusto = $array['centroDeCusto'];
                        $matricula = $array['matricula'];
                        $telefone = $array['telefone'];
                        $senha = $array['senha'];
                        $confirmarSenha = $array['confirmarSenha'];
                        $nivelUsuario = $array['nivelUsuario'];
                        $unidade = $array['unidade'];
                        $status = $array['status'];
                        ?>
                        <!-- Start: 2-column form row -->
                        <div class="form-row" style="height: 78px;">
                            <div class="col-10 col-sm-6 col-xl-3 offset-1 offset-xl-1">
                                <div class="form-group">
                                    <label></label>
                                    <input class="form-control" name="nome" type="text" value="<?php echo $nome ?>">
                                </div>
                            </div>
                            <div class="col-10 col-sm-6 col-xl-3 offset-1 offset-xl-1">
                                <div class="form-group">
                                    <label></label>
                                    <input class="form-control" name="sobrenome" type="text"
                                        value="<?php echo $sobrenome ?>">
                                </div>
                            </div>
                            <div class="col-xl-2 offset-xl-1">
                                <input class="form-control" name="usuarioAD" type="text" style="margin-top: 24px;"
                                    value="<?php echo $usuarioAD ?>">
                            </div>
                        </div><!-- End: 2-column form row -->

                        <!-- Start: 3-column form row -->
                        <div class="form-row">
                            <div class="col-sm-4 offset-lg-1 offset-xl-1">
                                <input class="form-control" name="funcao" type="text" value="<?php echo $funcao ?>"
                                    style="padding: 6px; margin-top: 24px;">
                            </div>
                            <div class="col-xl-3">
                                <div class="form-group">
                                    <label></label>
                                    <input class="form-control" name="dataNascimento" type="date"
                                        value="<?php echo $dataNascimento ?>">
                                </div>
                            </div>
                            <div class="col-xl-3 offset-xl-0">
                                <input class="form-control" name="email" type="email" value="<?php echo $email ?>"
                                    style="margin-top: 24px;">
                            </div>
                        </div><!-- End: 3-column form row -->

                        <!-- Start: 3-column form row -->
                        <div class="form-row">
                            <div class="col-sm-4 offset-lg-1 offset-xl-1">
                                <div class="form-group">
                                    <label></label>
                                    <input class="form-control" name="centroDeCusto" type="text"
                                        value="<?php echo $centroDeCusto ?>">
                                </div>
                            </div>
                            <div class="col-xl-1 offset-lg-1 offset-xl-0">
                                <div class="form-group">
                                    <label></label>
                                    <input class="form-control" name="matricula" type="text"
                                        value="<?php echo $matricula ?>" inputmode="numeric">
                                </div>
                            </div>
                            <div class="col-xl-3">
                                <div class="form-group">
                                    <label></label>
                                    <input class="form-control" name="telefone" type="text" inputmode="tel"
                                        value="<?php echo $telefone ?>">
                                </div>
                            </div>
                            <div class="col-xl-2" style="margin-top: 23px;">
                                <select class="form-control" name="tipoContrato" required="">
                                    <optgroup label="Tipo de Contrato">
                                        <option value="CLT">CLT</option>
                                        <option value="PJ">PJ</option>
                                        <option value="Cooperativa">Cooperativa</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div><!-- End: 3-column form row -->


                        <!-- Start: 5-column form row -->
                        <div class="form-row" style="height: 108px;">
                            <div class="col-sm-2 col-xl-4 offset-xl-1">
                                <div class="form-group">
                                    <label></label>
                                    <input class="form-control" name="senha" type="password" placeholder='Senha' value=""
                                        disabled>
                                </div>
                            </div>
                            <div class="col-sm-2 col-xl-4 offset-xl-2">
                                <div class="form-group">
                                    <label></label>
                                    <input class="form-control" name="confirmarSenha" type="password"
                                        placeholder='Confirmar Senha' value="" disabled>
                                    <small></small>
                                </div>
                            </div>
                        </div><!-- End: 5-column form row -->

                        <!-- Start: 4-column form row -->
                        <div class="form-row" style="height: 78px;">
                            <div class="col-xl-3 offset-xl-1">
                                <label></label>
                                <select class="form-control" name="nivelUsuario" value="<?php echo $nivelUsuario ?>"
                                    style="margin: 23px 0;">
                                    <optgroup label="Tipo de Usuário">
                                        <option value="1">Administrador</option>
                                        <option value="2">Suporte</option>
                                        <option value="3" selected="">Usuário</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-xl-3 offset-xl-1">
                                <label></label>
                                <select class="form-control" name="unidade" value="<?php echo $unidade ?>"
                                    style="margin: 23px 0;">
                                    <optgroup label="Unidade">
                                        <?php
                                        // Conectar ao banco de dados
                                        include 'conexao.php'; // Lembre-se do ponto e vírgula aqui
                                    
                                        // Verificar conexão
                                        if ($conn->connect_error) {
                                            die("Conexão falhou: " . $conn->connect_error);
                                        }

                                        $sql = "SELECT unidade FROM unidade";
                                        $result = $conn->query($sql);

                                        if ($result->num_rows > 0) {
                                            // Saída dos dados de cada linha
                                            while ($row = $result->fetch_assoc()) {
                                                echo '<option value="' . $row['unidade'] . '">' . $row['unidade'] . '</option>';
                                            }
                                        } else {
                                            echo '<option value="">Nenhuma unidade encontrada</option>';
                                        }
                                        $conn->close();
                                        ?>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-sm-3 col-xl-2 offset-xl-1">
                                <label></label>
                                <select class="form-control" name="status" value="<?php echo $status ?>"
                                    style="margin: 23px 0;">
                                    <optgroup label="Situação">
                                        <option value="Ativo" selected="">Ativo</option>
                                        <option value="Inativo">Inativo</option>
                                    </optgroup>
                                </select>

                            </div>
                        </div><!-- End: 4-column form row -->

                        <!-- Start: File Upload Row -->
                        <div class="form-row" style="margin-top: 20px;">
                            <div class="col-xl-6 offset-xl-3">
                                <div class="form-group">
                                    <label>Foto de Perfil Atual</label><br>
                                    <?php if (!empty($array['foto_perfil'])): ?>
                                        <img src="<?php echo htmlspecialchars($array['foto_perfil']); ?>" alt="Foto de Perfil"
                                            class="img-thumbnail" style="max-width: 150px; margin-bottom: 10px;">
                                    <?php else: ?>
                                        <p>Sem foto de perfil.</p>
                                    <?php endif; ?>
                                    <br>
                                    <label for="foto_perfil">Alterar Foto de Perfil</label>
                                    <input type="file" class="form-control-file" name="foto_perfil" id="foto_perfil"
                                        accept="image/*">
                                </div>
                            </div>
                        </div><!-- End: File Upload Row -->

                        <!-- Start: 6-column form row -->
                        <div class="form-row" style="margin-top: 50px;">
                            <div class="col-lg-4 col-xl-4 offset-lg-4 offset-xl-4" style="border-radius: 15px;">
                                <button class="btn btn-success btn-block active text-white pulse animated btn-user"
                                    type="submit"
                                    style="background: rgb(44,64,74); border-radius: 10px;  border-width: 0px; height: 50px;">Atualizar</button>
                            </div>
                        </div><!-- End: 6-column form row -->
                    <?php } ?>
                </form>

            </div><!-- Start: Simple footer by krissy -->
            <section class="text-center footer" style="background: rgb(34,40,39);">
                <!-- Start: Footer text -->
                <p style="margin-bottom: 0px;font-size: 15px;">DEGB&nbsp;Copyright © 2015-2024&nbsp;<br></p>
                <!-- End: Footer text -->
            </section><!-- End: Simple footer by krissy -->
        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
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

    <script>
        function passwordvalidation(input) {
            if (input.value != document.getElementById('Senha').value) {
                input.setCustomValidity('Senha diferente da anterior');
            } else {
                input.setCustomValidity('');
            }
        }
    </script>
</body>

</html>