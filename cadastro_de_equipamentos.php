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
    <link rel="stylesheet" href="/assets/css/Form-Select---Full-Date---Month-Day-Year.css?h=7b6a3c2cb7894fdb77bae43c70b92224">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightpick@1.3.4/css/lightpick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="/assets/css/Map-Clean.css?h=bdd15207233b27ebc7c6fc928c71b34c">
    <link rel="stylesheet" href="/assets/css/Modern-Contact-Form.css?h=af67b929d317df499a992472a9bb8fcc">
    <link rel="stylesheet" href="/assets/css/Multi-Select-Dropdown-by-Jigar-Mistry.css?h=28bd9d636c700fbf60086e2bcb002efb">
    <link rel="stylesheet" href="/assets/css/Password-Strenght-Checker---Ambrodu-1.css?h=1af6ac373aa34a3b40f3d87a4f494eaf">
    <link rel="stylesheet" href="/assets/css/Password-Strenght-Checker---Ambrodu.css?h=5818638767f362b9d58a96550bd9a9a3">
    <link rel="stylesheet" href="/assets/css/Simple-footer-by-krissy.css?h=73316da5ae5ad6b51632cd2e5413f263">
    <link rel="stylesheet" href="/assets/css/TR-Form.css?h=ce0bc58b5b8027e2406229d460f4d895">
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0" style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0"><a class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#">
                    <div class="sidebar-brand-icon rotate-n-15"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-layout-distribute-horizontal" style="width: 30px;height: 30px;">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <line x1="4" y1="4" x2="20" y2="4"></line>
                            <line x1="4" y1="20" x2="20" y2="20"></line>
                            <rect x="6" y="9" width="12" height="6" rx="2"></rect>
                        </svg></div>
                    <div class="sidebar-brand-text mx-3"><span>ASSET MGT</span></div>
                </a>
                <hr class="sidebar-divider my-0">
                <ul class="navbar-nav text-light" id="accordionSidebar">
                    <li class="nav-item"><a class="nav-link" href="/index.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/inicio.php"><i class="fas fa-home"></i><span> Início</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/usuarios.php"><i class="fas fa-user-alt"></i><span> Usuários</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/centro_de_custo.php"><i class="fas fa-file-invoice-dollar"></i><span> Centro de Custo</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/fornecedores.php"><i class="fas fa-hands-helping"></i><span> Fornecedores</span></a></li>
                    <li class="nav-item"><a class="nav-link active" href="/equipamentos.php"><i class="fas fa-boxes"></i><span> Ativos</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/relatorios.php"><i class="fas fa-scroll"></i><span> Relatórios</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/suporte.php"><i class="fas fa-user-cog"></i><span> Suporte</span></a></li>
                </ul>
                <div class="text-center d-none d-md-inline"><button class="btn rounded-circle border-0" id="sidebarToggle" type="button"></button></div>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top" style="margin: 23px;">
                    <div class="container-fluid"><button class="btn btn-link d-md-none rounded-circle mr-3" id="sidebarToggleTop-1" type="button"><i class="fas fa-bars"></i></button>
                        <form class="form-inline d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                            <div class="input-group"><input class="bg-light form-control border-0 small" type="text" placeholder="Pesquisar...">
                                <div class="input-group-append"><button class="btn btn-primary py-0" type="button" style="background: rgb(44,64,74);"><i class="fas fa-search"></i></button></div>
                            </div>
                        </form>
                        <ul class="navbar-nav flex-nowrap ml-auto">
                            <li class="nav-item dropdown d-sm-none no-arrow"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#"><i class="fas fa-search"></i></a>
                                <div class="dropdown-menu dropdown-menu-right p-3 animated--grow-in" aria-labelledby="searchDropdown">
                                    <form class="form-inline mr-auto navbar-search w-100">
                                        <div class="input-group"><input class="bg-light form-control border-0 small" type="text" placeholder="Search for ...">
                                            <div class="input-group-append"><button class="btn btn-primary py-0" type="button"><i class="fas fa-search"></i></button></div>
                                        </div>
                                    </form>
                                </div>
                            </li>
                            <li class="nav-item dropdown no-arrow mx-1">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#"></a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-list animated--grow-in">
                                        <h6 class="dropdown-header">alerts center</h6><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="mr-3">
                                                <div class="bg-primary icon-circle"><i class="fas fa-file-alt text-white"></i></div>
                                            </div>
                                            <div><span class="small text-gray-500">December 12, 2019</span>
                                                <p>A new monthly report is ready to download!</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="mr-3">
                                                <div class="bg-success icon-circle"><i class="fas fa-donate text-white"></i></div>
                                            </div>
                                            <div><span class="small text-gray-500">December 7, 2019</span>
                                                <p>$290.29 has been deposited into your account!</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="mr-3">
                                                <div class="bg-warning icon-circle"><i class="fas fa-exclamation-triangle text-white"></i></div>
                                            </div>
                                            <div><span class="small text-gray-500">December 2, 2019</span>
                                                <p>Spending Alert: We've noticed unusually high spending for your account.</p>
                                            </div>
                                        </a><a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                                    </div>
                                </div>
                            </li>
                            <li class="nav-item dropdown no-arrow mx-1">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#"></a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-list animated--grow-in">
                                        <h6 class="dropdown-header">alerts center</h6><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle" src="/assets/img/avatars/avatar4.jpeg?h=fefb30b61c8459a66bd338b7d790c3d5">
                                                <div class="bg-success status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>Hi there! I am wondering if you can help me with a problem I've been having.</span></div>
                                                <p class="small text-gray-500 mb-0">Emily Fowler - 58m</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle" src="/assets/img/avatars/avatar2.jpeg?h=5d142be9441885f0935b84cf739d4112">
                                                <div class="status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>I have the photos that you ordered last month!</span></div>
                                                <p class="small text-gray-500 mb-0">Jae Chun - 1d</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle" src="/assets/img/avatars/avatar3.jpeg?h=c5166867f10a4e454b5b2ae8d63268b3">
                                                <div class="bg-warning status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>Last month's report looks great, I am very happy with the progress so far, keep up the good work!</span></div>
                                                <p class="small text-gray-500 mb-0">Morgan Alvarez - 2d</p>
                                            </div>
                                        </a><a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="dropdown-list-image mr-3"><img class="rounded-circle" src="/assets/img/avatars/avatar5.jpeg?h=35dc45edbcda6b3fc752dab2b0f082ea">
                                                <div class="bg-success status-indicator"></div>
                                            </div>
                                            <div class="font-weight-bold">
                                                <div class="text-truncate"><span>Am I a good boy? The reason I ask is because someone told me that people say this to all dogs, even if they aren't good...</span></div>
                                                <p class="small text-gray-500 mb-0">Chicken the Dog · 2w</p>
                                            </div>
                                        </a><a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                                    </div>
                                </div>
                                <div class="shadow dropdown-list dropdown-menu dropdown-menu-right" aria-labelledby="alertsDropdown"></div>
                            </li>
                            <div class="d-none d-sm-block topbar-divider"></div>
                            <li class="nav-item dropdown no-arrow">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#"><span class="d-none d-lg-inline mr-2 text-gray-600 small">Eduardo Gomes</span><img class="border rounded-circle img-profile" src="/assets/img/avatars/Captura%20de%20Tela%202021-08-04%20às%2012.25.13.png?h=fcfb924f0ac1ab5f595f029bf526e62d"></a>
                                    <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in"><a class="dropdown-item" href="#"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a><a class="dropdown-item" href="#"><i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a><a class="dropdown-item" href="#"><i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>Desativar conta</a>
                                        <div class="dropdown-divider"></div><a class="dropdown-item" href="login.php"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="container-fluid">
                    <h3 class="text-dark mb-1">Cadastro de Ativo</h3>
                </div><!-- Start: Multi-row Form -->
                <form action="inserir_equipamento.php" method="post">
                
                    <!-- Start: 1-column form row -->
                    <div class="form-row">
                        <div class="col-sm-12 col-xl-2 offset-xl-1">
                            <div class="form-group"><label></label><select class="form-control" name="categoria" required="">
                                    <optgroup label="Categoria">
                                        <option value="" selected="">Selecione a Categoria</option>
                                        <option value="Desktop">Notebook</option>
                                        <option value="Monitor">Monitor</option>
                                        <option value="Teclado">Teclado</option>
                                        <option value="Mouse">Mouse</option>
                                        <option value="Totem">Totem</option>
                                        <option value="Impressora">Impressora</option>
                                        <option value="Multifuncional">Multifuncional</option>
                                        <option value="Telefone">Telefone</option>
                                        <option value="Etiquetadora">Etiquetadora</option>
                                        <option value="MS365">MS365</option>
                                    </optgroup>
                                </select></div>
                        </div>
                        <div class="col-sm-6 col-xl-6 offset-xl-1">
                            <div class="form-group"><label></label><input class="form-control" name="fabricante" type="text" placeholder="Fabricante" required=""></div>
                        </div>
                    </div><!-- End: 1-column form row -->
                    <!-- Start: 2-column form row -->
                    <div class="form-row">
                        <div class="col-sm-6 col-xl-4 offset-xl-1">
                            <div class="form-group"><label></label><input class="form-control" name="modelo" type="text" placeholder="Modelo" required=""></div>
                        </div>
                        <div class="col-sm-6 col-xl-4 offset-xl-1">
                            <div class="form-group"><label></label><input class="form-control" name="tag" type="text" placeholder="Tag" required=""></div>
                        </div>
                        <div class="col-sm-6 col-xl-4 offset-xl-1">
                            <div class="form-group"><label></label><input class="form-control" name="hostName" type="text" placeholder="Host Name" required=""></div>
                        </div>
                        <div class="col-sm-6 col-xl-4 offset-xl-1">
                            <div class="form-group"><label></label><input class="form-control" name="ip" type="text" placeholder="IP" required=""></div>
                        </div>
                    </div><!-- End: 2-column form row -->
                    <!-- Start: 3-column form row -->
                    <div class="form-row" style="height: 80px;">
                        <div class="col-sm-4 col-xl-2 offset-xl-1">
                            <div class="form-group"><input class="form-control" name="macAdress" type="text" placeholder="MAC Adress" required="" style="margin-top: 24px;"></div>
                        </div>
                        <div class="col-sm-4 col-xl-1">
    <select class="form-control" name="status" required style="margin-top: 24px" required="";>
        <optgroup label="Status" >
            <option value="Ativo">Ativo</option>
            <option value="Inativo">Inativo</option>
        </optgroup>
    </select>
</div>
                        <div class="col-xl-1"><input class="form-control" name="dataAtivacao" type="date" placeholder="Data de Entrada" required="" style="margin-top: 24px;"></div>
                        
                        <div class="col-sm-6 col-xl-4 offset-xl-1">
                            <div class="form-group"><label></label><input class="form-control" name="centroDeCusto" type="text" placeholder="Centro de custo" required=""></div>
                        </div>
                    </div><!-- End: 3-column form row -->
                    <!-- Start: 3-column form row -->
                    <div class="form-row" style="height: 80px;">
                        <div class="col-sm-4 col-xl-5 offset-xl-1"><input class="form-control-file d-xl-flex" name="imagem" type="file" style="margin-top: 24px;height: 30px;" accept="image/*"></div>
                    </div><!-- End: 3-column form row -->
                    <!-- Start: 4-column form row -->
                    <div class="form-row">
                        <div class="col-sm-3 col-xl-9 offset-xl-1" style="height: 200px;"><textarea class="form-control" name="descricao"  placeholder="Descrição..." style="height: 100px;margin-top: 10px; margin-bottom: 0px;"></textarea></div>
                        <div class="col-xl-4 offset-xl-4"><button class="btn btn-success btn-block active text-white pulse animated btn-user" type="submit" style="background: rgb(44,64,74);border-radius: 10px;padding: 30px, 30px;border-width: 0px;height: 50px;margin-top: 50px;">Cadastrar</button></div>
                        
                    </div><!-- End: 4-column form row -->
                </form><!-- End: Multi-row Form -->
                <!-- Start: Simple footer by krissy -->
                <footer class="bg-white sticky-footer" style="background: rgb(34,40,39);padding: 0;">
                <!-- Start: Simple footer by krissy -->
                <section class="text-center footer" style="padding: 10px;margin-top: 70px;">
                    <!-- Start: Footer text -->
                    <p style="margin-bottom: 0px;font-size: 15px;">DEGB&nbsp;Copyright © 2015-2024<br></p><!-- End: Footer text -->
                </section><!-- End: Simple footer by krissy -->
            </footer>
            </div>
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
</body>

</html>