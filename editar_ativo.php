<?php
/**
 * EDIÇÃO DE ATIVO: editar_ativo.php
 * Interface para alteração de dados técnicos e administrativos de equipamentos existentes.
 */
// Inclui arquivos de segurança e conexão
include 'auth.php';
include_once 'conexao.php';

// Captura e valida o ID do ativo vindo da URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Se o ID for inválido, redireciona ou trata o erro
if ($id <= 0) {
    header("Location: equipamentos.php");
    exit;
}
?>

<!DOCTYPE html>
<html style="margin: 0px, 0px, 0px;margin-bottom: 0px;margin-top: 0px;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Editar Ativo'); ?></title>
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
                <?php include 'topbar.php'; ?>
                <div class="container-fluid">
                    <h3 class="text-dark mb-4"><?php echo __('Editar Ativo'); ?></h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="update_ativo.php" method="post" enctype="multipart/form-data">
                                <?php
                                $sql = "SELECT * FROM ativos WHERE id_asset = '$id'";
                                $result = mysqli_query($conn, $sql);
                                if ($array = mysqli_fetch_array($result)) {
                                    echo "<input type='hidden' name='id_asset' value='" . $array['id_asset'] . "'>";
                                    $categoria = $array['categoria'];
                                    $fabricante = $array['fabricante'];
                                    $modelo = $array['modelo'];
                                    $tag = $array['tag'];
                                    $hostName = $array['hostName'];
                                    $valor = $array['valor'];
                                    $macAdress = $array['macAdress'];
                                    $status = $array['status'];
                                    $dataAtivacao = $array['dataAtivacao'];
                                    $centroDeCusto = $array['centroDeCusto'];
                                    $descricao = $array['descricao'];
                                }
                                ?>

                                <div class="form-row">
                                    <div class="col-sm-12 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Categoria'); ?></label>
                                            <select class="form-control" name="categoria" required="">
                                                <option value="<?php echo $categoria ?>"><?php echo $categoria ?></option>
                                                <?php
                                                $sql_cat = "SELECT categoria FROM categoria";
                                                $res_cat = $conn->query($sql_cat);
                                                if ($res_cat->num_rows > 0) {
                                                    while ($row_c = $res_cat->fetch_assoc()) {
                                                        if ($row_c['categoria'] != $categoria) {
                                                            echo '<option value="' . $row_c['categoria'] . '">' . $row_c['categoria'] . '</option>';
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Fabricante'); ?></label>
                                            <input class="form-control" name="fabricante" type="text"
                                                placeholder="<?php echo __('Fabricante'); ?>" value="<?php echo $fabricante ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Modelo'); ?></label>
                                                value="<?php echo $modelo ?>" placeholder="<?php echo __('Modelo'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Tag (Patrimônio)'); ?></label>
                                            <input class="form-control" name="tag" type="text" placeholder="Tag"
                                                value="<?php echo $tag ?>" disabled title="<?php echo __('A Tag/Patrimônio não pode ser alterada.'); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Host Name'); ?></label>
                                            <input class="form-control" name="hostName" type="text"
                                                placeholder="<?php echo __('Host Name'); ?>" value="<?php echo $hostName ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Valor R$'); ?></label>
                                            <input class="form-control" name="valor" type="number" step="0.01"
                                                placeholder="0.00" value="<?php echo $valor ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('MAC Adress'); ?></label>
                                            <div class="input-group">
                                                <input class="form-control" name="macAdress" type="text"
                                                    placeholder="<?php echo __('MAC Adress'); ?>" value="<?php echo $macAdress ?>">
                                                <div class="input-group-append bg-white border-0 ml-2 d-flex align-items-center">
                                                    <div class="custom-control custom-switch">
                                                        <input type="hidden" name="status" value="Inativo">
                                                        <input type="checkbox" class="custom-control-input" id="statusSwitch"
                                                            name="status" value="Ativo" <?php echo ($status == 'Ativo') ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" for="statusSwitch"><?php echo __('Ativo'); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Data de Cadastro'); ?></label>
                                            <input class="form-control" name="dataAtivacao" type="date"
                                                value="<?php echo $dataAtivacao ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="col-sm-12 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Centro de Custo'); ?></label>
                                            <select class="form-control" name="centroDeCusto">
                                                <option value="" <?php echo ($centroDeCusto == '' || $centroDeCusto == 'Nenhum') ? 'selected' : ''; ?>><?php echo __('Nenhum'); ?></option>
                                                <?php
                                                $sql_cc = "SELECT nomeSetor FROM centro_de_custo ORDER BY nomeSetor ASC";
                                                $res_cc = $conn->query($sql_cc);
                                                if ($res_cc && $res_cc->num_rows > 0) {
                                                    while ($row_cc = $res_cc->fetch_assoc()) {
                                                        $selected_cc = ($row_cc['nomeSetor'] == $centroDeCusto) ? 'selected' : '';
                                                        echo '<option value="' . $row_cc['nomeSetor'] . '" ' . $selected_cc . '>' . $row_cc['nomeSetor'] . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="col-xl-9 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Upload de Imagem'); ?></label>
                                            <input class="form-control-file" name="imagem" type="file" accept="image/*">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="col-xl-9 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Descrição / Observações'); ?></label>
                                            <textarea class="form-control" name="descricao" placeholder="<?php echo __('Descrição...'); ?>"
                                                style="height: 100px;"><?php echo $descricao; ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row mt-4 mb-4">
                                    <div class="col-12 d-flex justify-content-end align-items-center" style="gap: 15px;">
                                        <a class="btn btn-secondary btn-user" href="equipamentos.php" 
                                            style="border-radius: 10px; padding: 10px 30px; border: none; background: #858796; font-weight: 600;">
                                            <?php echo __('Voltar'); ?>
                                        </a>
                                        <button class="btn btn-primary btn-user" type="submit" 
                                            style="background: #2c404a; border-radius: 10px; padding: 10px 30px; border: none; font-weight: 600;">
                                            <?php echo __('Salvar Alterações'); ?>
                                        </button>
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