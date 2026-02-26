<?php include 'auth.php'; ?>
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
    <style>
        .hero-section {
            background: linear-gradient(135deg, #2c404a 0%, #1a2a33 100%);
            padding: 60px 0;
            border-radius: 20px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .support-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .support-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border-color: #2c404a;
        }

        .support-card i {
            font-size: 2.5rem;
            color: #2c404a;
            margin-bottom: 15px;
        }

        .contact-info-box {
            background: rgba(44, 64, 74, 0.03);
            border-radius: 15px;
            padding: 30px;
            border: 1px dashed #2c404a;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .info-item i {
            width: 45px;
            height: 45px;
            background: #2c404a;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .modern-input {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }

        .modern-input:focus {
            border-color: #2c404a;
            box-shadow: 0 0 0 0.2rem rgba(44, 64, 74, 0.1);
        }

        .map-container {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
        }
    </style>
    <?php include 'sidebar_style.php'; ?>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion p-0">
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
                        <form
                            class="form-inline d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search position-relative">
                            <div class="input-group">
                                <input class="bg-light form-control border-0 small" type="text"
                                    placeholder="Pesquisar..." id="globalSearchInput" autocomplete="off">
                                <div class="input-group-append"><button class="btn btn-primary py-0" type="button"
                                        style="background: rgb(44,64,74);"><i class="fas fa-search"></i></button></div>
                            </div>
                            <div id="globalSearchResults" class="dropdown-menu shadow animated--grow-in"
                                style="width: 100%; display: none;"></div>
                        </form>
                        <ul class="navbar-nav flex-nowrap ml-auto">
                            <li class="nav-item dropdown no-arrow">
                                <a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown"
                                    href="#">
                                    <span
                                        class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span>
                                    <img class="border rounded-circle img-profile"
                                        src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/avatar1.jpeg'; ?>">
                                </a>
                                <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in">
                                    <a class="dropdown-item" href="profile.php"><i
                                            class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a>
                                    <a class="dropdown-item" href="configuracoes.php"><i
                                            class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a>
                                    <a class="dropdown-item" href="equipamentos.php?status=Manutencao"><i
                                            class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>Ativos em
                                        Manutenção</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="logout.php" class="dropdown-item"><i
                                            class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                </div>
                            </li>
                        </ul>
                </nav>
                <div class="container-fluid" style="padding-left: 23px; padding-right: 23px;">
                    <!-- Hero Section -->
                    <div class="hero-section text-center">
                        <h2 class="font-weight-bold" style="font-size: 2.5rem;">Como podemos ajudar você hoje?</h2>
                        <p class="lead opacity-75">Sua central de suporte e inteligência para gestão de ativos.</p>
                    </div>

                    <!-- Quick Action Cards -->
                    <div class="row mb-5">
                        <div class="col-md-4 mb-4" onclick="window.location.href='agent.php'">
                            <div class="support-card">
                                <i class="fas fa-robot"></i>
                                <h5>Assistente IA</h5>
                                <p class="text-muted small">Tire dúvidas rápidas sobre o sistema e procedimentos com
                                    nossa inteligência artificial.</p>
                                <span class="btn btn-sm btn-outline-dark border-radius-pill">Acessar IA</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4" onclick="window.location.href='chamados.php'">
                            <div class="support-card">
                                <i class="fas fa-ticket-alt"></i>
                                <h5>Central de Chamados</h5>
                                <p class="text-muted small">Abra novas solicitações técnicas ou acompanhe o status dos
                                    seus pedidos pendentes.</p>
                                <span class="btn btn-sm btn-outline-dark border-radius-pill">Ver Chamados</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4" onclick="window.location.href='documentacao.php'">
                            <div class="support-card">
                                <i class="fas fa-book"></i>
                                <h5>Documentação</h5>
                                <p class="text-muted small">Acesse manuais de usuário, FAQs e guias de boas práticas
                                    para gestão do seu inventário.</p>
                                <span class="btn btn-sm btn-outline-dark border-radius-pill">Ver Manuais</span>
                            </div>
                        </div>
                    </div>

                    <!-- Main Support Layout -->
                    <div class="card shadow border-0 overflow-hidden" style="border-radius: 20px;">
                        <div class="row no-gutters">
                            <!-- Contact Info -->
                            <div class="col-lg-5 p-5 bg-light">
                                <h4 class="font-weight-bold mb-4">Canais de Atendimento</h4>
                                <p class="text-muted mb-5">Se preferir, utilize nossos canais diretos para um
                                    atendimento mais urgente.</p>

                                <div class="info-item">
                                    <i class="fab fa-whatsapp"></i>
                                    <div>
                                        <div class="font-weight-bold">WhatsApp Suporte</div>
                                        <div class="text-muted">(11) 96843-55543</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <i class="far fa-envelope"></i>
                                    <div>
                                        <div class="font-weight-bold">E-mail Corporativo</div>
                                        <div class="text-muted">suporte@degb.com.br</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <i class="far fa-clock"></i>
                                    <div>
                                        <div class="font-weight-bold">Horário de Atendimento</div>
                                        <div class="text-muted">Segunda a Sexta: 08:00 às 18:00</div>
                                    </div>
                                </div>

                                <div class="mt-5 p-4 contact-info-box text-center">
                                    <p class="small text-muted mb-0">Localização Física</p>
                                    <div class="font-weight-bold">Alphaville, Barueri - SP, Brasil</div>
                                </div>
                            </div>

                            <!-- Contact Form -->
                            <div class="col-lg-7 p-5 bg-white">
                                <h4 class="font-weight-bold mb-4">Envie uma Mensagem</h4>
                                <form>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="small font-weight-bold">Seu Nome</label>
                                            <input type="text" class="form-control modern-input"
                                                placeholder="Ex: João Silva">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="small font-weight-bold">Empresa/Centro de Custo</label>
                                            <input type="text" class="form-control modern-input"
                                                placeholder="Ex: TI Matriz">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small font-weight-bold">Seu E-mail Ativo</label>
                                        <input type="email" class="form-control modern-input"
                                            placeholder="Ex: joao@empresa.com.br">
                                    </div>
                                    <div class="mb-4">
                                        <label class="small font-weight-bold">Descrição da Necessidade</label>
                                        <textarea class="form-control modern-input" rows="4"
                                            placeholder="Como podemos ajudar você?"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-dark btn-block font-weight-bold py-3 shadow"
                                        style="border-radius: 12px; background: #2c404a;">
                                        ENVIAR MENSAGEM <i class="fas fa-paper-plane ml-2"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Map Section -->
                    <div class="map-container">
                        <iframe allowfullscreen="" frameborder="0"
                            src="https://www.google.com/maps/embed/v1/place?key=AIzaSyBPk6EEDKnLhNYxq-pI77Q4934lsuEC318&amp;q=Alphaville%2C+Barueri+-+SP%2C+Brasil&amp;zoom=15"
                            width="100%" height="400" style="border:0;"></iframe>
                    </div>
                </div>
            </div>
            <footer class="sticky-footer">
                <section class="text-center footer" style="padding: 10px; background-color: #212121; color: white;">
                    <p style="margin-bottom: 0px; font-size: 15px;">DEGB&nbsp;Copyright © 2015-2024<br></p>
                </section>
            </footer>

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
    <script src="/assets/js/global_search.js"></script>
</body>

</html>