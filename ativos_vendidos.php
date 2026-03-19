<!DOCTYPE html>
<?php
/**
 * LISTAGEM DE ATIVOS VENDIDOS: ativos_vendidos.php
 * Exibe o histórico de equipamentos que foram retirados do inventário via venda.
 * Inclui paginação e busca global.
 */
?>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Animated-numbers-section.css?h=f70eceb0d9266e15c95f7e63479d6265">
    <link rel="stylesheet" href="/assets/css/Bootstrap-Image-Uploader.css?h=406ba72429389f6080fdb666c60fb216">
    <link rel="stylesheet" href="/assets/css/card-image-zoom-on-hover.css?h=82e6162bc70edfde8bfd14b57fdcb3f7">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet"
        href="/assets/css/Form-Select---Full-Date---Month-Day-Year.css?h=7b6a3c2cb7894fdb77bae43c70b92224">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightpick@1.3.4/css/lightpick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="/assets/css/Map-Clean.css?h=bdd15207233b27ebc7c6fc928c71b34c">
    <link rel="stylesheet" href="/assets/css/Modern-Contact-Form.css?h=af67b929d317df499a992472a9bb8fcc">
    <link rel="stylesheet"
        href="/assets/css/Multi-Select-Dropdown-by-Jigar-Mistry.css?h=28bd9d636c700fbf60086e2bcb002efb">
    <link rel="stylesheet"
        href="/assets/css/Password-Strenght-Checker---Ambrodu-1.css?h=1af6ac373aa34a3b40f3d87a4f494eaf">
    <link rel="stylesheet"
        href="/assets/css/Password-Strenght-Checker---Ambrodu.css?h=5818638767f362b9d58a96550bd9a9a3">
    <link rel="stylesheet" href="/assets/css/Simple-footer-by-krissy.css?h=73316da5ae5ad6b51632cd2e5413f263">
    <link rel="stylesheet" href="/assets/css/TR-Form.css?h=ce0bc58b5b8027e2406229d460f4d895">
    <?php include 'sidebar_style.php'; ?>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0"><a
                    class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#">
                    <div class="sidebar-brand-icon rotate-n-15"><svg xmlns="http://www.w3.org/2000/svg" width="1em"
                            height="1em" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icon-tabler-layout-distribute-horizontal"
                            style="width: 30px;height: 30px;">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <line x1="4" y1="4" x2="20" y2="4"></line>
                            <line x1="4" y1="20" x2="20" y2="20"></line>
                            <rect x="6" y="9" width="12" height="6" rx="2"></rect>
                        </svg></div>
                    <div class="sidebar-brand-text mx-3"><span>ASSET MGT</span></div>
                </a>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <?php include 'topbar.php'; ?>
                <div class="container-fluid">
                    <h3 class="text-dark mb-4">Vendidos</h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-xl-3 text-nowrap">
                                    <div id="dataTable_length" class="dataTables_length" aria-controls="dataTable">
                                    </div><a class="btn btn-success btn-block active text-white pulse animated btn-user"
                                        role="button"
                                        style="background: rgb(44,64,74);border-radius: 10px;padding: 30px, 30px;border-width: 0px;height: 50px;margin-top: 23px;padding-top: 13px;"
                                        href="/inicio.php">Voltar</a>
                                </div>
                                <div class="col-md-6 col-xl-9">
                                    <div class="text-md-right dataTables_filter" id="dataTable_filter"><label><input
                                                type="search" class="form-control form-control-sm"
                                                aria-controls="dataTable" placeholder="Buscar..."></label></div>
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

                                            // Define a quantidade de registros exibidos por página (Padronizado)
                                            $results_per_page = 10;

                                            // Find out the number of results in the database (for vendidos status)
                                            $sql = "SELECT COUNT(*) AS total FROM venda";
                                            $result = mysqli_query($conn, $sql);
                                            $row = mysqli_fetch_assoc($result);
                                            $total_results = $row['total'];

                                            // Determine number of pages needed
                                            $total_pages = ceil($total_results / $results_per_page);

                                            // Determine the current page number from the URL, if not set default to 1
                                            $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

                                            // Calculate the starting limit for the records
                                            $start_from = ($current_page - 1) * $results_per_page;

                                            // Fetch the selected results from the database (ativos vendidos)
                                            $sql = "SELECT * FROM venda ORDER BY id DESC LIMIT $start_from, $results_per_page";
                                            $result = mysqli_query($conn, $sql);
                                            ?>

                                            <table class="table my-0" id="dataTable">
                                                <thead>
                                                    <tr>
                                                        <th>Categoria</th>
                                                        <th>Fabricante</th>
                                                        <th>Modelo</th>
                                                        <th>Tag</th>
                                                        <th>Valor</th>
                                                        <th>MAC Address</th>
                                                        <th>Usuário</th>
                                                        <th>Data da Venda</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if (mysqli_num_rows($result) > 0) {
                                                        while ($row = mysqli_fetch_assoc($result)) {
                                                            // Retrieve the user who is assigned to the asset
                                                            $assigned_to = $row['assigned_to'];
                                                            $sql_user = "SELECT nome FROM usuarios WHERE id_usuarios = '$assigned_to'";
                                                            $result_user = mysqli_query($conn, $sql_user);
                                                            $user_name = "Não atribuído";
                                                            if ($result_user && mysqli_num_rows($result_user) > 0) {
                                                                $user = mysqli_fetch_assoc($result_user);
                                                                $user_name = htmlspecialchars($user['nome']);
                                                            }
                                                            ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                                                                <td><?php echo htmlspecialchars($row['fabricante']); ?></td>
                                                                <td><?php echo htmlspecialchars($row['modelo']); ?></td>
                                                                <td><?php echo htmlspecialchars($row['tag']); ?></td>
                                                                <td><?php echo htmlspecialchars($row['valor']); ?></td>
                                                                <td><?php echo htmlspecialchars($row['macAdress']); ?></td>
                                                                <td><?php echo $user_name; ?></td>
                                                                <td><?php echo htmlspecialchars($row['data_venda']); ?></td>

                                                            </tr>
                                                            <?php
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='11'>Nenhum ativo vendido encontrado.</td></tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>

                                            <div class="d-flex justify-content-start mt-3">
                                                <ul class="pagination-custom">
                                                    <?php
                                                    if ($current_page > 1) {
                                                        echo "<li><a href='?page=" . ($current_page - 1) . "'>« Anterior</a></li>";
                                                    }
                                                    for ($page = 1; $page <= $total_pages; $page++) {
                                                        if ($page == $current_page) {
                                                            echo "<li class='active'><span>$page</span></li>";
                                                        } else {
                                                            echo "<li><a href='?page=$page'>$page</a></li>";
                                                        }
                                                    }
                                                    if ($current_page < $total_pages) {
                                                        echo "<li><a href='?page=" . ($current_page + 1) . "'>Próximo »</a></li>";
                                                    }
                                                    ?>
                                                </ul>
                                            </div>

                                            <?php
                                            mysqli_close($conn);
                                            ?>
                                            </tr>
                                    </tbody>

                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightpick@1.3.4/lightpick.min.js"></script>
    <script src="/assets/js/Date-Range-Picker.js?h=1d598b35ada76eb401b3897ae4b61ccb"></script>
    <script src="/assets/js/Animated-numbers-section.js?h=a0ec092b1194013aa3c8e220b0938a52"></script>
    <script src="/assets/js/Bootstrap-Image-Uploader.js?h=2218f85124ce4687cddacceb8e123cc9"></script>
    <script src="/assets/js/DateRangePicker.js?h=e84100887465fbb69726c415c180211a"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.2.0/zxcvbn.js"></script>
    <script src="/assets/js/Multi-Select-Dropdown-by-Jigar-Mistry.js?h=45421b0ed6bd109b4f00e752ae5bf3e5"></script>
    <script src="/assets/js/Password-Strenght-Checker---Ambrodu.js?h=f40a32e3d989fd0e00bf2f0567e52e27"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
</body>

</html>