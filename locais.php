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
    <?php include_once 'sidebar_style.php'; ?>
    <style>
        .tree-level-1 { padding-left: 0; font-weight: bold; color: #2c404a; }
        .tree-level-2 { padding-left: 20px; }
        .tree-level-3 { padding-left: 40px; }
        .tree-level-4 { padding-left: 60px; }
        .tree-level-5 { padding-left: 80px; }
        
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
                                                            
                                                            echo "<tr>
                                                                <td class='tree-level-$level'>
                                                                    <i class='fas $icon mr-2' style='color: #4e73df;'></i> " . htmlspecialchars($row['nome_local']) . "
                                                                </td>
                                                                <td><span class='dot-badge $badgeClass'>" . __($row['tipo_local']) . "</span></td>
                                                                <td class='text-right'>
                                                                    <a href='apagar_local.php?id={$row['id_local']}' class='btn btn-danger btn-sm' style='border-radius: 8px;' onclick=\"return confirm('Tem certeza que deseja apagar este local? Isso pode afetar equipamentos vinculados.');\"><i class='fas fa-trash'></i></a>
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
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/theme.js"></script>
</body>
</html>
