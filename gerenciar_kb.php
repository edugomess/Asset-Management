<?php
/**
 * GESTÃO DA BASE DE CONHECIMENTO: gerenciar_kb.php
 * Interface para Administração e Suporte gerenciarem artigos de ajuda.
 */
include_once 'auth.php';
include_once 'conexao.php';

// Restrição de acesso: Apenas Admin e Suporte
if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Base de Conhecimento'); ?> - Asset Mgt</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <?php include_once 'sidebar_style.php'; ?>
    <?php include_once 'pagination_style.php'; ?>
    <style>
        .kb-icon-box {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(44, 64, 74, 0.1);
            color: #2c404a;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }
        .clickable-row { cursor: pointer; transition: all 0.2s; }
        .clickable-row:hover { background-color: rgba(0,0,0,0.02) !important; }
        .article-title { font-weight: 700; color: #2c404a; margin-bottom: 0; }
        .article-meta { font-size: 0.75rem; color: #858796; }
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
                <div class="container-fluid">
                    <div class="d-sm-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeInDown">
                        <h3 class="text-dark mb-0"><i class="fas fa-book-reader mr-2 text-info"></i><?php echo __('Base de Conhecimento'); ?></h3>
                    </div>

                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row mb-4 align-items-center">
                                <div class="col-md-auto">
                                    <a class="btn btn-primary shadow-sm text-white px-4" role="button" href="adicionar_artigo.php" style="background: #2c404a; border: none; border-radius: 10px; height: 50px; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                        <i class="fas fa-plus mr-2"></i><?php echo __('Novo Artigo'); ?>
                                    </a>
                                </div>
                                <div class="col">
                                    <form method="GET" action="" class="form-horizontal">
                                        <div class="input-group mb-0 shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid #e3e6f0;">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-0 text-muted pl-4">
                                                    <i class="fas fa-search"></i>
                                                </span>
                                            </div>
                                            <input type="text" name="search" class="form-control border-0 bg-white py-4" 
                                                style="height: 50px; font-size: 1rem;"
                                                placeholder="<?php echo __('Buscar artigos, soluções ou palavras-chave...'); ?>"
                                                value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary px-4 font-weight-bold" type="submit" style="background: #2c404a; border: none;">
                                                    <?php echo __('PESQUISAR'); ?>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead style="background: #f8f9fc;">
                                        <tr class="text-uppercase small font-weight-bold text-muted">
                                            <th class="border-0 px-4 py-3"><?php echo __('Artigo'); ?></th>
                                            <th class="border-0 py-3"><?php echo __('Categoria'); ?></th>
                                            <th class="border-0 py-3"><?php echo __('Última Atualização'); ?></th>
                                            <th class="border-0 text-center py-3"><?php echo __('Utilidade'); ?></th>
                                            <th class="border-0 text-right px-4 py-3"><?php echo __('Ações'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        <?php
                                        $results_per_page = 10;
                                        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                                        $where = !empty($search) ? "WHERE titulo LIKE '%$search%' OR palavras_chave LIKE '%$search%' OR categoria LIKE '%$search%'" : "";

                                        $total_q = $conn->query("SELECT COUNT(*) as total FROM base_conhecimento $where");
                                        $total_r = $total_q->fetch_assoc();
                                        $total_pages = ceil($total_r['total'] / $results_per_page);
                                        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                        $start = ($current_page - 1) * $results_per_page;

                                        $sql = "SELECT * FROM base_conhecimento $where ORDER BY data_atualizacao DESC LIMIT $start, $results_per_page";
                                        $res = $conn->query($sql);

                                        if ($res && $res->num_rows > 0) {
                                            while ($row = $res->fetch_assoc()) {
                                                $date = date('d/m/Y H:i', strtotime($row['data_atualizacao']));
                                                $utility = $row['votos_uteis'] - $row['votos_nao_uteis'];
                                                $util_class = ($utility >= 0) ? 'text-success' : 'text-danger';
                                                
                                                echo "<tr class='clickable-row' data-href='visualizar_kb.php?id={$row['id']}'>
                                                    <td class='px-4'>
                                                        <div class='d-flex align-items-center'>
                                                            <div class='kb-icon-box' style='background: #f0f3f5; border: 1px solid #e3e6f0;'><i class='fas fa-file-alt text-primary'></i></div>
                                                            <div>
                                                                <p class='article-title mb-0'>" . htmlspecialchars($row['titulo']) . "</p>
                                                                <span class='article-meta text-muted small'>ID: #{$row['id']}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><span class='badge badge-light border py-1 px-2 mb-0' style='background: #eef2f7; color: #2c404a; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;'>" . __($row['categoria']) . "</span></td>
                                                    <td class='small text-muted'>$date</td>
                                                    <td class='text-center font-weight-bold $util_class'>" . ($utility > 0 ? '+' : '') . "$utility</td>
                                                    <td class='text-right px-4 action-cell'>
                                                        <a href='editar_artigo.php?id={$row['id']}' class='btn btn-warning btn-action rounded-circle mr-1' title='" . __('Editar') . "' onclick='event.stopPropagation()' style='width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;'><i class='fas fa-pencil-alt'></i></a>
                                                        <button class='btn btn-danger btn-action rounded-circle' title='" . __('Excluir') . "' onclick='deleteArticle({$row['id']}); event.stopPropagation();' style='width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;'><i class='fas fa-trash'></i></button>
                                                    </td>
                                                </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center py-5 text-muted'><i class='fas fa-search fa-2x mb-3 d-block opacity-50'></i>" . __('Nenhum artigo encontrado.') . "</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-start mt-4 px-2">
                                <ul class="pagination-custom">
                                    <?php
                                    if ($total_pages > 1) {
                                        if ($current_page > 1) {
                                            echo "<li><a href='?page=" . ($current_page - 1) . "&search=$search'>« " . __('Anterior') . "</a></li>";
                                        }
                                        for ($p = 1; $p <= $total_pages; $p++) {
                                            $active = ($p == $current_page) ? "class='active'" : "";
                                            echo "<li $active><a href='?page=$p&search=$search'>$p</a></li>";
                                        }
                                        if ($current_page < $total_pages) {
                                            echo "<li><a href='?page=" . ($current_page + 1) . "&search=$search'>" . __('Próximo') . " »</a></li>";
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer removed based on user request/error correction -->
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('.clickable-row').click(function() {
                window.location = $(this).data('href');
            });
        });

        function deleteArticle(id) {
            Swal.fire({
                title: '<?php echo __('Tem certeza?'); ?>',
                text: "<?php echo __('O artigo será excluído permanentemente da Base de Conhecimento.'); ?>",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<?php echo __('Sim, excluir!'); ?>',
                cancelButtonText: '<?php echo __('Cancelar'); ?>'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `processar_kb.php?action=delete&id=${id}`;
                }
            })
        }
    </script>
</body>

</html>
