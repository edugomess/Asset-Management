<?php
/**
 * EXCLUSÃO DE LOCAL: apagar_local.php
 * Verifica dependências (ativos e sub-locais) antes de permitir a exclusão.
 */
include_once 'auth.php';
include_once 'conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: locais.php");
    exit();
}

// 1. Verificar se existem ativos vinculados a este local
$sql_assets = "SELECT id_asset, tag, modelo FROM ativos WHERE id_local = $id";
$res_assets = $conn->query($sql_assets);
$linked_assets = [];
while ($row = $res_assets->fetch_assoc()) {
    $linked_assets[] = $row;
}

// 2. Verificar se existem locais filhos vinculados (hierarquia)
$sql_children = "SELECT id_local, nome_local, tipo_local FROM locais WHERE id_parent_local = $id";
$res_children = $conn->query($sql_children);
$linked_children = [];
while ($row = $res_children->fetch_assoc()) {
    $linked_children[] = $row;
}

// Se houver qualquer vínculo, impedir a exclusão e mostrar a lista
if (!empty($linked_assets) || !empty($linked_children)) {
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'pt-BR') ? 'pt-br' : 'en'; ?>">
    <head>
        <meta charset="utf-8">
        <title><?php echo __('Exclusão Bloqueada'); ?> - Asset MGT</title>
        <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
        <?php include_once 'sidebar_style.php'; ?>
        <style>
            .block-card { border-left: 5px solid #e74a3b; border-radius: 15px; }
            .item-list { background: #f8f9fc; border-radius: 10px; padding: 15px; margin-top: 10px; }
        </style>
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
                    <div class="container-fluid">
                        <div class="row justify-content-center mt-5">
                            <div class="col-lg-8">
                                <div class="card shadow block-card">
                                    <div class="card-body p-5">
                                        <div class="text-center mb-4">
                                            <i class="fas fa-exclamation-triangle fa-4x text-danger mb-3"></i>
                                            <h2 class="text-dark font-weight-bold"><?php echo __('Exclusão Bloqueada'); ?></h2>
                                            <p class="text-muted lead"><?php echo __('Não é possível excluir este local pois existem itens vinculados a ele.'); ?></p>
                                        </div>

                                        <?php if (!empty($linked_assets)): ?>
                                            <h5 class="font-weight-bold text-gray-800"><i class="fas fa-microchip mr-2 text-primary"></i><?php echo __('Ativos Vinculados'); ?> (<?php echo count($linked_assets); ?>)</h5>
                                            <div class="item-list mb-4">
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($linked_assets as $asset): ?>
                                                        <li class="mb-2">
                                                            <a href="perfil_ativo.php?id=<?php echo $asset['id_asset']; ?>" target="_blank" class="text-decoration-none">
                                                                <span class="badge badge-primary mr-2"><?php echo htmlspecialchars($asset['tag']); ?></span>
                                                                <span class="text-dark"><?php echo htmlspecialchars($asset['modelo']); ?></span>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($linked_children)): ?>
                                            <h5 class="font-weight-bold text-gray-800"><i class="fas fa-sitemap mr-2 text-info"></i><?php echo __('Sub-locais Vinculados'); ?> (<?php echo count($linked_children); ?>)</h5>
                                            <div class="item-list mb-4">
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($linked_children as $child): ?>
                                                        <li class="mb-1 text-dark">
                                                            <i class="fas fa-angle-right mr-2"></i>
                                                            <?php echo htmlspecialchars($child['nome_local']); ?>
                                                            <small class="text-muted">(<?php echo __($child['tipo_local']); ?>)</small>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>

                                        <div class="alert alert-warning small border-0 shadow-sm mt-4">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            <?php echo __('Para excluir este local, você deve primeiro remover ou mover todos os ativos e sub-locais listados acima.'); ?>
                                        </div>

                                        <div class="text-center mt-5">
                                            <a href="locais.php" class="btn btn-primary btn-lg px-5 shadow-sm" style="border-radius: 12px; background: #2c404a; border: none;">
                                                <i class="fas fa-arrow-left mr-2"></i><?php echo __('Voltar para Gestão de Locais'); ?>
                                            </a>
                                        </div>
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
    <?php
    exit();
}

// Se chegou aqui, não há vínculos. Pode apagar.
$sql = "DELETE FROM locais WHERE id_local = $id";
if ($conn->query($sql) === true) {
    header("Location: locais.php?msg=deleted");
} else {
    header("Location: locais.php?msg=error");
}

$conn->close();
?>
