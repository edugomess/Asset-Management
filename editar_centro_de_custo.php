<?php
/**
 * EDIÇÃO DE CENTRO DE CUSTO: editar_centro_de_custo.php
 * Interface para alteração de dados de centros de custo existentes.
 */
include_once 'auth.php'; // Proteção de sessão
include_once 'conexao.php'; // Banco de Dados

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: centro_de_custo.php");
    exit();
}

$sql = "SELECT * FROM centro_de_custo WHERE id_centro_de_custo = $id";
$result = $conn->query($sql);
$cc = $result->fetch_assoc();

if (!$cc) {
    header("Location: centro_de_custo.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'pt-BR') ? 'pt-br' : 'en'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Editar Centro de Custo'); ?> - Asset MGT</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=3265483e434712d72c41db9eebc4c8bb">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=d6a29779d310462e7fcdde7b9a80e0db">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=5f41e73f827c7b56616237a1da13b6e2">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=19488c1c6619bc9bd5c02de5f7ffbfd4">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=193916adb9d7af47fe74d9a2270caac3">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <?php include_once 'sidebar_style.php'; ?>
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
                    <h3 class="text-dark mb-1"><?php echo __('Editar Centro de Custo'); ?></h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="update_centro_de_custo.php" method="post">
                                <input type="hidden" name="id_centro_de_custo" value="<?php echo $cc['id_centro_de_custo']; ?>">

                                <!-- Row 1: Identificação do Setor -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="nomeSetor"><?php echo __('Nome do Setor'); ?></label>
                                            <input class="form-control" name="nomeSetor" id="nomeSetor" type="text" value="<?php echo htmlspecialchars($cc['nomeSetor']); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="codigo"><?php echo __('Código'); ?></label>
                                            <input class="form-control" name="codigo" id="codigo" type="text" value="<?php echo htmlspecialchars($cc['codigo']); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="ramal"><?php echo __('Ramal'); ?></label>
                                            <input class="form-control" name="ramal" id="ramal" type="text" value="<?php echo htmlspecialchars($cc['ramal']); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 2: Gestão e Unidade -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="gestor"><?php echo __('Gestor Responsável'); ?></label>
                                            <input class="form-control" name="gestor" id="gestor" type="text" value="<?php echo htmlspecialchars($cc['gestor']); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="emailGestor"><?php echo __('E-mail do Gestor'); ?></label>
                                            <input class="form-control" name="emailGestor" id="emailGestor" type="email" value="<?php echo htmlspecialchars($cc['emailGestor']); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="unidade"><?php echo __('Unidade'); ?></label>
                                            <select class="form-control" name="unidade" id="unidade" required="">
                                                <?php
                                                $sql_un = "SELECT unidade FROM unidade ORDER BY unidade ASC";
                                                $res_un = $conn->query($sql_un);
                                                if ($res_un && $res_un->num_rows > 0) {
                                                    while ($row_u = $res_un->fetch_assoc()) {
                                                        $selected = ($row_u['unidade'] == $cc['unidade']) ? 'selected' : '';
                                                        echo '<option value="' . $row_u['unidade'] . '" ' . $selected . '>' . __($row_u['unidade']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 3: Status -->
                                <div class="row align-items-center">
                                    <div class="col-md-12 text-center">
                                        <div class="custom-control custom-switch d-inline-block mt-3">
                                            <input type="hidden" name="status" value="Inativo">
                                            <input type="checkbox" class="custom-control-input" id="statusSwitch" name="status" value="Ativo" <?php echo ($cc['status'] == 'Ativo') ? 'checked' : ''; ?>>
                                            <label class="custom-control-label font-weight-bold" for="statusSwitch"><?php echo __('Ativo'); ?></label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 4: Descrição -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="descricao"><?php echo __('Descrição Adicional'); ?></label>
                                            <textarea class="form-control" name="descricao" id="descricao" style="height: 80px;"><?php echo htmlspecialchars($cc['descricao']); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 5: Action Buttons -->
                                <div class="row mt-4 mb-3">
                                    <div class="col-12 d-flex justify-content-end align-items-center" style="gap: 15px;">
                                        <a class="btn btn-secondary" href="centro_de_custo.php" 
                                            style="border-radius: 10px; padding: 10px 30px; border: none; background: #2c404a; font-weight: 600;">
                                            <?php echo __('Voltar'); ?>
                                        </a>
                                        <button class="btn btn-success active pulse animated" type="submit" 
                                            style="background: #2c404a; border-radius: 10px; padding: 10px 30px; border: none; font-weight: 600;">
                                            <i class="fas fa-save mr-2"></i><?php echo __('Salvar Alterações'); ?>
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
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
</body>

</html>