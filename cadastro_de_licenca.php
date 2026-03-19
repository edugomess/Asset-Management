<?php
/**
 * CADASTRO DE LICENÇAS: cadastro_de_licenca.php
 * Interface para gestão de ativos de software e compliance.
 * Controla fabricante, tipo de licença ( vitalícia/assinatura), seats e expiração.
 */
include 'auth.php';    // Segurança
include 'conexao.php'; // Dados

// AUXILIAR: Carrega centros de custo para faturamento da licença
$sql_cc = "SELECT id_centro_de_custo, nomeSetor FROM centro_de_custo ORDER BY nomeSetor ASC";
$result_cc = mysqli_query($conn, $sql_cc);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Cadastro de Licença'); ?></title>
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
                <?php include 'sidebar_brand.php'; ?>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper" style="min-height: 100vh;">
            <div id="content" style="flex: 1 0 auto;">
                <?php include 'topbar.php'; ?>
                <div class="container-fluid">
                    <h3 class="text-dark mb-1"><?php echo __('Cadastro de Licença'); ?></h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="inserir_licenca.php" method="post">
                                <div class="form-row">
                                    <div class="col-sm-3 col-xl-4 offset-xl-1">
                                        <div class="form-group"><label class="text-gray-600 small font-weight-bold"><?php echo __('Software / Aplicação'); ?></label>
                                            <input class="form-control" name="software" type="text"
                                                placeholder="<?php echo __('Ex: Microsoft Office 365'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Fabricante'); ?></label>
                                            <input class="form-control" name="fabricante" type="text"
                                                placeholder="<?php echo __('Ex: Microsoft'); ?>" required="">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-xl-2">
                                        <div class="form-group"><label class="text-gray-600 small font-weight-bold"><?php echo __('Tipo de Licença'); ?></label>
                                            <select class="form-control" name="tipo_licenca" required>
                                                <option value="Assinatura"><?php echo __('Assinatura'); ?></option>
                                                <option value="Vitalícia"><?php echo __('Vitalícia'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xl-4 offset-xl-1">
                                        <div class="form-group"><label class="text-gray-600 small font-weight-bold"><?php echo __('Chave de Licença'); ?></label>
                                            <input class="form-control" name="chave_licenca" type="text"
                                                placeholder="<?php echo __('XXXXX-XXXXX-XXXXX-XXXXX'); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-sm-3 col-xl-2 offset-xl-1">
                                        <div class="form-group"><label class="text-gray-600 small font-weight-bold"><?php echo __('Quantidade Seats'); ?></label>
                                            <input class="form-control" name="quantidade" type="number" min="1" value="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-xl-2">
                                        <div class="form-group"><label class="text-gray-600 small font-weight-bold"><?php echo __('Valor Unitário'); ?></label>
                                            <input class="form-control" name="valor_unitario" type="text" id="valor_unitario" placeholder="<?php echo __('0.00'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 offset-xl-1">
                                        <div class="form-group"><label class="text-gray-600 small font-weight-bold"><?php echo __('Centro de Custo'); ?></label>
                                            <select class="form-control" name="id_centro_custo">
                                                <option value=""><?php echo __('Nenhum'); ?></option>
                                            <?php
                                            // Reset the pointer if needed, but it should be fresh
                                            mysqli_data_seek($result_cc, 0);
                                            while ($row_cc = mysqli_fetch_assoc($result_cc)) {
                                                echo "<option value='" . $row_cc['id_centro_de_custo'] . "'>" . __(htmlspecialchars($row_cc['nomeSetor'])) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-xl-3 offset-xl-1">
                                        <div class="form-group"><label class="text-gray-600 small font-weight-bold"><?php echo __('Data de Aquisição'); ?></label>
                                            <input class="form-control" name="data_aquisicao" type="date">
                                        </div>
                                    </div>
                                    <div class="col-xl-3">
                                        <div class="form-group"><label class="text-gray-600 small font-weight-bold"><?php echo __('Data de Expiração'); ?></label>
                                            <input class="form-control" name="data_expiracao" type="date">
                                        </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-xl-4 offset-xl-4">
                                        <button class="btn btn-success btn-block active text-white pulse animated btn-user" type="submit"
                                            style="background: rgb(44,64,74);border-radius: 10px;padding: 30px, 30px;border-width: 0px;height: 50px;margin-top: 30px;"><?php echo __('Cadastrar Licença'); ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
            </div>

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