<?php include 'performance_header.php'; ?>
<?php
include 'conexao.php';
include_once 'language.php';
/**
 * PÁGINA DE ACESSO: login.php
 * Interface de autenticação e processamento de credenciais dos usuários.
 */
?>
<!DOCTYPE html>
<html lang="<?php echo strtolower($_SESSION['idioma'] ?? 'pt-BR'); ?>"> <!-- Define o idioma dinamicamente -->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Login'); ?> - Asset Management</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <?php renderPerformanceHints(); ?>
    <?php include_once 'sidebar_style.php'; ?>
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif !important;
            background: #0d1418 !important;
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            color: #fff;
            overflow: hidden;
            position: relative;
        }

        /* Ambient Background Blobs */
        .ambient-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            filter: blur(80px);
        }

        .blob {
            position: absolute;
            background: rgba(28, 200, 138, 0.15);
            border-radius: 50%;
            animation: float 20s infinite alternate ease-in-out;
        }

        .blob-1 { width: 500px; height: 500px; top: -100px; left: -100px; background: rgba(44, 64, 74, 0.4); }
        .blob-2 { width: 600px; height: 600px; bottom: -150px; right: -150px; background: rgba(28, 200, 138, 0.1); animation-delay: -5s; }
        .blob-3 { width: 400px; height: 400px; top: 40%; left: 50%; background: rgba(78, 115, 223, 0.1); animation-delay: -10s; }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(100px, 50px) scale(1.1); }
        }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem;
            width: 100%;
            z-index: 10;
        }

        /* Glassmorphism Card Upgrade */
        .card.shadow-lg {
            background: rgba(255, 255, 255, 0.03) !important;
            backdrop-filter: blur(25px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(25px) saturate(180%) !important;
            border: none !important;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.6) !important;
            border-radius: 24px !important;
            width: 100% !important;
            max-width: 1400px !important;
            overflow: hidden !important;
        }

        .bg-login-image {
            border-radius: 0 !important;
            position: relative;
        }

        .bg-login-image::after {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            background: linear-gradient(90deg, transparent, rgba(13, 20, 24, 0.3));
        }

        .login-padding {
            padding: 3.5rem !important;
            background: rgba(255, 255, 255, 1) !important; /* Contrast container for the form */
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Language Switcher */
        .lang-switcher {
            position: absolute;
            top: 2rem;
            right: 2.5rem;
            z-index: 100;
            display: flex;
            gap: 10px;
        }

        .lang-btn {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none !important;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .lang-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
            color: #fff;
        }

        .lang-btn.active {
            background: #2c404a;
            border-color: #3e5b69;
        }

        .brand-title {
            color: #1a2a33 !important;
            font-weight: 900 !important;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-size: 2.2rem !important;
            margin-bottom: 2.5rem !important;
        }

        .btn-user {
            border-radius: 12px !important;
            padding: 14px !important;
            font-size: 1rem !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: #1a2a33 !important;
        }

        /* Premium Footer Integration */
        .footer-premium {
            padding: 2.5rem 0;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: auto;
            z-index: 10;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
            font-weight: 500;
            margin: 0 15px;
            transition: color 0.3s;
            text-decoration: none !important;
        }

        .footer-link:hover { color: #fff; }

        .copyright {
            margin-top: 1.5rem;
            color: rgba(255, 255, 255, 0.2);
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        @media (max-width: 991px) {
            .login-padding { padding: 2.5rem !important; }
            .card.shadow-lg { max-width: 500px !important; }
        }

    </style>
</head>

<body id="page-top">
    <?php startNProgress(); ?>
    <div class="ambient-bg">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="lang-switcher animate__animated animate__fadeIn">
        <a href="?lang=pt-BR" class="lang-btn <?php echo ($_SESSION['idioma'] ?? 'pt-BR') === 'pt-BR' ? 'active' : ''; ?>">
            <img src="https://flagcdn.com/w20/br.png" alt="PT" width="18"> PT
        </a>
        <a href="?lang=en-US" class="lang-btn <?php echo ($_SESSION['idioma'] ?? '') === 'en-US' ? 'active' : ''; ?>">
            <img src="https://flagcdn.com/w20/us.png" alt="EN" width="18"> EN
        </a>
    </div>

    <div class="main-content premium-page-fade">
        <div class="container d-flex justify-content-center align-items-center">
            <div class="row justify-content-center w-100">
                <div class="col-xl-12 col-lg-12 d-flex justify-content-center">
                    <form action="autenticador.php" method="post" style="width: 100%;">
                        <div class="card shadow-lg pulse animated">
                            <div class="card-body p-0">
                                <div class="row no-gutters">
                                    <div class="col-lg-6 d-none d-lg-flex">
                                        <div class="flex-grow-1 bg-login-image"
                                            style="background: url(&quot;/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90&quot;) center / cover no-repeat;">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="login-padding">
                                            <div class="text-center w-100">
                                                <?php if (isset($_GET['timeout'])): ?>
                                                    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert"
                                                        style="border-radius: 12px; border: none; background-color: rgba(246, 194, 62, 0.1); color: #f6c23e;">
                                                        <i class="fas fa-exclamation-circle mr-2"></i>
                                                        <strong><?php echo __('Sessão Expirada!'); ?></strong>
                                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                                <h3 class="brand-title"><strong>ASSET MANAGEMENT</strong></h3>
                                            </div>
                                            <div class="user w-100">
                                                <div class="form-group mb-4">
                                                    <label class="small font-weight-bold text-muted ml-1"><?php echo __('E-mail'); ?></label>
                                                    <input class="form-control form-control-user" type="email"
                                                        id="exampleInputEmail" placeholder="exemplo@empresa.com"
                                                        name="email" required style="border: 1px solid #eee; background: #fafafa;">
                                                </div>
                                                <div class="form-group mb-4">
                                                    <label class="small font-weight-bold text-muted ml-1"><?php echo __('Senha'); ?></label>
                                                    <input class="form-control form-control-user" type="password"
                                                        id="exampleInputPassword" placeholder="••••••••" name="senha"
                                                        required style="border: 1px solid #eee; background: #fafafa;">
                                                </div>
                                                <div class="form-group mb-4 d-flex justify-content-between align-items-center">
                                                    <div class="custom-control custom-checkbox small">
                                                        <input class="custom-control-input" type="checkbox" id="formCheck-1">
                                                        <label class="custom-control-label text-muted" for="formCheck-1"><?php echo __('Lembrar-me'); ?></label>
                                                    </div>
                                                    <a class="small font-weight-bold" href="esqueceu_senha.php" style="color: #6c757d;"><?php echo __('Esqueceu a senha?'); ?></a>
                                                </div>
                                                <button class="btn btn-primary btn-block text-white btn-user border-0"
                                                    type="submit"><?php echo __('Entrar'); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer-premium text-center">
        <div class="container">
            <div class="d-flex justify-content-center flex-wrap">
                <a href="#" class="footer-link"><?php echo __('Suporte'); ?></a>
                <a href="documentacao.php" class="footer-link"><?php echo __('Documentação'); ?></a>
                <a href="#" class="footer-link"><?php echo __('Termos de Uso'); ?></a>
            </div>
            <p class="copyright">
                &copy; <?php echo date('Y'); ?> Asset Management System. <?php echo __('Todos os direitos reservados.'); ?>
            </p>
        </div>
    </footer>

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
    </script>
    <?php include 'performance_footer.php'; ?>
</body>

</html>