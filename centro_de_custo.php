<?php
/**
 * GESTÃO DE CENTROS DE CUSTO: centro_de_custo.php
 * Visualização e administração das unidades de negócio e setores da organização.
 */
include_once 'auth.php';
include_once 'conexao.php';

// Restrição de acesso: Usuário comum não acessa centros de custo
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
    <title><?php echo __('Centro de Custo'); ?> - Asset Mgt</title>
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
    <?php include_once 'sidebar_style.php'; ?>
    <?php include_once 'pagination_style.php'; ?>
    <style>
        .clickable-row { cursor: pointer; transition: all 0.2s; }
        .clickable-row:hover {
            background-color: rgba(44, 64, 74, 0.05) !important;
        }
        
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
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include 'sidebar_brand.php'; ?>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <?php include_once 'topbar.php'; ?>
                <div class="container-fluid" style="padding-left: 23px; padding-right: 23px;">
                    <h3 class="text-dark mb-4"><i class="fas fa-wallet mr-2 text-success"></i><?php echo __('Centro de Custo'); ?></h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6 col-xl-2 text-nowrap">
                                    <div id="dataTable_length" class="dataTables_length" aria-controls="dataTable"></div>
                                    <a class="btn-premium-cadastro pulse animated"
                                        role="button"
                                        href="/cadastro_de_centro_de_custo.php"><?php echo __('Cadastrar Novo'); ?></a>
                                </div>
                            </div>

                            <div class="table-responsive table mt-2" id="dataTable" role="grid"
                                aria-describedby="dataTable_info">
                                <?php
                                // CONFIGURAÇÃO DA LISTAGEM: Paginação e Busca
                                $results_per_page = 10;

                                // LÓGICA DE PESQUISA: Filtra por nome do setor ou código do centro de custo
                                $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                                $where_clause = "";
                                if (!empty($search)) {
                                    $where_clause = "WHERE nomeSetor LIKE '%$search%' OR codigo LIKE '%$search%'";
                                }

                                // CÁLCULO DE PAGINAÇÃO: Define o total de páginas baseada na busca
                                $sql = "SELECT COUNT(*) AS total FROM centro_de_custo $where_clause";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                $total_results = $row['total'];
                                $total_pages = ceil($total_results / $results_per_page);

                                // Identifica a página atual (padrão 1)
                                $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                                $start_from = ($current_page - 1) * $results_per_page;

                                // CONSULTA DINÂMICA: Recupera os dados ordenados ALFABETICAMENTE
                                $sql = "SELECT * FROM centro_de_custo $where_clause ORDER BY nomeSetor ASC LIMIT $start_from, $results_per_page";
                                $result = mysqli_query($conn, $sql);
                                ?>

                                <table class="table my-0" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('Setor'); ?></th>
                                            <th><?php echo __('Código'); ?></th>
                                            <th><?php echo __('Ramal'); ?></th>
                                            <th><?php echo __('Unidade'); ?></th>
                                            <th><?php echo __('E-mail Gestor'); ?></th>
                                            <th><?php echo __('Gestor'); ?></th>
                                            <th><?php echo __('Ações'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                echo "<tr class='clickable-row' data-href='perfil_centro_de_custo.php?id=" . $row['id_centro_de_custo'] . "'>
                                           <td><strong><a href='perfil_centro_de_custo.php?id=" . $row['id_centro_de_custo'] . "'>" . htmlspecialchars($row['nomeSetor']) . "</a></strong></td>
                    <td>" . htmlspecialchars($row['codigo']) . "</td>
                    <td>" . htmlspecialchars($row['ramal']) . "</td>
                    <td>" . htmlspecialchars($row['unidade']) . "</td>
                    <td>" . htmlspecialchars($row['emailGestor']) . "</td>
                    <td>" . htmlspecialchars($row['gestor']) . "</td>
                    <td>
                        <a class='btn btn-warning btn-action' href='editar_centro_de_custo.php?id=" . $row['id_centro_de_custo'] . "' onclick='event.stopPropagation()' title='" . __('Editar') . "'><i class='fas fa-edit'></i></a>
                        <a class='btn btn-danger btn-action' href='apagar_centro_de_custo.php?id=" . $row['id_centro_de_custo'] . "' onclick='event.stopPropagation()' title='" . __('Excluir') . "'><i class='fas fa-trash'></i></a>
                    </td>
                </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='7'>" . __('Nenhum dado encontrado.') . "</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                    <thead>
                                        <tr>
                                            <th><?php echo __('Setor'); ?></th>
                                            <th><?php echo __('Código'); ?></th>
                                            <th><?php echo __('Ramal'); ?></th>
                                            <th><?php echo __('Unidade'); ?></th>
                                            <th><?php echo __('E-mail Gestor'); ?></th>
                                            <th><?php echo __('Gestor'); ?></th>
                                            <th><?php echo __('Ações'); ?></th>
                                        </tr>
                                    </thead>
                                </table>

                                <div class="d-flex justify-content-start mt-3">
                                    <ul class="pagination-custom">
                                        <?php
                                        $search_param = !empty($search) ? "&search=" . urlencode($search) : "";
                                        if ($current_page > 1) {
                                            echo "<li><a href='?page=" . ($current_page - 1) . "$search_param'>« " . __('Anterior') . "</a></li>";
                                        }

                                        for ($page = 1; $page <= $total_pages; $page++) {
                                            if ($page == $current_page) {
                                                echo "<li class='active'><span>$page</span></li>";
                                            } else {
                                                echo "<li><a href='?page=$page$search_param'>$page</a></li>";
                                            }
                                        }

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
            </div>
        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightpick@1.3.4/lightpick.min.css"></script>
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
    <script>
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
</body>

</html>