<?php
/**
 * GESTÃO DE USUÁRIOS: cadastro_de_usuario.php
 * Interface administrativa para criação de novos acessos ao sistema Asset MGT.
 * Define níveis de permissão (Admin, Suporte, Usuário) e dados contratuais.
 */
include 'auth.php';    // Validação de autoridade
include 'conexao.php'; // Banco de Dados

// LÓGICA DE MATRÍCULA: Sugere o próximo ID disponível como matrícula provisória
$sql_id = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = 'usuarios'";
$res_id = $conn->query($sql_id);
$next_id = $res_id->fetch_assoc()['AUTO_INCREMENT'];
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
                    <h3 class="text-dark mb-1">Cadastro de Usuário</h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <!-- Start: Multi-row Form -->
                            <form action="inserir_usuario.php" method="post" enctype="multipart/form-data">
                                <!-- Row 1: Nome, Sobrenome -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Nome</label>
                                            <input class="form-control" name="nome" type="text" placeholder="Ex: João"
                                                required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Sobrenome</label>
                                            <input class="form-control" name="sobrenome" type="text" placeholder="Ex: Silva"
                                                required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 2: Usuário AD, Função -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Usuário AD</label>
                                            <input class="form-control" name="usuarioAD" type="text"
                                                placeholder="Ex: joao.silva" required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Função</label>
                                            <input class="form-control" name="funcao" type="text"
                                                placeholder="Ex: Analista de TI" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 3: Data Nasc, Email -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Data de Nascimento</label>
                                            <input class="form-control" name="dataNascimento" type="date" required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Email</label>
                                            <input class="form-control" name="email" type="email"
                                                placeholder="email@exemplo.com" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 4: CPF, Centro de Custo -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold text-danger">CPF (Somente Números)</label>
                                            <input class="form-control" name="cpf" id="cpf" type="text"
                                                placeholder="000.000.000-00" required="" maxlength="14"
                                                oninput="maskCPF(this)">
                                            <div id="cpf-error" class="text-danger small mt-1" style="display:none;">CPF
                                                Inválido
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Centro de Custo</label>
                                            <select class="form-control" name="centroDeCusto">
                                                <option value="Nenhum">Nenhum</option>
                                                <?php
                                                // CENTROS DE CUSTO: Permite vincular o usuário à sua unidade orçamentária
                                                include_once 'conexao.php';
                                                $sql_cc = "SELECT nomeSetor FROM centro_de_custo ORDER BY nomeSetor ASC";
                                                $res_cc = $conn->query($sql_cc);
                                                if ($res_cc && $res_cc->num_rows > 0) {
                                                    while ($row_cc = $res_cc->fetch_assoc()) {
                                                        echo '<option value="' . $row_cc['nomeSetor'] . '">' . $row_cc['nomeSetor'] . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 5: Matrícula, Telefone -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Matrícula (Automático)</label>
                                            <input class="form-control" name="matricula" type="text"
                                                value="<?php echo $next_id; ?>" readonly required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Telefone</label>
                                            <input class="form-control" name="telefone" type="text" inputmode="tel"
                                                placeholder="(99) 99999-9999" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 6: Tipo de Contrato, Tipo de Usuário -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Tipo de Contrato</label>
                                            <select class="form-control" name="tipoContrato" required="">
                                                <option value="CLT">CLT</option>
                                                <option value="PJ">PJ</option>
                                                <option value="Cooperativa">Cooperativa</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Tipo de Usuário</label>
                                            <select class="form-control" name="nivelUsuario" required="">
                                                <option value="1">Administrador</option>
                                                <option value="2">Suporte</option>
                                                <option value="3" selected="">Usuário</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 7: Unidade, Status -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Unidade</label>
                                            <select class="form-control" name="unidade" required="">
                                                <?php
                                                include 'conexao.php';
                                                $sql_un = "SELECT unidade FROM unidade";
                                                $res_un = $conn->query($sql_un);
                                                if ($res_un->num_rows > 0) {
                                                    while ($row = $res_un->fetch_assoc()) {
                                                        echo '<option value="' . $row['unidade'] . '">' . $row['unidade'] . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="custom-control custom-switch" style="margin-top: 35px;">
                                            <input type="hidden" name="status" value="Inativo">
                                            <input type="checkbox" class="custom-control-input" id="statusSwitch"
                                                name="status" value="Ativo" checked>
                                            <label class="custom-control-label" for="statusSwitch">Usuário Ativo</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 8: Senha, Confirmação -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Senha</label>
                                            <input class="form-control" name="senha" id="Senha" type="password"
                                                placeholder="********" required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Confirmação de Senha</label>
                                            <input class="form-control" name="confirmarSenha" type="password"
                                                placeholder="********" required="" oninput="passwordvalidation(this)">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 9: Foto Perfil -->
                                <div class="form-row">
                                    <div class="col-xl-6 offset-xl-3 mt-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold">Foto de Perfil (Opcional)</label>
                                            <input type="file" class="form-control-file" name="foto_perfil" id="foto_perfil"
                                                accept="image/*">
                                        </div>
                                    </div>
                                </div>

                                <!-- Start: 6-column form row -->
                                <div class="form-row" style="margin-top: 50px;">
                                    <div class="col-lg-4 col-xl-4 offset-lg-4 offset-xl-4" style="border-radius: 15px;">
                                        <button class="btn btn-success btn-block active text-white pulse animated btn-user"
                                            type="submit"
                                            style="background: rgb(44,64,74); border-radius: 10px;  border-width: 0px; height: 50px;">Cadastrar</button>
                                    </div>
                                </div><!-- End: 6-column form row -->
                            </form><!-- End: Multi-row Form -->
                        </div>
                    </div>
                </div><!-- End: Multi-row Form -->

            </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
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

        //
        <script>
            function passwordvalidation(input) {
                if (input.value != document.getElementById('Senha').value) {
                    input.setCustomValidity('Senha diferente da anterior');
                } else {
                    input.setCustomValidity('');
                }
            }

            function maskCPF(i) {
                var v = i.value;
                if (isNaN(v[v.length - 1])) {
                    i.value = v.substring(0, v.length - 1);
                    return;
                }
                i.setAttribute("maxlength", "14");
                if (v.length == 3 || v.length == 7) i.value += ".";
                if (v.length == 11) i.value += "-";

                if (v.length == 14) {
                    validateCPF(v);
                } else {
                    document.getElementById('cpf-error').style.display = 'none';
                    i.setCustomValidity('');
                }
            }

            // VALIDAÇÃO TÉCNICA DE CPF (Algoritmo de dígitos verificadores)
            function validateCPF(cpf) {
                cpf = cpf.replace(/[^\d]+/g, '');
                if (cpf == '') return false;
                // Elimina CPFs invalidos conhecidos
                if (cpf.length != 11 ||
                    cpf == "00000000000" ||
                    cpf == "11111111111" ||
                    cpf == "22222222222" ||
                    cpf == "33333333333" ||
                    cpf == "44444444444" ||
                    cpf == "55555555555" ||
                    cpf == "66666666666" ||
                    cpf == "77777777771" ||
                    cpf == "88888888888" ||
                    cpf == "99999999999") {
                    showCPFError(true);
                    return false;
                }
                // Valida 1o digito	
                add = 0;
                for (i = 0; i < 9; i++)
                    add += parseInt(cpf.charAt(i)) * (10 - i);
                rev = 11 - (add % 11);
                if (rev == 10 || rev == 11)
                    rev = 0;
                if (rev != parseInt(cpf.charAt(9))) {
                    showCPFError(true);
                    return false;
                }
                // Valida 2o digito	
                add = 0;
                for (i = 0; i < 10; i++)
                    add += parseInt(cpf.charAt(i)) * (11 - i);
                rev = 11 - (add % 11);
                if (rev == 10 || rev == 11)
                    rev = 0;
                if (rev != parseInt(cpf.charAt(10))) {
                    showCPFError(true);
                    return false;
                }
                showCPFError(false);
                return true;
            }

            // Interface Visual de Erro no CPF
            function showCPFError(hasError) {
                const errorEl = document.getElementById('cpf-error');
                const inputEl = document.getElementById('cpf');
                if (hasError) {
                    errorEl.style.display = 'block';
                    inputEl.setCustomValidity('CPF Inválido');
                } else {
                    errorEl.style.display = 'none';
                    inputEl.setCustomValidity('');
                }
            }

            document.querySelector('form').addEventListener('submit', function (e) {
                const cpf = document.getElementById('cpf').value;
                if (!validateCPF(cpf)) {
                    e.preventDefault();
                    alert('Por favor, insira um CPF válido.');
                }
            });
        </script>
        <script src="/assets/js/global_search.js"></script>
        <script src="/assets/js/global_search.js"></script>
</body>

</html>