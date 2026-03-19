<?php
/**
 * GESTÃO DE USUÁRIOS: usuarios.php
 * Controle de níveis de acesso, perfis e dados cadastrais dos colaboradores.
 */
// Inclui os arquivos de autenticação e conexão com o banco de dados
include 'auth.php';
include 'conexao.php';
// Restrição de acesso: Apenas usuários com nível 'Admin' ou 'Suporte' podem gerenciar usuários
if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    // Redireciona para a página inicial se o usuário não tiver permissão
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br"> <!-- Define o idioma como português do Brasil -->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Gestão de Usuários - Asset Management</title>
    <!-- Favicon e Bibliotecas CSS Externas -->
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <?php include 'sidebar_style.php'; ?>
    <?php include 'pagination_style.php'; ?>
    <style>
        /* Estilos para linhas clicáveis da tabela */
        .clickable-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .clickable-row:hover {
            background-color: rgba(0, 0, 0, 0.05) !important;
        }

        /* Estilos para fotos de perfil em miniatura */
        .user-thumbnail {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Barra Lateral (Sidebar) -->
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include 'sidebar_brand.php'; ?>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>

        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <!-- Barra Superior (Topbar) -->
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top"
                    style="margin: 5px 23px;">
                    <div class="container-fluid">
                        <button class="btn btn-link d-md-none rounded-circle mr-3" id="sidebarToggleTop-1"
                            type="button"><i class="fas fa-bars"></i></button>
                        <!-- Busca Global de Usuários -->
                        <form
                            class="form-inline d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search position-relative">
                            <div class="input-group">
                                <input class="bg-light form-control border-0 small" type="text"
                                    placeholder="Pesquisar..." id="globalSearchInput" autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button"
                                        style="background: rgb(44,64,74); border: none;">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="globalSearchResults" class="dropdown-menu shadow animated--grow-in"
                                style="width: 100%; display: none;"></div>
                        </form>
                        <!-- Menu do Usuário Logado -->
                        <ul class="navbar-nav flex-nowrap ml-auto">
                            <li class="nav-item dropdown no-arrow">
                                <a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown"
                                    href="#">
                                    <span
                                        class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span>
                                    <img class="border rounded-circle img-profile"
                                        src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/avatar5.jpeg'; ?>"
                                        alt="Foto de Perfil">
                                </a>
                                <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in">
                                    <a class="dropdown-item" href="profile.php"><i
                                            class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a>
                                    <a class="dropdown-item" href="configuracoes.php"><i
                                            class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a>
                                    <?php if ($_SESSION['nivelUsuario'] !== 'Usuário'): ?>
                                        <a class="dropdown-item" href="equipamentos.php?status=Manutencao"><i
                                                class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>Ativos em
                                            Manutenção</a>
                                    <?php endif; ?>
                                    <div class="dropdown-divider"></div>
                                    <a href="logout.php" class="dropdown-item"><i
                                            class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>

                <div class="container-fluid" style="padding-left: 23px; padding-right: 23px;">
                    <h3 class="text-dark mb-4">Gerenciamento de Usuários</h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-xl-2 text-nowrap">
                                    <!-- Botão para cadastrar novo usuário -->
                                    <a class="btn btn-success btn-block active text-white pulse animated btn-user"
                                        role="button"
                                        style="background: rgb(44,64,74);border-radius: 10px;border-width: 0px;height: 50px;padding-top: 13px;"
                                        href="/cadastro_de_usuario.php">Cadastrar Novo</a>
                                </div>
                                <div class="col-md-6 col-xl-10">
                                    <div class="text-md-right dataTables_filter">
                                        <!-- Formulário de Filtros: Centro de Custo e Busca Textual -->
                                        <form method="GET" action="" class="form-inline justify-content-end">
                                            <div class="form-group mr-2">
                                                <input type="search" name="search"
                                                    class="form-control form-control-sm premium-filter"
                                                    placeholder="Buscar..."
                                                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                                    aria-label="Buscar usuários">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive table mt-2" role="grid" aria-describedby="usersDataTable_info">
                                <?php
                                // Configuração de Paginação
                                $results_per_page = 10; // Resultados por página
                                
                                // Sanitização dos parâmetros de busca
                                $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

                                $where_clauses = [];
                                // Aplica filtros se existirem
                                if (!empty($search)) {
                                    $where_clauses[] = "(nome LIKE '%$search%' OR sobrenome LIKE '%$search%' OR email LIKE '%$search%' OR usuarioAD LIKE '%$search%')";
                                }

                                $where_clause = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

                                // Busca o total de registros para cálculo da paginação
                                $sql_total = "SELECT COUNT(*) AS total FROM usuarios $where_clause";
                                $res_total = mysqli_query($conn, $sql_total);
                                $total_results = mysqli_fetch_assoc($res_total)['total'];
                                $total_pages = ceil($total_results / $results_per_page);

                                // Determina a página atual
                                $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                                $start_from = ($current_page - 1) * $results_per_page;

                                // Busca os usuários limitados pela paginação
                                $sql_users = "SELECT * FROM usuarios $where_clause ORDER BY id_usuarios DESC LIMIT $start_from, $results_per_page";
                                $result = mysqli_query($conn, $sql_users);
                                ?>

                                <table class="table my-0" id="usersDataTable">
                                    <thead>
                                        <tr>
                                            <th scope="col">Usuário</th>
                                            <th scope="col">E-Mail</th>
                                            <th scope="col">Centro de Custo</th>
                                            <th scope="col">Função</th>
                                            <th scope="col">Nível</th>
                                            <th scope="col">Unidade</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Popula a tabela com os dados do banco
                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $foto = !empty($row['foto_perfil']) ? htmlspecialchars($row['foto_perfil']) : '/assets/img/avatars/avatar1.jpeg';
                                                ?>
                                                <tr class="clickable-row"
                                                    onclick="window.location='profile.php?id=<?php echo $row['id_usuarios']; ?>'">
                                                    <td class="d-flex align-items-center">
                                                        <img src="<?php echo $foto; ?>" class="user-thumbnail"
                                                            alt="Foto de <?php echo htmlspecialchars($row['nome']); ?>">
                                                        <?php echo htmlspecialchars($row['usuarioAD']); ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['centroDeCusto']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['funcao']); ?></td>
                                                     <td>
                                                         <?php
                                                         $nivel_display = !empty($row['nivelUsuario']) && $row['nivelUsuario'] !== '-' ? $row['nivelUsuario'] : 'Usuário';
                                                         $badge_color = '#6c757d'; // Default Usuário (Gray)
                                                         if ($nivel_display === 'Admin') $badge_color = '#2c404a'; // Admin (Theme Dark)
                                                         if ($nivel_display === 'Suporte') $badge_color = '#36b9cc'; // Suporte (Cyan)
                                                         ?>
                                                         <span class="badge" style="background-color: <?php echo $badge_color; ?>; color: white;">
                                                             <?php echo htmlspecialchars($nivel_display); ?>
                                                         </span>
                                                     </td>
                                                    <td><?php echo htmlspecialchars($row['unidade']); ?></td>
                                                    <td>
                                                        <span
                                                            class="badge <?php echo ($row['status'] === 'Ativo') ? 'badge-success' : 'badge-danger'; ?>">
                                                            <?php echo htmlspecialchars($row['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <!-- Ações de Edição e Exclusão -->
                                                        <a class="btn btn-warning"
                                                            href="editar_usuario.php?id=<?php echo $row['id_usuarios']; ?>"
                                                            title="Editar Usuário" onclick="event.stopPropagation();">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a class="btn btn-danger"
                                                            href="apagar_usuario.php?id=<?php echo $row['id_usuarios']; ?>"
                                                            title="Excluir Usuário" onclick="event.stopPropagation();">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            // Mensagem se não houver resultados
                                            echo "<tr><td colspan='7' class='text-center'>Nenhum usuário encontrado.</td></tr>";
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
                                        echo "<li><a href='?page=" . ($current_page - 1) . "$search_param'>« Anterior</a></li>";
                                    }

                                    // Números das Páginas
                                    for ($page = 1; $page <= $total_pages; $page++) {
                                        if ($page == $current_page) {
                                            echo "<li class='active'><span>$page</span></li>";
                                        } else {
                                            echo "<li><a href='?page=$page$search_param'>$page</a></li>";
                                        }
                                    }

                                    // Botão Próximo
                                    if ($current_page < $total_pages) {
                                        echo "<li><a href='?page=" . ($current_page + 1) . "$search_param'>Próximo »</a></li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>

    <!-- Bibliotecas JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
</body>

</html>
<?php mysqli_close($conn); // Fecha a conexão com o banco de dados ?>