<?php
/**
 * EDIÇÃO DE LOCAL: editar_local.php
 * Interface para alteração de nomes, tipos e hierarquias de localização.
 */
include_once 'auth.php';
include_once 'conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: locais.php");
    exit();
}

$sql = "SELECT * FROM locais WHERE id_local = $id";
$res = $conn->query($sql);
$local = $res->fetch_assoc();

if (!$local) {
    header("Location: locais.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Editar Local'); ?> - Asset MGT</title>
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/Montserrat.css">
    <link rel="stylesheet" href="/assets/css/Nunito.css">
    <link rel="stylesheet" href="/assets/css/Raleway.css">
    <link rel="stylesheet" href="/assets/css/Roboto.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <?php include_once 'sidebar_style.php'; ?>
</head>
<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0" style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include_once 'sidebar_brand.php'; ?>
                <?php include_once 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <?php include_once 'topbar.php'; ?>
                <div class="container-fluid" style="padding-left: 23px; padding-right: 23px;">
                    <h3 class="text-dark mb-1"><i class="fas fa-map-marker-alt mr-2 text-warning"></i><?php echo __('Editar Local'); ?></h3>
                    
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary font-weight-bold m-0"><?php echo __('Dados do Local'); ?></h6>
                                </div>
                                <div class="card-body">
                                    <form action="update_local.php" method="POST">
                                        <input type="hidden" name="id_local" value="<?php echo $local['id_local']; ?>">
                                        
                                        <div class="form-group">
                                            <label for="nome_local"><strong><?php echo __('Nome do Local'); ?></strong></label>
                                            <input class="form-control premium-filter" type="text" id="nome_local" name="nome_local" value="<?php echo htmlspecialchars($local['nome_local']); ?>" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="tipo_local"><strong><?php echo __('Tipo'); ?></strong></label>
                                            <select class="form-control premium-filter" id="tipo_local" name="tipo_local" required>
                                                <?php
                                                $tipos = ['Prédio', 'Andar', 'Sala', 'Rack', 'Setor'];
                                                foreach($tipos as $t) {
                                                    $sel = ($local['tipo_local'] == $t) ? 'selected' : '';
                                                    echo "<option value='$t' $sel>" . __($t) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="id_parent_local"><strong><?php echo __('Local Pai (Opcional)'); ?></strong></label>
                                            <select class="form-control premium-filter" id="id_parent_local" name="id_parent_local">
                                                <option value=""><?php echo __('Nenhum (Raiz)'); ?></option>
                                                <?php
                                                // Lista todos exceto ele mesmo para evitar circularidade direta (ID != $id)
                                                // Nota: Para hierarquias profundas, seria ideal uma verificação mais robusta,
                                                // mas ID != $id resolve o caso mais comum.
                                                $sql_parents = "SELECT id_local, nome_local, tipo_local FROM locais WHERE id_local != $id ORDER BY nome_local ASC";
                                                $res_parents = $conn->query($sql_parents);
                                                while($p = $res_parents->fetch_assoc()) {
                                                    $sel_p = ($local['id_parent_local'] == $p['id_local']) ? 'selected' : '';
                                                    echo "<option value='{$p['id_local']}' $sel_p>{$p['nome_local']} ({$p['tipo_local']})</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="d-flex justify-content-between mt-4">
                                            <a href="locais.php" class="btn btn-secondary" style="border-radius: 10px; padding: 10px 25px; border: none; font-weight: 600;">
                                                <?php echo __('Cancelar'); ?>
                                            </a>
                                            <button class="btn btn-primary" type="submit" style="background: #2c404a; border-radius: 10px; padding: 10px 30px; border: none; font-weight: 600;">
                                                <i class="fas fa-save mr-2"></i><?php echo __('Salvar Alterações'); ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
