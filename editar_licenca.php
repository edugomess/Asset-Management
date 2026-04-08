<?php
/**
 * EDIÇÃO DE LICENÇA: editar_licenca.php
 * Interface para gestão de ativos de software e compliance.
 */
include_once 'auth.php'; // Proteção de sessão
include_once 'conexao.php'; // Banco de Dados

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: licencas.php");
    exit();
}

$sql = "SELECT * FROM licencas WHERE id_licenca = $id";
$result = $conn->query($sql);
$licensa = $result->fetch_assoc();

if (!$licensa) {
    header("Location: licencas.php");
    exit();
}

// Carrega centros de custo
$sql_cc = "SELECT id_centro_de_custo, nomeSetor FROM centro_de_custo ORDER BY nomeSetor ASC";
$result_cc = $conn->query($sql_cc);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Editar Licença'); ?> - Asset MGT</title>
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
        <div class="d-flex flex-column" id="content-wrapper" style="min-height: 100vh;">
            <div id="content">
                <?php include_once 'topbar.php'; ?>
                <div class="container-fluid">
                    <h3 class="text-dark mb-1"><i class="fas fa-file-contract mr-2 text-primary"></i><?php echo __('Editar Licença'); ?></h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="update_licenca.php" method="post">
                                <input type="hidden" name="id_licenca" value="<?php echo $licensa['id_licenca']; ?>">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="software"><?php echo __('Software / Aplicação'); ?></label>
                                            <input class="form-control" name="software" id="software" type="text" value="<?php echo htmlspecialchars($licensa['software']); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="chave"><?php echo __('Chave de Licença'); ?></label>
                                            <input class="form-control" name="chave" id="chave" type="text" value="<?php echo htmlspecialchars($licensa['chave']); ?>" required="">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="fabricante"><?php echo __('Fabricante'); ?></label>
                                            <input class="form-control" name="fabricante" id="fabricante" type="text" value="<?php echo htmlspecialchars($licensa['fabricante']); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="tipo"><?php echo __('Tipo de Licença'); ?></label>
                                            <select class="form-control" name="tipo" id="tipo" required="">
                                                <option value="Vitalícia" <?php echo ($licensa['tipo'] == 'Vitalícia') ? 'selected' : ''; ?>><?php echo __('Vitalícia'); ?></option>
                                                <option value="Assinatura" <?php echo ($licensa['tipo'] == 'Assinatura') ? 'selected' : ''; ?>><?php echo __('Assinatura (SaaS)'); ?></option>
                                                <option value="OEM" <?php echo ($licensa['tipo'] == 'OEM') ? 'selected' : ''; ?>><?php echo __('OEM'); ?></option>
                                                <option value="Volume" <?php echo ($licensa['tipo'] == 'Volume') ? 'selected' : ''; ?>><?php echo __('Volume (VLK)'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="quantidade_total"><?php echo __('Quantidade de Licenças'); ?></label>
                                            <input class="form-control" name="quantidade_total" id="quantidade_total" type="number" value="<?php echo $licensa['quantidade_total']; ?>" required="">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="valor_unitario"><?php echo __('Valor Unitário (R$)'); ?></label>
                                            <input class="form-control" name="valor_unitario" id="valor_unitario" type="number" step="0.01" value="<?php echo $licensa['valor_unitario']; ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="id_centro_custo"><?php echo __('Centro de Custo'); ?></label>
                                            <select class="form-control" name="id_centro_custo" id="id_centro_custo">
                                                <option value=""><?php echo __('Global / Comum'); ?></option>
                                                <?php
                                                if ($result_cc && $result_cc->num_rows > 0) {
                                                    while ($row_cc = $result_cc->fetch_assoc()) {
                                                        $selected = ($row_cc['id_centro_de_custo'] == $licensa['id_centro_custo']) ? 'selected' : '';
                                                        echo '<option value="' . $row_cc['id_centro_de_custo'] . '" ' . $selected . '>' . __($row_cc['nomeSetor']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="fornecedor"><?php echo __('Fornecedor'); ?></label>
                                            <select class="form-control" name="fornecedor" id="fornecedor">
                                                <option value="Nenhum" <?php echo ($licensa['fornecedor'] == 'Nenhum' || empty($licensa['fornecedor'])) ? 'selected' : ''; ?>><?php echo __('Nenhum'); ?></option>
                                                <?php
                                                $sql_for = "SELECT nomeEmpresa FROM fornecedor ORDER BY nomeEmpresa ASC";
                                                $res_for = $conn->query($sql_for);
                                                if ($res_for && $res_for->num_rows > 0) {
                                                    while ($row_for = $res_for->fetch_assoc()) {
                                                        $selected_for = ($licensa['fornecedor'] == $row_for['nomeEmpresa']) ? 'selected' : '';
                                                        echo '<option value="' . $row_for['nomeEmpresa'] . '" ' . $selected_for . '>' . htmlspecialchars($row_for['nomeEmpresa']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="data_aquisicao"><?php echo __('Data de Aquisição'); ?></label>
                                            <input class="form-control" name="data_aquisicao" id="data_aquisicao" type="date" value="<?php echo $licensa['data_aquisicao']; ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="data_expiracao"><?php echo __('Data de Expiração'); ?></label>
                                            <input class="form-control" name="data_expiracao" id="data_expiracao" type="date" value="<?php echo $licensa['data_expiracao']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="status"><?php echo __('Status'); ?></label>
                                            <select class="form-control" name="status" id="status">
                                                <option value="Ativa" <?php echo ($licensa['status'] == 'Ativa') ? 'selected' : ''; ?>><?php echo __('Ativa'); ?></option>
                                                <option value="Expirada" <?php echo ($licensa['status'] == 'Expirada') ? 'selected' : ''; ?>><?php echo __('Expirada'); ?></option>
                                                <option value="Cancelada" <?php echo ($licensa['status'] == 'Cancelada') ? 'selected' : ''; ?>><?php echo __('Cancelada'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4 mb-3">
                                    <div class="col-12 d-flex justify-content-end align-items-center" style="gap: 15px;">
                                        <a class="btn btn-secondary" href="licencas.php" 
                                            style="border-radius: 10px; padding: 10px 30px; border: none; background: #858796; font-weight: 600;">
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