<?php include 'auth.php';
include 'conexao.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Documentação - Asset Mgt</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <?php include 'sidebar_style.php'; ?>
    <style>
        .doc-header {
            background: linear-gradient(135deg, #2c404a 0%, #1a2a33 100%);
            padding: 40px 0;
            border-radius: 15px;
            margin-bottom: 30px;
            color: white;
        }

        .faq-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .accordion .card {
            border: none;
            margin-bottom: 10px;
        }

        .accordion .card-header {
            background: #f8f9fc;
            border-radius: 10px !important;
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .accordion .card-header:hover {
            background: #e3e6f0;
        }

        .manual-card {
            border: 1px solid #e3e6f0;
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s;
            height: 100%;
        }

        .manual-card:hover {
            border-color: #2c404a;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .manual-icon {
            font-size: 2.5rem;
            color: #e74a3b;
            margin-bottom: 15px;
        }

        .btn-download-manual {
            border-radius: 50px !important;
            padding-top: 10px !important;
            padding-bottom: 10px !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.7rem !important;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(44, 64, 74, 0.2) !important;
        }

        .btn-download-manual:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(44, 64, 74, 0.3) !important;
            background: #1a2a33 !important;
        }
    </style>
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
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top">
                    <!-- Global Search e User Profile omitidos por brevidade, mantendo consistência -->
                </nav>
                <div class="container-fluid">
                    <div class="doc-header text-center">
                        <h2 class="font-weight-bold">Centro de Conhecimento</h2>
                        <p>Guia completo para utilização e gestão de ativos no sistema.</p>
                    </div>

                    <div class="row">
                        <div class="col-lg-8 mb-4">
                            <div class="faq-section">
                                <h4 class="font-weight-bold mb-4">Perguntas Frequentes (FAQ)</h4>
                                <div class="accordion" id="faqAccordion">
                                    <div class="card">
                                        <div class="card-header" id="headingOne" data-toggle="collapse"
                                            data-target="#collapseOne">
                                            <h6 class="mb-0 font-weight-bold text-dark"><i
                                                    class="fas fa-question-circle mr-2"></i> Como cadastrar um novo
                                                equipamento?</h6>
                                        </div>
                                        <div id="collapseOne" class="collapse show" data-parent="#faqAccordion">
                                            <div class="card-body text-muted">
                                                Navegue até o menu "Inventário de Ativos" no lado esquerdo e clique no
                                                botão "Novo Equipamento". Preencha os campos obrigatórios como Tag,
                                                Modelo e Centro de Custo para finalizar o registro.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header collapsed" id="headingTwo" data-toggle="collapse"
                                            data-target="#collapseTwo">
                                            <h6 class="mb-0 font-weight-bold text-dark"><i
                                                    class="fas fa-question-circle mr-2"></i> Como enviar um ativo para
                                                manutenção?</h6>
                                        </div>
                                        <div id="collapseTwo" class="collapse" data-parent="#faqAccordion">
                                            <div class="card-body text-muted">
                                                No Inventário de Ativos, localize o equipamento desejado e clique no
                                                ícone de ferramentas (Manutenção). Informe o motivo do problema e
                                                confirme. O ativo passará a ser listado apenas na seção "Ativos em
                                                Manutenção".
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header collapsed" id="headingThree" data-toggle="collapse"
                                            data-target="#collapseThree">
                                            <h6 class="mb-0 font-weight-bold text-dark"><i
                                                    class="fas fa-question-circle mr-2"></i> Como gerar um relatório em
                                                PDF?</h6>
                                        </div>
                                        <div id="collapseThree" class="collapse" data-parent="#faqAccordion">
                                            <div class="card-body text-muted">
                                                Acesse o menu "Relatórios Internos". Lá você encontrará diversas
                                                categorias. Basta selecionar o tipo de dado desejado (Ativos, Chamados
                                                ou Financeiro) e clicar para gerar o documento oficial.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-4">
                            <div class="faq-section">
                                <h4 class="font-weight-bold mb-4">Downloads</h4>
                                <div class="manual-card mb-3">
                                    <div class="text-center">
                                        <i class="fas fa-file-pdf manual-icon"></i>
                                        <h6>Manual do Usuário Final</h6>
                                        <p class="small text-muted">Guia completo para solicitações de chamados e
                                            consultas (PDF).</p>
                                        <a href="gerar_manual_usuario.php" target="_blank"
                                            class="btn btn-sm btn-dark btn-block btn-download-manual"
                                            style="background: #2c404a;">DOWNLOAD MANUAL <i
                                                class="fas fa-download ml-1"></i></a>
                                    </div>
                                </div>
                                <div class="manual-card">
                                    <div class="text-center">
                                        <i class="fas fa-file-pdf manual-icon"></i>
                                        <h6>Guia de Admin</h6>
                                        <p class="small text-muted">Procedimentos técnicos para gestão de inventário e
                                            licenças (PDF).</p>
                                        <a href="gerar_guia_admin.php" target="_blank"
                                            class="btn btn-sm btn-dark btn-block btn-download-manual">DOWNLOAD GUIA <i
                                                class="fas fa-terminal ml-1"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="bg-white sticky-footer">
                <div class="container my-auto">
                    <div class="text-center my-auto copyright"><span>DEGB&nbsp;Copyright © 2024</span></div>
                </div>
            </footer>
        </div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/theme.js"></script>
</body>

</html>