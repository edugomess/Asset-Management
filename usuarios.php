<?php include_once 'performance_header.php'; ?>
<?php
/**
 * GESTÃO DE USUÁRIOS: usuarios.php
 * Visualização e administração de todos os usuários cadastrados no sistema.
 */
include_once 'auth.php';
include_once 'conexao.php';

// Verificação de permissão: Apenas Admins e Suporte acessam esta lista completa
if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    header("Location: index.php");
    exit();
}

// Lógica de mapeamento de IDs de Centro de Custo para links
$cc_map = [];
$res_cc = mysqli_query($conn, "SELECT id_centro_de_custo, nomeSetor FROM centro_de_custo");
while ($row_cc = mysqli_fetch_assoc($res_cc)) {
    $cc_map[$row_cc['nomeSetor']] = $row_cc['id_centro_de_custo'];
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Usuários'); ?> - Asset Mgt</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <?php renderPerformanceHints(); ?>
    <?php include_once 'sidebar_style.php'; ?>
    <style>
        .clickable-row { cursor: pointer; transition: all 0.2s; }
        .clickable-row:hover { background-color: rgba(0,0,0,0.05) !important; }
        .cc-link { color: #000 !important; font-weight: 800 !important; }
        .cc-link:hover { text-decoration: underline; color: #2c404a !important; }
        .user-thumbnail { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; margin-right: 12px; border: 2px solid #e3e6f0; }
        
        .btn-action {
            width: 35px;
            height: 35px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin: 0 2px;
            transition: all 0.2s;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
                    <h3 class="text-dark mb-4"><i class="fas fa-user-shield mr-2 text-dark"></i><?php echo __('Usuários'); ?></h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 15px;">
                                <div class="d-flex align-items-center">
                                    <a class="btn-premium-cadastro pulse animated" role="button"
                                        href="cadastro_de_usuario.php"><?php echo __('Cadastrar Novo'); ?></a>
                                    <a href="#" class="btn-premium-import ml-3 shadow-sm pulse animated" 
                                        role="button"
                                        onclick="window.abrirModalImportar('usuarios'); return false;"
                                        data-import-type="usuarios">
                                        <i class="fas fa-file-import mr-2"></i><?php echo __('Importar CSV'); ?>
                                    </a>
                                </div>

                                <div class="dataTables_filter">
                                    <!-- Formulário de busca simples na tabela -->
                                    <form method="GET" action="" class="form-inline">
                                        <div class="form-group mr-2">
                                            <input type="search" name="search"
                                                class="form-control form-control-sm premium-filter"
                                                placeholder="<?php echo __('Buscar...'); ?>" onsearch="this.form.submit()"
                                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                                aria-label="<?php echo __('Buscar Usuários'); ?>">
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive table mt-2" id="dataTable" role="grid"
                                aria-describedby="dataTable_info">
                                <?php
                                $results_per_page = 10;
                                $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                                
                                // Filtros de Busca
                                $where_clause = "";
                                if (!empty($search)) {
                                    $where_clause = "WHERE nome LIKE '%$search%' OR sobrenome LIKE '%$search%' OR email LIKE '%$search%' OR centroDeCusto LIKE '%$search%'";
                                }

                                // Paginação
                                $sql_count = "SELECT COUNT(*) AS total FROM usuarios $where_clause";
                                $res_count = mysqli_query($conn, $sql_count);
                                $total_results = mysqli_fetch_assoc($res_count)['total'];
                                $total_pages = ceil($total_results / $results_per_page);
                                $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                                $start_from = ($current_page - 1) * $results_per_page;

                                // Consulta usuários - Ordenado Alfabeticamente
                                $sql = "SELECT * FROM usuarios $where_clause ORDER BY nome ASC, sobrenome ASC LIMIT $start_from, $results_per_page";
                                $result = mysqli_query($conn, $sql);
                                ?>

                                <table class="table my-0" id="userTable">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('Usuário'); ?></th>
                                            <th><?php echo __('E-mail'); ?></th>
                                            <th><?php echo __('Função'); ?></th>
                                            <th><?php echo __('Setor'); ?></th>
                                            <th><?php echo __('CC'); ?></th>
                                            <th style="text-align: center;"><?php echo __('Nível'); ?></th>
                                            <th style="text-align: center;"><?php echo __('Status'); ?></th>
                                            <th style="text-align: center;"><?php echo __('Ações'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $foto = !empty($row['foto_perfil']) ? htmlspecialchars($row['foto_perfil']) : '/assets/img/avatars/avatar1.jpeg';
                                                ?>
                                                <tr class="clickable-row"
                                                    data-href="perfil_usuario.php?id=<?php echo $row['id_usuarios']; ?>">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?php echo $foto; ?>" class="user-thumbnail"
                                                                alt="<?php echo __('Foto de ') . htmlspecialchars($row['nome']); ?>">
                                                            <strong><?php echo htmlspecialchars($row['nome'] . ' ' . $row['sobrenome']); ?></strong>
                                                        </div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['funcao']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['setor'] ?? '-'); ?></td>
                                                    <td>
                                                        <?php 
                                                        $cc_nome = $row['centroDeCusto'];
                                                        if (isset($cc_map[$cc_nome])) {
                                                            echo "<a href='perfil_centro_de_custo.php?id=" . $cc_map[$cc_nome] . "' class='cc-link' onclick='event.stopPropagation()'>" . htmlspecialchars($cc_nome) . "</a>";
                                                        } else {
                                                            echo htmlspecialchars($cc_nome);
                                                        }
                                                        ?>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <?php
                                                        $nivel_display = !empty($row['nivelUsuario']) && $row['nivelUsuario'] !== '-' ? __($row['nivelUsuario']) : __('Usuário');
                                                        $badge_color = '#6c757d'; // Default Usuário (Gray)
                                                        if ($row['nivelUsuario'] === 'Admin') {
                                                            $badge_color = '#2c404a'; // Admin (Theme Dark)
                                                        }
                                                        if ($row['nivelUsuario'] === 'Suporte') {
                                                            $badge_color = '#36b9cc'; // Suporte (Cyan)
                                                        }
                                                        ?>
                                                        <span class="status-badge"
                                                            style="background-color: <?php echo $badge_color; ?>; color: white;">
                                                            <?php echo htmlspecialchars($nivel_display); ?>
                                                        </span>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <span
                                                            class="status-badge badge-<?php echo ($row['status'] === 'Ativo') ? 'success' : 'danger'; ?>">
                                                            <?php echo __(htmlspecialchars($row['status'])); ?>
                                                        </span>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <!-- Ações de Edição e Exclusão -->
                                                        <a class="btn btn-warning btn-action"
                                                            href="editar_usuario.php?id=<?php echo $row['id_usuarios']; ?>"
                                                            onclick="event.stopPropagation()"
                                                            title="<?php echo __('Editar Usuário'); ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button class="btn btn-danger btn-action"
                                                            title="<?php echo __('Excluir Usuário'); ?>"
                                                            onclick="event.stopPropagation(); deleteUser(<?php echo $row['id_usuarios']; ?>, '<?php echo htmlspecialchars($row['nome'] . ' ' . $row['sobrenome']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            // Mensagem se não houver resultados
                                            echo "<tr><td colspan='8' class='text-center'>" . __('Nenhum usuário encontrado.') . "</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Sistema de Paginação Customizada -->
                            <div class="d-flex justify-content-start mt-3">
                                <ul class="pagination-custom">
                                    <?php
                                    $search_param = !empty($search) ? "&search=" . urlencode($search) : "";

                                    // Botão Anterior
                                    if ($current_page > 1) {
                                        echo "<li><a href='?page=" . ($current_page - 1) . "$search_param'>« " . __('Anterior') . "</a></li>";
                                    }

                                    // Páginas Numeradas
                                    for ($page = 1; $page <= $total_pages; $page++) {
                                        $active_class = ($page == $current_page) ? "class='active'" : "";
                                        echo "<li $active_class><a href='?page=$page$search_param'>$page</a></li>";
                                    }

                                    // Botão Próximo
                                    if ($current_page < $total_pages) {
                                        echo "<li><a href='?page=" . ($current_page + 1) . "$search_param'>" . __('Próximo') . " »</a></li>";
                                    }
                                    ?>
                                </ul>
                            </div>


                            <?php
                            mysqli_close($conn);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/bootstrap/js/bootstrap.min.js"></script>
    <?php include_once 'modal_importar.php'; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script src="/assets/js/bs-init.js"></script>
    <script src="/assets/js/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
        function deleteUser(id, name) {
            Swal.fire({
                title: '<?php echo __('Excluir Usuário?'); ?>',
                text: '<?php echo __('Deseja realmente remover o acesso de:'); ?> ' + name + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#2c404a',
                confirmButtonText: '<?php echo __('Sim, excluir'); ?>',
                cancelButtonText: '<?php echo __('Cancelar'); ?>'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `apagar_usuario.php?id=${id}`;
                }
            });
        }

        // Lógica para clique na linha (delegando para o documento para maior confiabilidade)
        $(document).ready(function() {
            $(document).on('click', '.clickable-row', function(e) {
                if (!$(e.target).closest('button, a, .btn').length) {
                    const href = $(this).data('href');
                    if (href) {
                        window.location = href;
                    }
                }
            });
        });
    </script>
    <script src="/assets/js/global_search.js" defer></script>
    <?php include_once 'performance_footer.php'; ?>
</body>

</html>