<?php
/**
 * CADASTRO DE CENTRO DE CUSTO: cadastro_de_centro_de_custo.php
 * Interface para criação de novos centros de custo (setores/unidades).
 * Permite definir nome, código, ramal, unidade física e gestor responsável.
 */
include_once 'auth.php'; // Proteção de sessão
include_once 'conexao.php'; // Banco de Dados
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Cadastro de Centro de Custo'); ?> - Asset MGT</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=f3d9abe8d5aa7831c01bfaa2a1563712">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=41e93b37bc495fd67938799bb3a6adaf">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <?php include_once 'sidebar_style.php'; ?>
    <link rel="stylesheet" href="/assets/css/help_system.css">
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include_once 'sidebar_brand.php'; ?>
                <?php include_once 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <?php include_once 'topbar.php'; ?>
                <div class="container-fluid">
                    <h3 class="text-dark mb-4">
                        <i class="fas fa-plus-circle mr-2 text-success"></i>
                        <?php echo __('Cadastrar Centro de Custo'); ?>
                        <div class="help-indicator animate__animated animate__fadeIn" data-toggle="modal" data-target="#helpModal" title="<?php echo __('Guia de Cadastro'); ?>">
                            <i class="fas fa-question"></i>
                        </div>
                    </h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="inserir_centro_de_custo.php" method="post">
                                <!-- Row 1: Identificação do Setor -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="nomeSetor"><?php echo __('Nome do Setor'); ?></label>
                                            <input class="form-control" name="nomeSetor" id="nomeSetor" type="text" placeholder="<?php echo __('Ex: Tecnologia da Informação'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="codigo"><?php echo __('Código'); ?></label>
                                            <input class="form-control" name="codigo" id="codigo" type="text" placeholder="<?php echo __('Ex: 102030'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="ramal"><?php echo __('Ramal'); ?></label>
                                            <input class="form-control" name="ramal" id="ramal" type="text" placeholder="<?php echo __('Ex: 2201'); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 2: Gestão e Unidade -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="gestor"><?php echo __('Gestor Responsável'); ?></label>
                                            <input class="form-control" name="gestor" id="gestor" type="text" placeholder="<?php echo __('Ex: João da Silva'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="emailGestor"><?php echo __('E-mail do Gestor'); ?></label>
                                            <input class="form-control" name="emailGestor" id="emailGestor" type="email" placeholder="<?php echo __('gestor@empresa.com.br'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="unidade"><?php echo __('Unidade'); ?></label>
                                            <select class="form-control" name="unidade" id="unidade" required="">
                                                <?php
                                                $sql_u = "SELECT unidade FROM unidade ORDER BY unidade ASC";
                                                $result_u = $conn->query($sql_u);
                                                if ($result_u && $result_u->num_rows > 0) {
                                                    while ($row_u = $result_u->fetch_assoc()) {
                                                        echo '<option value="' . $row_u['unidade'] . '">' . __($row_u['unidade']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 3: Status -->
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <div class="custom-control custom-switch d-inline-block mt-3">
                                            <input type="hidden" name="status" value="Inativo">
                                            <input type="checkbox" class="custom-control-input" id="statusSwitch" name="status" value="Ativo" checked>
                                            <label class="custom-control-label" for="statusSwitch"><?php echo __('Ativo'); ?></label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 4: Descrição -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="descricao"><?php echo __('Descrição Adicional'); ?></label>
                                            <textarea class="form-control" name="descricao" id="descricao" placeholder="<?php echo __('Detalhes sobre o centro de custo...'); ?>" style="height: 80px;"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-4 offset-md-4">
                                        <button class="btn btn-success btn-block active text-white pulse animated btn-user" type="submit" style="background: rgb(44,64,74);border-radius: 10px;border-width: 0px;height: 50px;"><?php echo __('Cadastrar Centro de Custo'); ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
    <!-- Help Modal -->
    <div class="modal fade glass-help-modal" id="helpModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-file-invoice mr-2"></i><?php echo __('Guia de Centros de Custo'); ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="help-item">
                                <h6><i class="fas fa-barcode mr-2"></i><?php echo __('Código Contábil'); ?></h6>
                                <p class="small text-muted mb-0"><?php echo __('O código é fundamental para a integração com sistemas de ERP e para o faturamento de licenças de software por setor.'); ?></p>
                            </div>
                            <div class="help-item">
                                <h6><i class="fas fa-user-tie mr-2"></i><?php echo __('Gestor Responsável'); ?></h6>
                                <p class="small text-muted mb-0"><?php echo __('O gestor receberá notificações sobre o inventário alocado ao seu setor e alertas de custos excedentes.'); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="help-item">
                                <h6><i class="fas fa-building mr-2"></i><?php echo __('Unidade Física'); ?></h6>
                                <p class="small text-muted mb-0"><?php echo __('Vincule o centro de custo à unidade física correta para facilitar a logística de entrega de novos equipamentos.'); ?></p>
                            </div>
                            <div class="help-item">
                                <h6><i class="fas fa-phone-alt mr-2"></i><?php echo __('Ramal Coletivo'); ?></h6>
                                <p class="small text-muted mb-0"><?php echo __('O ramal do setor ajuda a equipe de suporte a localizar rapidamente um responsável em caso de falhas críticas na área.'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <a href="documentacao.php" class="help-footer-btn text-decoration-none">
                        <i class="fas fa-book mr-2"></i><?php echo __('Ver Documentação Completa'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
