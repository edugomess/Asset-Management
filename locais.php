<?php include 'performance_header.php'; ?>
<?php
/**
 * GESTÃO DE LOCAIS: locais.php
 * Gerenciamento da hierarquia de localizações (Prédio, Andar, Sala, Rack).
 */
include_once 'auth.php';
include_once 'conexao.php';

// Restrição de acesso
if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    header("Location: index.php");
    exit();
}

$msg = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'success') {
        $msg = '<div class="alert alert-success">Local cadastrado com sucesso!</div>';
    } elseif ($_GET['msg'] == 'deleted') {
        $msg = '<div class="alert alert-warning">Local excluído com sucesso!</div>';
    } elseif ($_GET['msg'] == 'error') {
        $msg = '<div class="alert alert-danger">Erro ao processar operação.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language'] ?? 'pt-br'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Gerenciar Locais'); ?> - Asset Mgt</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=f3d9abe8d5aa7831c01bfaa2a1563712">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=41e93b37bc495fd67938799bb3a6adaf">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <?php renderPerformanceHints(); ?>
    <?php include_once 'sidebar_style.php'; ?>
    <style>
        .tree-level-1 { font-weight: bold; color: #2c404a; }
        
        .tree-branch { color: #a0aec0; margin-right: 5px; opacity: 0.7; }
        
        .btn-system {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s;
            background: #2c404a;
            border: none;
            color: white;
            padding: 10px 20px;
        }

        .btn-system:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background: #3e5b69;
            color: white;
        }
    </style>
    <style>
        .clickable-row {
            cursor: pointer;
            transition: all 0.2s;
        }

        .clickable-row:hover {
            background-color: rgba(0, 0, 0, 0.05) !important;
        }
    </style>
</head>

<body id="page-top">
    <?php startNProgress(); ?>
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include_once 'sidebar_brand.php'; ?>
                <?php include_once 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column premium-page-fade" id="content-wrapper">
            <div id="content">
                <?php include_once 'topbar.php'; ?>
                <div class="container-fluid" style="padding-left: 23px; padding-right: 23px;">
                    <!-- Seção de Resumo: Cards Informativos -->
                    <div class="row">
                        <?php
                        $res_total = $conn->query("SELECT COUNT(*) as total FROM locais");
                        $total_locais = $res_total->fetch_assoc()['total'];

                        $res_predios = $conn->query("SELECT COUNT(*) as total FROM locais WHERE tipo_local = 'Prédio'");
                        $total_predios = $res_predios->fetch_assoc()['total'];

                        $res_racks = $conn->query("SELECT COUNT(*) as total FROM locais WHERE tipo_local = 'Rack'");
                        $total_racks = $res_racks->fetch_assoc()['total'];

                        $res_salas = $conn->query("SELECT COUNT(*) as total FROM locais WHERE tipo_local = 'Sala'");
                        $total_salas = $res_salas->fetch_assoc()['total'];
                        ?>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-left-primary py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-primary font-weight-bold text-xs mb-1">
                                                <span><?php echo __('Total de Locais'); ?></span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0"><span><?php echo $total_locais; ?></span></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-map-marked-alt fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-left-success py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-success font-weight-bold text-xs mb-1">
                                                <span><?php echo __('Prédios'); ?></span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0"><span><?php echo $total_predios; ?></span></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-building fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-left-info py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-info font-weight-bold text-xs mb-1">
                                                <span><?php echo __('Racks'); ?></span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0"><span><?php echo $total_racks; ?></span></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-server fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-left-warning py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-warning font-weight-bold text-xs mb-1">
                                                <span><?php echo __('Salas'); ?></span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0"><span><?php echo $total_salas; ?></span></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-door-open fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-dark mb-4"><?php echo __('Gerenciar Locais e Infraestrutura'); ?></h3>
                    <?php echo $msg; ?>
                    
                    <div class="row">
                        <!-- Formulário de Cadastro -->
                        <div class="col-md-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary font-weight-bold m-0"><?php echo __('Novo Local'); ?></h6>
                                </div>
                                <div class="card-body">
                                    <form action="inserir_local.php" method="POST">
                                        <div class="form-group">
                                            <label for="nome_local"><strong><?php echo __('Nome do Local'); ?></strong></label>
                                            <input class="form-control premium-filter" type="text" id="nome_local" name="nome_local" placeholder="Ex: Prédio Central, Sala 02..." required>
                                        </div>
                                        <div class="form-group">
                                            <label for="tipo_local"><strong><?php echo __('Tipo'); ?></strong></label>
                                            <select class="form-control premium-filter" id="tipo_local" name="tipo_local" required>
                                                <option value="Prédio"><?php echo __('Prédio'); ?></option>
                                                <option value="Andar"><?php echo __('Andar'); ?></option>
                                                <option value="Sala"><?php echo __('Sala'); ?></option>
                                                <option value="Rack"><?php echo __('Rack'); ?></option>
                                                <option value="Setor"><?php echo __('Setor'); ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="id_parent_local"><strong><?php echo __('Local Pai (Opcional)'); ?></strong></label>
                                            <select class="form-control premium-filter" id="id_parent_local" name="id_parent_local">
                                                <option value=""><?php echo __('Nenhum (Raiz)'); ?></option>
                                                <?php
                                                $sql_parents = "SELECT id_local, nome_local, tipo_local FROM locais ORDER BY nome_local ASC";
                                                $res_parents = $conn->query($sql_parents);
                                                while($p = $res_parents->fetch_assoc()) {
                                                    echo "<option value='{$p['id_local']}'>{$p['nome_local']} ({$p['tipo_local']})</option>";
                                                }
                                                ?>
                                            </select>
                                            <small class="text-muted"><?php echo __('Use para criar hierarquia (ex: Sala dentro de um Andar).'); ?></small>
                                        </div>
                                        <button class="btn btn-system btn-block" type="submit"><?php echo __('Cadastrar'); ?></button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Listagem Hierárquica -->
                        <div class="col-md-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary font-weight-bold m-0"><?php echo __('Estrutura de Localização'); ?></h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover my-0">
                                            <thead>
                                                <tr>
                                                    <th><?php echo __('Nível / Nome'); ?></th>
                                                    <th><?php echo __('Tipo'); ?></th>
                                                    <th class="text-right"><?php echo __('Ações'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                function renderLocais($conn, $parentId = null, $level = 1) {
                                                    $sql = $parentId === null 
                                                        ? "SELECT * FROM locais WHERE id_parent_local IS NULL ORDER BY nome_local ASC" 
                                                        : "SELECT * FROM locais WHERE id_parent_local = $parentId ORDER BY nome_local ASC";
                                                    
                                                    $result = $conn->query($sql);
                                                    if ($result->num_rows > 0) {
                                                        while ($row = $result->fetch_assoc()) {
                                                            $icon = 'fa-map-marker-alt';
                                                            $badgeClass = 'badge-info';
                                                            if ($row['tipo_local'] == 'Prédio') { $icon = 'fa-building'; $badgeClass = 'badge-primary'; }
                                                            if ($row['tipo_local'] == 'Rack') { $icon = 'fa-server'; $badgeClass = 'badge-dark'; }
                                                            if ($row['tipo_local'] == 'Sala') { $icon = 'fa-door-open'; $badgeClass = 'badge-info'; }
                                                            
                                                            $indentHtml = '';
                                                            $branchIcon = '';
                                                            if ($level > 1) {
                                                                $indentHtml = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $level - 1);
                                                                $branchIcon = "<i class='fas fa-level-up-alt fa-rotate-90 tree-branch'></i>";
                                                            }
                                                            
                                                            echo "<tr class='clickable-row' data-href='editar_local.php?id={$row['id_local']}'>
                                                                <td class='tree-level-$level'>
                                                                    $indentHtml $branchIcon
                                                                    <i class='fas $icon mr-2' style='color: #4e73df;'></i> " . htmlspecialchars($row['nome_local']) . "
                                                                </td>
                                                                <td><span class='dot-badge $badgeClass'>" . __($row['tipo_local']) . "</span></td>
                                                                <td class='text-right'>
                                                                    <a href='editar_local.php?id={$row['id_local']}' class='btn btn-primary btn-sm mr-1' style='border-radius: 8px; background: #2c404a; border: none;' title='" . __('Editar') . "'><i class='fas fa-pencil-alt'></i></a>
                                                                    <button type='button' class='btn btn-danger btn-sm btn-delete-local' data-id='{$row['id_local']}' data-name='" . htmlspecialchars($row['nome_local']) . "' style='border-radius: 8px;' title='" . __('Excluir') . "'><i class='fas fa-trash'></i></button>
                                                                </td>
                                                            </tr>";
                                                            renderLocais($conn, $row['id_local'], $level + 1);
                                                        }
                                                    } elseif ($parentId === null && $level == 1) {
                                                        echo "<tr><td colspan='3' class='text-center py-4 text-muted'><i class='fas fa-info-circle mr-1'></i> " . __('Nenhum local cadastrado.') . "</td></tr>";
                                                    }
                                                }
                                                renderLocais($conn);
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <footer class="bg-white sticky-footer">
                <div class="container my-auto">
                    <div class="text-center my-auto copyright"><span>Copyright © Asset Mgt 2024</span></div>
                </div>
            </footer>
        </div>

    <!-- Modal de Confirmação de Exclusão com AJAX -->
    <div class="modal fade" id="modalDeleteLocal" tabindex="-1" role="dialog" aria-labelledby="modalDeleteLocalLabel" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; pointer-events: auto !important;">
                <div class="modal-header border-0 p-4" style="background: #2c404a; color: white;">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 mr-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-trash-alt text-danger"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-weight-bold mb-0" id="modalDeleteLocalLabel"><?php echo __('Confirmar Exclusão'); ?></h5>
                            <small class="text-white-50"><?php echo __('Verifique as dependências antes de confirmar a remoção.'); ?></small>
                        </div>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div id="deleteLoading" class="text-center py-3">
                        <div class="spinner-border text-primary" role="status"><span class="sr-only">Carregando...</span></div>
                    </div>
                    <div id="deleteContent" style="display: none;">
                        <div class="text-center mb-4" id="deleteIconArea">
                            <i class="fas fa-exclamation-circle fa-4x text-warning mb-3"></i>
                            <h4 class="text-dark font-weight-bold"><?php echo __('Tem certeza?'); ?></h4>
                            <p class="text-muted" id="deleteTargetName"></p>
                        </div>

                        <div id="dependenciesArea" style="display: none;">
                            <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">
                                <i class="fas fa-times-circle mr-2"></i>
                                <strong><?php echo __('Exclusão Bloqueada!'); ?></strong><br>
                                <?php echo __('Existem itens vinculados a este local que impedem a exclusão.'); ?>
                            </div>
                            <div id="dependencyList" class="mt-3" style="max-height: 200px; overflow-y: auto; background: #fff; border-radius: 12px; padding: 15px; border: 1px solid #e3e6f0;">
                                <!-- Populado via JS -->
                            </div>
                            <p class="text-muted small mt-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                <?php echo __('Mova todos os itens acima para outro local antes de tentar excluir.'); ?>
                            </p>
                        </div>

                        <p id="noDependenciesMsg" class="text-muted text-center"><?php echo __('Este local será removido permanentemente.'); ?></p>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-white d-flex justify-content-between">
                    <button type="button" class="btn btn-link text-muted font-weight-bold" data-dismiss="modal">
                        <?php echo __('Cancelar'); ?>
                    </button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger px-4 font-weight-bold shadow-sm" style="border-radius: 12px; display: none;">
                        <?php echo __('Excluir Local'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/theme.js"></script>
    <script>
        $(document).ready(function() {
            $('.btn-delete-local').on('click', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                
                $('#modalDeleteLocal').modal('show');
                $('#deleteLoading').show();
                $('#deleteContent').hide();
                $('#confirmDeleteBtn').hide();
                $('#deleteTargetName').text(name);
                $('#dependencyList').empty();
                $('#dependenciesArea').hide();
                $('#noDependenciesMsg').show();
                $('#deleteIconArea i').removeClass('text-danger').addClass('text-warning').removeClass('fa-times-circle').addClass('fa-exclamation-circle');

                $.ajax({
                    url: 'check_local_dependencies.php',
                    type: 'GET',
                    data: { id: id },
                    success: function(response) {
                        $('#deleteLoading').hide();
                        $('#deleteContent').show();

                        if (response.has_dependencies) {
                            $('#dependenciesArea').show();
                            $('#noDependenciesMsg').hide();
                            $('#confirmDeleteBtn').hide();
                            $('#deleteIconArea i').removeClass('text-warning').addClass('text-danger').removeClass('fa-exclamation-circle').addClass('fa-times-circle');

                            var listHtml = '<ul class="list-unstyled mb-0">';
                            if (response.assets.length > 0) {
                                listHtml += '<li class="font-weight-bold mb-2 text-dark"><i class="fas fa-microchip mr-2 text-primary"></i>' + <?php echo json_encode(__('Ativos')); ?> + ' (' + response.assets.length + ')</li>';
                                $.each(response.assets, function(i, asset) {
                                    listHtml += '<li class="ml-4 small mb-1"><span class="badge badge-primary mr-2">' + asset.tag + '</span> ' + asset.modelo + '</li>';
                                });
                            }
                            if (response.children.length > 0) {
                                if (response.assets.length > 0) listHtml += '<hr class="my-2">';
                                listHtml += '<li class="font-weight-bold mb-2 text-dark"><i class="fas fa-sitemap mr-2 text-info"></i>' + <?php echo json_encode(__('Sub-locais')); ?> + ' (' + response.children.length + ')</li>';
                                $.each(response.children, function(i, child) {
                                    listHtml += '<li class="ml-4 small mb-1"><i class="fas fa-angle-right mr-2"></i>' + child.nome + '</li>';
                                });
                            }
                            listHtml += '</ul>';
                            $('#dependencyList').html(listHtml);
                        } else {
                            $('#confirmDeleteBtn').attr('href', 'apagar_local.php?id=' + id).show();
                        }
                    },
                    error: function() {
                        $('#deleteLoading').hide();
                        alert('Erro ao verificar dependências.');
                    }
                });
            });
        });
    </script>
    <?php include 'performance_footer.php'; ?>
    <script>
        $(document).ready(function() {
            $('.clickable-row').on('click', function(e) {
                if (!$(e.target).closest('button, a, .btn').length) {
                    const href = $(this).data('href');
                    if (href) {
                        window.location = href;
                    }
                }
            });
        });
    </script>
</body>

</html>
