<?php include 'auth.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Table - Brand</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=f3d9abe8d5aa7831c01bfaa2a1563712">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=41e93b37bc495fd67938799bb3a6adaf">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="/assets/css/Simple-footer-by-krissy.css?h=73316da5ae5ad6b51632cd2e5413f263">
    <?php include 'sidebar_style.php'; ?>
    <?php include 'pagination_style.php'; ?>
    <style>
        .clickable-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .clickable-row:hover {
            background-color: rgba(0, 0, 0, 0.05) !important;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion p-0">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include 'sidebar_brand.php'; ?>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top"
                    style="margin: 5px 23px;">
                    <div class="container-fluid"><button class="btn btn-link d-md-none rounded-circle mr-3"
                            id="sidebarToggleTop-1" type="button"><i class="fas fa-bars"></i></button>
                        <form
                            class="form-inline d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search position-relative">
                            <div class="input-group">
                                <input class="bg-light form-control border-0 small" type="text"
                                    placeholder="Pesquisar..." id="globalSearchInput" autocomplete="off">
                                <div class="input-group-append"><button class="btn btn-primary py-0" type="button"
                                        style="background: rgb(44,64,74);"><i class="fas fa-search"></i></button></div>
                            </div>
                            <div id="globalSearchResults" class="dropdown-menu shadow animated--grow-in"
                                style="width: 100%; display: none;"></div>
                        </form>
                        <ul class="navbar-nav flex-nowrap ml-auto">
                            <li class="nav-item dropdown no-arrow">
                                <a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown"
                                    href="#">
                                    <span
                                        class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span>
                                    <img class="border rounded-circle img-profile"
                                        src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/Captura%20de%20Tela%202021-08-04%20às%2012.25.13.png?h=fcfb924f0ac1ab5f595f029bf526e62d'; ?>">
                                </a>
                                <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in">
                                    <a class="dropdown-item" href="profile.php"><i
                                            class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a>
                                    <a class="dropdown-item" href="configuracoes.php"><i
                                            class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a>
                                    <a class="dropdown-item" href="equipamentos.php?status=Manutencao"><i
                                            class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>Ativos em
                                        Manutenção</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="logout.php" class="dropdown-item"><i
                                            class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="container-fluid" style="padding-left: 23px; padding-right: 23px;">
                    <h3 class="text-dark mb-4">Usuários</h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-xl-3 text-nowrap">
                                    <div id="dataTable_length" class="dataTables_length" aria-controls="dataTable">
                                    </div><a class="btn btn-success btn-block active text-white pulse animated btn-user"
                                        role="button"
                                        style="background: rgb(44,64,74);border-radius: 10px;padding: 30px, 30px;border-width: 0px;height: 50px;margin-top: 0px;padding-top: 13px;"
                                        href="/cadastro_de_usuario.php">Cadastrar Novo</a>
                                </div>
                                <div class="col-md-6 col-xl-9">
                                    <div class="text-md-right dataTables_filter" id="dataTable_filter">
                                        <form method="GET" action=""><label><input type="search" name="search"
                                                    class="form-control form-control-sm" aria-controls="dataTable"
                                                    placeholder="Buscar..."
                                                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"></label>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive table mt-2" id="dataTable" role="grid"
                                aria-describedby="dataTable_info">
                                <table class="table my-0" id="dataTable">
                                    <tbody>
                                        <div class="table-responsive table mt-2" id="dataTable" role="grid"
                                            aria-describedby="dataTable_info">
                                            <?php

                                            include 'conexao.php';

                                            // Define how many results you want per page
                                            $results_per_page = 10;

                                            // Buscar termo de pesquisa
                                            $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                                            $where_clause = "";
                                            if (!empty($search)) {
                                                $where_clause = "WHERE nome LIKE '%$search%' OR sobrenome LIKE '%$search%' OR email LIKE '%$search%' OR usuarioAD LIKE '%$search%'";
                                            }

                                            // Find out the number of results in the database
                                            $sql = "SELECT COUNT(*) AS total FROM usuarios $where_clause";
                                            $result = mysqli_query($conn, $sql);
                                            $row = mysqli_fetch_assoc($result);
                                            $total_results = $row['total'];

                                            // Determine number of pages needed
                                            $total_pages = ceil($total_results / $results_per_page);

                                            // Determine the current page number from the URL, if not set default to 1
                                            $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

                                            // Calculate the starting limit for the records
                                            $start_from = ($current_page - 1) * $results_per_page;

                                            // Fetch the selected results from the database
                                            $sql = "SELECT * FROM usuarios $where_clause ORDER BY id_usuarios DESC LIMIT $start_from, $results_per_page";
                                            $result = mysqli_query($conn, $sql);
                                            ?>

                                            <table class="table my-0" id="dataTable">
                                                <thead>
                                                    <tr>
                                                        <th>Usuário</th>
                                                        <th>E-Mail</th>
                                                        <th>Centro de Custo</th>
                                                        <th>Função</th>
                                                        <th>Unidade</th>
                                                        <th>Status</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if (mysqli_num_rows($result) > 0) {
                                                        while ($row = mysqli_fetch_assoc($result)) {
                                                            echo "<tr class='clickable-row' onclick=\"window.location='profile.php?id=" . $row['id_usuarios'] . "'\">
                    <td>" . htmlspecialchars($row['usuarioAD']) . "</td>
                    <td>" . htmlspecialchars($row['email']) . "</td>
                    <td>" . htmlspecialchars($row['centroDeCusto']) . "</td>
                    <td>" . htmlspecialchars($row['funcao']) . "</td>
                    <td>" . htmlspecialchars($row['unidade']) . "</td>
                    <td>" . htmlspecialchars($row['status']) . "</td>
                    <td>
                        <a class='btn btn-warning' href='editar_usuario.php?id=" . $row['id_usuarios'] . "' onclick='event.stopPropagation();'><i class='fas fa-edit'></i></a>
                        <a class='btn btn-danger' href='apagar_usuario.php?id=" . $row['id_usuarios'] . "' onclick='event.stopPropagation();'><i class='fas fa-trash'></i></a>
                    </td>
                </tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='11'>Nenhum dado encontrado.</td></tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                                <thead>
                                                    <tr>
                                                        <th>Usuário</th>
                                                        <th>E-Mail</th>
                                                        <th>Centro de Custo</th>
                                                        <th>Função</th>
                                                        <th>Unidade</th>
                                                        <th>Status</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                            </table>

                                            <div class="d-flex justify-content-start mt-3">
                                                <ul class="pagination-custom">
                                                    <?php
                                                    $search_param = !empty($search) ? "&search=" . urlencode($search) : "";
                                                    if ($current_page > 1) {
                                                        echo "<li><a href='?page=" . ($current_page - 1) . "$search_param'>« Anterior</a></li>";
                                                    }
                                                    for ($page = 1; $page <= $total_pages; $page++) {
                                                        if ($page == $current_page) {
                                                            echo "<li class='active'><span>$page</span></li>";
                                                        } else {
                                                            echo "<li><a href='?page=$page$search_param'>$page</a></li>";
                                                        }
                                                    }
                                                    if ($current_page < $total_pages) {
                                                        echo "<li><a href='?page=" . ($current_page + 1) . "$search_param'>Próximo »</a></li>";
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
                </div>
                <footer class="sticky-footer">
                    <section class="text-center footer" style="padding: 10px; background-color: #212121; color: white;">
                        <p style="margin-bottom: 0px; font-size: 15px;">DEGB&nbsp;Copyright © 2015-2024<br></p>
                    </section>
                </footer>

            </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
        <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
        <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
        <script src="/assets/js/global_search.js"></script>
</body>

</html>