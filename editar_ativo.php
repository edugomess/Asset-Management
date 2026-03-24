<?php
/**
 * EDIÇÃO DE ATIVO: editar_ativo.php
 * Interface para alteração de dados técnicos e administrativos de equipamentos existentes.
 */
include_once 'auth.php'; // Proteção de sessão
include_once 'conexao.php'; // Banco de Dados

// Verificação de permissão: Apenas Admin e Suporte podem editar ativos
if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    header("Location: index.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: equipamentos.php");
    exit();
}

$sql = "SELECT * FROM ativos WHERE id_asset = $id";
$result = $conn->query($sql);
$asset = $result->fetch_assoc();

if (!$asset) {
    header("Location: equipamentos.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'pt-BR') ? 'pt-br' : 'en'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Editar Ativo'); ?> - Asset MGT</title>
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
                    <h3 class="text-dark mb-1"><?php echo __('Editar Ativo'); ?></h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="update_ativo.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="id_asset" value="<?php echo $asset['id_asset']; ?>">

                                <!-- Row 1: Identificação Básica -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="categoria"><?php echo __('Categoria'); ?></label>
                                            <select class="form-control" name="categoria" id="categoria" required="">
                                                <?php
                                                $sql_cat = "SELECT categoria FROM categoria ORDER BY categoria ASC";
                                                $res_cat = $conn->query($sql_cat);
                                                if ($res_cat && $res_cat->num_rows > 0) {
                                                    while ($row_c = $res_cat->fetch_assoc()) {
                                                        $selected = ($asset['categoria'] == $row_c['categoria']) ? 'selected' : '';
                                                        echo '<option value="' . $row_c['categoria'] . '" ' . $selected . '>' . __($row_c['categoria']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="fabricante"><?php echo __('Fabricante'); ?></label>
                                            <input class="form-control" name="fabricante" id="fabricante" type="text" value="<?php echo htmlspecialchars($asset['fabricante']); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="modelo"><?php echo __('Modelo'); ?></label>
                                            <input class="form-control" name="modelo" id="modelo" type="text" value="<?php echo htmlspecialchars($asset['modelo']); ?>" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 2: Rastreabilidade -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="tag"><?php echo __('Tag / Service Tag'); ?></label>
                                            <input class="form-control" name="tag" id="tag" type="text" value="<?php echo htmlspecialchars($asset['tag']); ?>" readonly title="<?php echo __('A Tag/Patrimônio não pode ser alterada.'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="hostnameContainer">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="hostName"><?php echo __('Host Name'); ?></label>
                                            <input class="form-control" name="hostName" id="hostName" type="text" value="<?php echo htmlspecialchars($asset['hostName']); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="macAdress"><?php echo __('Endereço MAC'); ?></label>
                                            <input class="form-control" name="macAdress" id="macAdress" type="text" value="<?php echo htmlspecialchars($asset['macAdress']); ?>" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 3: Financeiro e Organização -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="valor"><?php echo __('Valor do Ativo (R$)'); ?></label>
                                            <input class="form-control" name="valor" id="valor" type="number" step="0.01" value="<?php echo $asset['valor']; ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="centroDeCusto"><?php echo __('Centro de Custo'); ?></label>
                                            <select class="form-control" name="centroDeCusto" id="centroDeCusto">
                                                <option value="Nenhum" <?php echo ($asset['centroDeCusto'] == 'Nenhum' || empty($asset['centroDeCusto'])) ? 'selected' : ''; ?>><?php echo __('Nenhum'); ?></option>
                                                <?php
                                                $sql_cc = "SELECT nomeSetor FROM centro_de_custo ORDER BY nomeSetor ASC";
                                                $res_cc = $conn->query($sql_cc);
                                                if ($res_cc && $res_cc->num_rows > 0) {
                                                    while ($row_cc = $res_cc->fetch_assoc()) {
                                                        $selected_cc = ($asset['centroDeCusto'] == $row_cc['nomeSetor']) ? 'selected' : '';
                                                        echo '<option value="' . $row_cc['nomeSetor'] . '" ' . $selected_cc . '>' . __($row_cc['nomeSetor']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="setor"><?php echo __('Setor Destinado'); ?></label>
                                            <input class="form-control" name="setor" id="setor" type="text" value="<?php echo htmlspecialchars($asset['setor'] ?? ''); ?>" placeholder="<?php echo __('Ex: Marketing, RH, TI'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="fornecedor"><?php echo __('Fornecedor'); ?></label>
                                            <select class="form-control" name="fornecedor" id="fornecedor">
                                                <option value="Nenhum" <?php echo ($asset['fornecedor'] == 'Nenhum' || empty($asset['fornecedor'])) ? 'selected' : ''; ?>><?php echo __('Nenhum'); ?></option>
                                                <?php
                                                $sql_for = "SELECT nomeEmpresa FROM fornecedor ORDER BY nomeEmpresa ASC";
                                                $res_for = $conn->query($sql_for);
                                                if ($res_for && $res_for->num_rows > 0) {
                                                    while ($row_for = $res_for->fetch_assoc()) {
                                                        $selected_for = ($asset['fornecedor'] == $row_for['nomeEmpresa']) ? 'selected' : '';
                                                        echo '<option value="' . $row_for['nomeEmpresa'] . '" ' . $selected_for . '>' . htmlspecialchars($row_for['nomeEmpresa']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 4: Dados Financeiros / Nota Fiscal (New) -->
                                <h5 class="text-primary font-weight-bold mt-4 mb-3"><?php echo __('Dados de Compra / Nota Fiscal'); ?></h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="numero_nota_fiscal"><?php echo __('Nota Fiscal/Chave de Acesso'); ?></label>
                                            <input class="form-control" name="numero_nota_fiscal" id="numero_nota_fiscal" type="text" value="<?php echo htmlspecialchars($asset['numero_nota_fiscal']); ?>" placeholder="Ex: 35260312345678000190550010000458921876543210" required pattern="\d{44}" title="A chave de acesso deve ter exatamente 44 dígitos numéricos">
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="anexo_nota_fiscal">
                                                <?php echo __('Anexo da Nota Fiscal'); ?>
                                                <?php if (!empty($asset['anexo_nota_fiscal'])): ?>
                                                    <a href="<?php echo htmlspecialchars($asset['anexo_nota_fiscal']); ?>" target="_blank" class="badge badge-info ml-2">
                                                        <i class="fas fa-file-pdf mr-1"></i><?php echo __('Ver Atual'); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </label>
                                            <input class="form-control-file" name="anexo_nota_fiscal" id="anexo_nota_fiscal" type="file" accept=".pdf,image/*">
                                        </div>
                                    </div>
                                </div>

                                <!-- Hardware Details (Conditional) -->
                                <div id="hardwareSection" style="display: none; border-left: 4px solid #2c404a; padding-left: 15px; margin-bottom: 25px; background: #f8f9fc; padding-top: 10px; padding-bottom: 5px; border-radius: 5px;">
                                    <h5 class="text-primary font-weight-bold mb-3"><?php echo __('Especificações de Hardware'); ?></h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="text-gray-600 small font-weight-bold" for="memoria"><?php echo __('Memória RAM'); ?></label>
                                                <input class="form-control" name="memoria" id="memoria" type="text" value="<?php echo htmlspecialchars($asset['memoria']); ?>" placeholder="Ex: 16GB DDR4">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="text-gray-600 small font-weight-bold" for="processador"><?php echo __('Processador'); ?></label>
                                                <input class="form-control" name="processador" id="processador" type="text" value="<?php echo htmlspecialchars($asset['processador']); ?>" placeholder="Ex: Intel i7-1185G7">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="text-gray-600 small font-weight-bold" for="armazenamento"><?php echo __('Capacidade Armazenamento'); ?></label>
                                                <input class="form-control" name="armazenamento" id="armazenamento" type="text" value="<?php echo htmlspecialchars($asset['armazenamento']); ?>" placeholder="Ex: 512GB">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="text-gray-600 small font-weight-bold" for="tipo_armazenamento"><?php echo __('Tipo de Armazenamento'); ?></label>
                                                <select class="form-control" name="tipo_armazenamento" id="tipo_armazenamento">
                                                    <option value="SSD" <?php echo ($asset['tipo_armazenamento'] == 'SSD') ? 'selected' : ''; ?>>SSD</option>
                                                    <option value="HD" <?php echo ($asset['tipo_armazenamento'] == 'HD') ? 'selected' : ''; ?>>HD</option>
                                                    <option value="NVMe" <?php echo ($asset['tipo_armazenamento'] == 'NVMe') ? 'selected' : ''; ?>>NVMe</option>
                                                    <option value="Híbrido" <?php echo ($asset['tipo_armazenamento'] == 'Híbrido') ? 'selected' : ''; ?>>Híbrido</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 5: Visual, Datas e Status -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="dataAtivacao"><?php echo __('Data de Cadastro'); ?></label>
                                            <input class="form-control" name="dataAtivacao" id="dataAtivacao" type="date" value="<?php echo $asset['dataAtivacao']; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="imagem">
                                                <?php echo __('Alterar Imagem do Ativo'); ?>
                                                <img src="<?php echo !empty($asset['imagem']) ? htmlspecialchars($asset['imagem']) : '/assets/img/avatars/avatar1.jpeg'; ?>"
                                                     class="img-thumbnail ml-2" style="width: 30px; height: 30px; object-fit: cover; border-radius: 5px;">
                                            </label>
                                            <input class="form-control-file" name="imagem" id="imagem" type="file" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="custom-control custom-switch" style="margin-top: 32px;">
                                            <input type="hidden" name="status" value="Inativo">
                                            <input type="checkbox" class="custom-control-input" id="statusSwitch" name="status" value="Ativo" <?php echo ($asset['status'] == 'Ativo') ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="statusSwitch"><?php echo __('Ativo'); ?></label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 5: Descrição -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="descricao"><?php echo __('Observações Adicionais'); ?></label>
                                            <textarea class="form-control" name="descricao" id="descricao" style="height: 80px;"><?php echo htmlspecialchars($asset['descricao']); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 6: Action Buttons -->
                                <div class="row mt-4 mb-3">
                                    <div class="col-12 d-flex justify-content-end align-items-center" style="gap: 15px;">
                                        <a class="btn btn-secondary" href="equipamentos.php" 
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

        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
    <script>
        $(document).ready(function() {
            function toggleFields() {
                var cat = $('#categoria').val();
                
                // Hardware Section (CPU, RAM, Disk)
                if (cat === 'Notebook' || cat === 'Desktop' || cat === 'Servidores') {
                    $('#hardwareSection').slideDown();
                } else {
                    $('#hardwareSection').slideUp();
                }

                // Host Name visibility
                if (cat === 'Monitor' || cat === 'Periféricos') {
                    $('#hostnameContainer').slideUp();
                    $('#hostName').removeAttr('required');
                } else {
                    $('#hostnameContainer').slideDown();
                    $('#hostName').attr('required', 'required');
                }
            }
            $('#categoria').change(toggleFields);
            toggleFields(); // Initial check
        });
    </script>
</body>

</html>