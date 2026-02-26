<?php
include 'auth.php';
include 'conexao.php';

// Verifica se o ID do ativo foi passado para transferir os dados
if (isset($_GET['id'])) {
    $ativoId = $_GET['id'];

    // Transfere o ativo para a tabela 'venda'
    $sql = "INSERT INTO venda (categoria, modelo, tag) 
            SELECT categoria, modelo, tag FROM ativos WHERE id = '$ativoId'";

    if (mysqli_query($conn, $sql)) {
        // Remove o ativo da tabela 'ativos'
        $deleteSql = "DELETE FROM ativos WHERE id = '$ativoId'";
        if (mysqli_query($conn, $deleteSql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Blank Page - Brand</title>
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
    <?php include 'sidebar_style.php'; ?>
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
                <a class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#">
                    <div class="sidebar-brand-text mx-3"><span>Asset Mgt</span></div>
                </a>
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
                                        src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/avatar1.jpeg'; ?>">
                                </a>
                                <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in">
                                    <a class="dropdown-item" href="profile.php"><i
                                            class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a>
                                    <a class="dropdown-item" href="configuracoes.php"><i
                                            class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="logout.php" class="dropdown-item"><i
                                            class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="container-fluid" style="padding-left: 23px; padding-right: 23px;">

                    <!-- Start: 4-column form row -->
                    <?php
                    include 'conexao.php';

                    // Definir o número de resultados por página
                    $results_per_page = 10;

                    // Verificar o número de resultados atribuídos no banco de dados
                    $sql = "SELECT COUNT(*) AS total FROM ativos WHERE assigned_to IS NOT NULL";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    $total_results = $row['total'];

                    // Determinar o número de páginas necessárias
                    $total_pages = ceil($total_results / $results_per_page);

                    // Determinar a página atual a partir da URL, se não definida, assume 1
                    $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

                    // Calcular o limite de registros para a consulta
                    $start_from = ($current_page - 1) * $results_per_page;

                    // Consultar os ativos atribuídos
                    $sql = "SELECT * FROM ativos WHERE assigned_to IS NOT NULL ORDER BY id_asset DESC LIMIT $start_from, $results_per_page";
                    $result = mysqli_query($conn, $sql);

                    // Buscar configurações de depreciação/doação globais
                    $dep_config_ini = [
                        'taxa_depreciacao' => 10.00,
                        'periodo_anos' => 1,
                        'periodo_meses' => 0,
                        'elegivel_doacao' => 0,
                        'tempo_doacao_anos' => 5,
                        'tempo_doacao_meses' => 0
                    ];
                    $result_dep_ini = mysqli_query($conn, "SELECT * FROM configuracoes_depreciacao LIMIT 1");
                    if ($result_dep_ini && mysqli_num_rows($result_dep_ini) > 0) {
                        $dep_config_ini = mysqli_fetch_assoc($result_dep_ini);
                    }
                    $doacao_global_ini = intval($dep_config_ini['elegivel_doacao']);
                    $tempo_min_doacao_meses_ini = (intval($dep_config_ini['tempo_doacao_anos']) * 12) + intval($dep_config_ini['tempo_doacao_meses']);

                    // Buscar elegibilidade por categoria
                    $cat_doacao_map_ini = [];
                    $result_cat_ini = mysqli_query($conn, "SELECT categoria, elegivel_doacao FROM categoria_doacao");
                    if ($result_cat_ini) {
                        while ($r = mysqli_fetch_assoc($result_cat_ini)) {
                            $cat_doacao_map_ini[$r['categoria']] = intval($r['elegivel_doacao']);
                        }
                    }
                    ?>
                    <h3 class="text-dark mb-4">Ativos Atribuídos</h3>
                    <div class="card shadow">
                        <div class="col-md-6 col-xl-3 text-nowrap">
                            <div id="dataTable_length" class="dataTables_length" aria-controls="dataTable"></div><a
                                class="btn btn-success btn-block active text-white pulse animated btn-user"
                                role="button"
                                style="background: rgb(44,64,74);border-radius: 10px;padding: 30px, 30px;border-width: 0px;height: 50px;margin-top: 23px;padding-top: 13px;"
                                href="/ativos_doados.php">Doações</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive mt-2">
                                <table class="table my-0" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th>Categoria</th>
                                            <th>Fabricante</th>
                                            <th>Modelo</th>
                                            <th>Tag</th>
                                            <th>HostName</th>
                                            <th>Valor</th>
                                            <th>MAC Address</th>
                                            <th>Usuário</th>
                                            <th>Centro de Custo</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>


                                        <?php
                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $assigned_to = $row['assigned_to'];
                                                $valor_original = floatval($row['valor']);

                                                // Calcular depreciação
                                                $data_ativacao = new DateTime($row['dataAtivacao']);
                                                $data_atual = new DateTime();
                                                $diff = $data_ativacao->diff($data_atual);

                                                $taxa_pct = floatval($dep_config_ini['taxa_depreciacao']);
                                                $periodo_total_meses = (intval($dep_config_ini['periodo_anos']) * 12) + intval($dep_config_ini['periodo_meses']);

                                                if ($periodo_total_meses > 0 && $valor_original > 0) {
                                                    $meses_ativos = ($diff->y * 12) + $diff->m;
                                                    $periodos_completos = floor($meses_ativos / $periodo_total_meses);
                                                    $depreciacao_total = min($valor_original, $valor_original * ($taxa_pct / 100) * $periodos_completos);
                                                    $valor_atual = max(0, $valor_original - $depreciacao_total);
                                                } else {
                                                    $valor_atual = $valor_original;
                                                }
                                                ?>
                                                <tr class="clickable-row"
                                                    onclick="window.location='detalhes_do_equipamento.php?id=<?php echo $row['id_asset']; ?>'">
                                                    <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['fabricante']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['modelo']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['tag']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['hostName']); ?></td>
                                                    <td>
                                                        <span class="font-weight-bold" style="color: #2c404a;">
                                                            R$ <?php echo number_format($valor_atual, 2, ',', '.'); ?>
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            (R$ <?php echo number_format($valor_original, 2, ',', '.'); ?>)
                                                        </small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['macAdress']); ?></td>
                                                    <td>
                                                        <?php
                                                        $sql_user = "SELECT nome FROM usuarios WHERE id_usuarios = '$assigned_to'";
                                                        $result_user = mysqli_query($conn, $sql_user);
                                                        if ($result_user && mysqli_num_rows($result_user) > 0) {
                                                            $user = mysqli_fetch_assoc($result_user);
                                                            echo htmlspecialchars($user['nome']);
                                                        } else {
                                                            echo "Não encontrado";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['centroDeCusto']); ?></td>
                                                    <td>
                                                        <?php
                                                        // Elegibilidade para doação baseada nas configurações
                                                        $data_ativacao = new DateTime($row['dataAtivacao']);
                                                        $data_atual = new DateTime();
                                                        $diff = $data_ativacao->diff($data_atual);
                                                        $meses_desde_cadastro_ini = ($diff->y * 12) + $diff->m;
                                                        $cat_do_ativo_ini = $row['categoria'];
                                                        $cat_elegivel_ini = isset($cat_doacao_map_ini[$cat_do_ativo_ini]) ? $cat_doacao_map_ini[$cat_do_ativo_ini] : 1;

                                                        $btn_style = 'style="width:150px; font-size:12px; border-radius:10px;"';

                                                        if (!$doacao_global_ini) {
                                                            echo '<button class="btn btn-secondary btn-sm" disabled title="A doação está desativada globalmente nas configurações." ' . $btn_style . ' >Desativada</button>';
                                                        } elseif (!$cat_elegivel_ini) {
                                                            echo '<button class="btn btn-secondary btn-sm" disabled title="A categoria &quot;' . htmlspecialchars($cat_do_ativo_ini) . '&quot; não está habilitada para doação." ' . $btn_style . ' >Cat. não elegível</button>';
                                                        } elseif ($meses_desde_cadastro_ini >= $tempo_min_doacao_meses_ini) {
                                                            echo '<button class="btn btn-success btn-sm" onclick="event.stopPropagation(); sellAsset(' . $row['id_asset'] . ')" ' . $btn_style . ' >Doar</button>';
                                                        } else {
                                                            $restante_ini = $tempo_min_doacao_meses_ini - $meses_desde_cadastro_ini;
                                                            $a_ini = floor($restante_ini / 12);
                                                            $m_ini = $restante_ini % 12;
                                                            $t_ini = '';
                                                            if ($a_ini > 0)
                                                                $t_ini .= $a_ini . ' ano(s)';
                                                            if ($a_ini > 0 && $m_ini > 0)
                                                                $t_ini .= ' e ';
                                                            if ($m_ini > 0)
                                                                $t_ini .= $m_ini . ' mês(es)';
                                                            if (empty($t_ini))
                                                                $t_ini = 'menos de 1 mês';
                                                            echo '<button class="btn btn-warning btn-sm" disabled title="Carência: ' . $t_ini . '" ' . $btn_style . ' >Bloqueado</button>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='10'>Nenhum ativo atribuído encontrado.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

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
                        </div>
                    </div>
                </div>

                <script>

                    // Função para doar o ativo (transferir para a tabela "venda" - agora doações)
                    function sellAsset(assetId) {
                        if (confirm('Tem certeza que deseja doar este ativo?')) {
                            fetch('doar_ativo.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ id_asset: assetId }) // Envia o id do ativo como JSON
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        alert('Ativo doado com sucesso!');
                                        location.reload(); // Recarrega a página para refletir as mudanças
                                    } else {
                                        alert('Ativo doado com sucesso!');
                                        location.reload();
                                    }
                                })
                                .catch(error => {
                                    alert('Erro ao processar doação!');
                                    location.reload();
                                });
                        }
                    }



                </script>

                <?php
                mysqli_close($conn);
                ?>



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