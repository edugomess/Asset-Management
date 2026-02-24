<?php
include 'auth.php';
include 'conexao.php';
?>
<!DOCTYPE html>
<html>
<style>
    .btn-tamanho-fixo {
        width: 130px;
    }

    .btn-edit {
        width: 50px;
        padding: 5px 0;
    }

    .btn-maintenance-system {
        background-color: #f6953e;
        color: white;
    }

    .btn-medium {
        width: 100px;
    }

    .badge-success {
        background-color: #28a745 !important;
        color: #fff !important;
    }

    .badge-danger {
        background-color: #dc3545 !important;
        color: #fff !important;
    }

    .badge-warning {
        background-color: #f6c23e !important;
        color: #fff !important;
    }

    .clickable-row {
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .clickable-row:hover {
        background-color: rgba(0, 0, 0, 0.05) !important;
    }

    .btn-system {
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-system:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Ativos - Gestão</title>
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
    <style>
        .card.shadow {
            transition: all 0.3s ease-in-out;
            cursor: pointer;
        }

        .card.shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15) !important;
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
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-1 topbar static-top"
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
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link"
                                        aria-expanded="false" data-toggle="dropdown" href="#"><span
                                            class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span><img
                                            class="border rounded-circle img-profile"
                                            src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/avatar5.jpeg'; ?>"></a>
                                    <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in"><a
                                            class="dropdown-item" href="profile.php"><i
                                                class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a><a
                                            class="dropdown-item" href="configuracoes.php"><i
                                                class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a>
                                        <div class="dropdown-divider"></div><a class="dropdown-item" href="login.php"><i
                                                class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>

                <div class="container-fluid">
                    <div class="row">
                        <?php
                        // Buscar contagens de ativos
                        $res_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM ativos WHERE id_asset NOT IN (SELECT id_asset FROM manutencao WHERE status_manutencao = 'Em Manutenção')");
                        $total_ativos = mysqli_fetch_assoc($res_total)['total'];

                        $res_disp = mysqli_query($conn, "SELECT COUNT(*) as total FROM ativos WHERE (assigned_to IS NULL OR assigned_to = 0) AND id_asset NOT IN (SELECT id_asset FROM manutencao WHERE status_manutencao = 'Em Manutenção')");
                        $total_disp = mysqli_fetch_assoc($res_disp)['total'];

                        $res_uso = mysqli_query($conn, "SELECT COUNT(*) as total FROM ativos WHERE (assigned_to IS NOT NULL AND assigned_to != 0) AND id_asset NOT IN (SELECT id_asset FROM manutencao WHERE status_manutencao = 'Em Manutenção')");
                        $total_uso = mysqli_fetch_assoc($res_uso)['total'];

                        $res_manut_card = mysqli_query($conn, "SELECT COUNT(*) as total FROM manutencao WHERE status_manutencao = 'Em Manutenção'");
                        $total_manut = mysqli_fetch_assoc($res_manut_card)['total'];
                        ?>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-left-primary py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-primary font-weight-bold text-xs mb-1">
                                                <span>Total de Ativos</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_ativos; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-boxes fa-2x text-gray-300"></i></div>
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
                                                <span>Disponíveis</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_disp; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-left-info py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-info font-weight-bold text-xs mb-1"><span>Em
                                                    Uso</span></div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_uso; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-user-check fa-2x text-gray-300"></i>
                                        </div>
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
                                                <span>Manutenção</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_manut; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-tools fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-dark mb-4">
                        <?php echo (isset($_GET['status']) && $_GET['status'] === 'Manutencao') ? 'Ativos em Manutenção' : 'Ativos'; ?>
                    </h3>

                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row">
                                <?php if (!isset($_GET['status']) || $_GET['status'] !== 'Manutencao'): ?>
                                    <div class="col-md-6 col-xl-3 text-nowrap">
                                        <div id="dataTable_length" class="dataTables_length"><a
                                                class="btn btn-success btn-block active text-white pulse animated btn-user"
                                                role="button"
                                                style="background: rgb(44,64,74);border-radius: 10px;border-width: 0px;height: 50px;padding-top: 13px;"
                                                href="/cadastro_de_equipamentos.php">Cadastrar Novo</a></div>
                                    </div>
                                <?php endif; ?>
                                <div class="col-md-6 col-xl-9">
                                    <div class="text-md-right dataTables_filter">
                                        <form method="GET" action=""><label><input type="search" name="search"
                                                    class="form-control form-control-sm" placeholder="Buscar..."
                                                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"></label>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive table mt-2">
                                <?php
                                $results_per_page = 10;
                                $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                                $status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
                                $maintenance_join = "";

                                $where_clauses = [];
                                if (!empty($search)) {
                                    $where_clauses[] = "(a.modelo LIKE '%$search%' OR a.tag LIKE '%$search%' OR a.hostName LIKE '%$search%')";
                                }
                                if ($status_filter === 'Manutencao') {
                                    $maintenance_join = " JOIN manutencao m ON a.id_asset = m.id_asset ";
                                    $where_clauses[] = "m.status_manutencao = 'Em Manutenção'";
                                } else {
                                    // Se não estiver filtrando por manutenção, oculta os que estão em manutenção
                                    $where_clauses[] = "a.id_asset NOT IN (SELECT id_asset FROM manutencao WHERE status_manutencao = 'Em Manutenção')";
                                    if (!empty($status_filter)) {
                                        $where_clauses[] = "a.status LIKE '%$status_filter%'";
                                    }
                                }

                                $where_clause = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

                                $sql_count = "SELECT COUNT(*) AS total FROM ativos a $maintenance_join $where_clause";
                                $res_count = mysqli_query($conn, $sql_count);
                                $total_results = mysqli_fetch_assoc($res_count)['total'];
                                $total_pages = ceil($total_results / $results_per_page);
                                $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                                $start_from = ($current_page - 1) * $results_per_page;

                                $order_by = "a.id_asset DESC";
                                if ($status_filter === 'Manutencao') {
                                    $order_by = "m.id_manutencao DESC";
                                }

                                $sql = "SELECT a.*, u.nome AS user_nome, u.sobrenome AS user_sobrenome, u.usuarioAD AS user_usuarioAD, u.email AS user_email, u.centroDeCusto AS user_centroDeCusto, m_info.id_manutencao as em_manutencao, m_info.observacoes as manutencao_motivo 
                                        FROM ativos a 
                                        LEFT JOIN usuarios u ON a.assigned_to = u.id_usuarios 
                                        LEFT JOIN manutencao m_info ON a.id_asset = m_info.id_asset AND m_info.status_manutencao = 'Em Manutenção'
                                        $maintenance_join $where_clause 
                                        ORDER BY $order_by LIMIT $start_from, $results_per_page";
                                $result = mysqli_query($conn, $sql);
                                ?>

                                <table class="table my-0" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th>Categoria</th>
                                            <th>Fabricante</th>
                                            <th>Modelo</th>
                                            <th>Tag</th>
                                            <th>HostName</th>
                                            <th>Valor Atual</th>
                                            <th>MAC</th>
                                            <th>CC</th>
                                            <?php if ($status_filter !== 'Manutencao'): ?>
                                                <th>Usuário</th>
                                            <?php else: ?>
                                                <th>Motivo Manut.</th>
                                            <?php endif; ?>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $assigned_to = $row['assigned_to'];
                                                $valor_original = floatval($row['valor']);
                                                // Lógica simplificada de valor para o exemplo
                                                $valor_atual = $valor_original;
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['fabricante']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['modelo']); ?></td>
                                                    <td><a href="detalhes_do_equipamento.php?id=<?php echo $row['id_asset']; ?>"
                                                            class="font-weight-bold"
                                                            style="color: #2c404a;"><?php echo htmlspecialchars($row['tag']); ?></a>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['hostName']); ?></td>
                                                    <td>R$ <?php echo number_format($valor_atual, 2, ',', '.'); ?></td>
                                                    <td><?php echo htmlspecialchars($row['macAdress']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['centroDeCusto']); ?></td>

                                                    <?php if ($status_filter !== 'Manutencao'): ?>
                                                        <td>
                                                            <?php
                                                            if ($assigned_to && !empty($row['user_nome'])) {
                                                                echo "<a href='#' onclick='showUserModal(" . $assigned_to . ", \"" . addslashes($row['user_nome']) . "\", \"" . addslashes($row['user_sobrenome']) . "\", \"" . addslashes($row['user_usuarioAD']) . "\", \"" . addslashes($row['user_email']) . "\", \"" . addslashes($row['user_centroDeCusto']) . "\", " . $row['id_asset'] . ")'>" . htmlspecialchars($row['user_nome']) . "</a>";
                                                            } else {
                                                                echo "Não Atribuído";
                                                            }
                                                            ?>
                                                        </td>
                                                    <?php else: ?>
                                                        <td>
                                                            <?php
                                                            $motivo_completo = !empty($row['manutencao_motivo']) ? $row['manutencao_motivo'] : 'Sem observações registradas';
                                                            $motivo_resumo = (mb_strlen($motivo_completo) > 30) ? mb_substr($motivo_completo, 0, 27) . "..." : $motivo_completo;
                                                            $has_motivo = !empty($row['manutencao_motivo']);
                                                            ?>
                                                            <span data-toggle="tooltip" data-placement="top"
                                                                title="<?php echo htmlspecialchars($motivo_completo); ?>"
                                                                style="cursor: help; <?php echo $has_motivo ? 'border-bottom: 1px dashed #ccc;' : 'color: #ccc; font-style: italic;'; ?>">
                                                                <?php echo htmlspecialchars($motivo_resumo); ?>
                                                            </span>
                                                        </td>
                                                    <?php endif; ?>

                                                    <td>
                                                        <span
                                                            class="badge <?php echo ($row['status'] === 'Ativo') ? 'badge-success' : (($row['status'] === 'Manutencao') ? 'badge-warning' : 'badge-danger'); ?>">
                                                            <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if (!$row['em_manutencao']): ?>
                                                                <?php if ($assigned_to): ?>
                                                                    <button class='btn btn-dark btn-tamanho-fixo mr-2'
                                                                        onclick='unassignUser(<?php echo $row['id_asset']; ?>)'>Desatribuir
                                                                        <i class='fas fa-user-minus'></i></button>
                                                                <?php else: ?>
                                                                    <button class='btn btn-info btn-tamanho-fixo mr-2'
                                                                        onclick='openAssignModal(<?php echo $row['id_asset']; ?>)'>Atribuir
                                                                        <i class='fas fa-user-plus'></i></button>
                                                                <?php endif; ?>
                                                            <?php endif; ?>

                                                            <a class='btn btn-warning btn-edit mr-2'
                                                                href='editar_ativo.php?id=<?php echo $row['id_asset']; ?>'
                                                                title="Editar"><i class='fas fa-edit'></i></a>
                                                            <a class='btn btn-info btn-edit mr-2'
                                                                href='detalhes_do_equipamento.php?id=<?php echo $row['id_asset']; ?>'
                                                                title="Histórico"><i class='fas fa-history'></i></a>

                                                            <?php if ($row['em_manutencao']): ?>
                                                                <button class="btn btn-success btn-edit"
                                                                    onclick="releaseFromMaintenance(<?php echo $row['id_asset']; ?>)"
                                                                    title="Liberar"><i class="fas fa-check-circle"></i></button>
                                                            <?php else: ?>
                                                                <button class="btn btn-warning btn-edit"
                                                                    onclick="sendToMaintenance(<?php echo $row['id_asset']; ?>)"
                                                                    title="Manutenção"><i class="fas fa-tools"></i></button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='11'>Nenhum dado encontrado.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-start mt-3">
                                <ul class="pagination-custom">
                                    <?php
                                    $params = ($search ? "&search=$search" : "") . ($status_filter ? "&status=$status_filter" : "");
                                    if ($current_page > 1) {
                                        echo "<li><a href='?page=" . ($current_page - 1) . "$params'>« Anterior</a></li>";
                                    }
                                    for ($p = 1; $p <= $total_pages; $p++) {
                                        if ($p == $current_page) {
                                            echo "<li class='active'><span>$p</span></li>";
                                        } else {
                                            echo "<li><a href='?page=$p$params'>$p</a></li>";
                                        }
                                    }
                                    if ($current_page < $total_pages) {
                                        echo "<li><a href='?page=" . ($current_page + 1) . "$params'>Próximo »</a></li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="maintenanceModal" class="modal" tabindex="-1" role="dialog"
                style="display: none; background: rgba(0,0,0,0.5);">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>Iniciar Manutenção</h5><button type="button" class="close"
                                onclick="closeMaintenanceModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <label>Descreva os detalhes da manutenção:</label>
                            <textarea id="maintenanceDesc" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" onclick="closeMaintenanceModal()">Cancelar</button>
                            <button class="btn btn-primary" onclick="confirmMaintenance()">Confirmar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="assignModal" class="modal" tabindex="-1" role="dialog"
                style="display: none; background: rgba(0,0,0,0.5);">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>Atribuir Ativo</h5><button type="button" class="close"
                                onclick="closeAssignModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="text" id="userSearch" class="form-control" placeholder="Pesquisar usuário..."
                                oninput="searchUsers()">
                            <ul id="userList" class="list-group mt-2"></ul>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="bg-white sticky-footer">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>DEGB&nbsp;Copyright © 2015-2024</span>
                    </div>
                </div>
            </footer>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
        <script src="/assets/js/bs-init.js"></script>
        <script src="/assets/js/theme.js"></script>
        <script src="/assets/js/global_search.js"></script>
        <script>
            $(document).ready(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });

            let currentAssetId = null;

            function openAssignModal(id) {
                currentAssetId = id;
                document.getElementById('assignModal').style.display = 'block';
            }

            function closeAssignModal() {
                document.getElementById('assignModal').style.display = 'none';
            }

            function sendToMaintenance(id) {
                currentAssetId = id;
                document.getElementById('maintenanceModal').style.display = 'block';
            }

            function closeMaintenanceModal() {
                document.getElementById('maintenanceModal').style.display = 'none';
            }

            function confirmMaintenance() {
                const obs = document.getElementById('maintenanceDesc').value;
                if (!obs) return alert('Descrição obrigatória');
                fetch('save_maintenance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_asset: currentAssetId,
                        acao: 'iniciar',
                        observacoes: obs
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erro: ' + data.message);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Erro na comunicação com o servidor.');
                    });
            }

            function releaseFromMaintenance(id) {
                if (!confirm('Liberar ativo?')) return;
                fetch('save_maintenance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_asset: id,
                        acao: 'liberar'
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erro: ' + data.message);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Erro na comunicação com o servidor.');
                    });
            }

            function unassignUser(id) {
                if (!confirm('Desatribuir usuário?')) return;
                fetch('unassign_asset.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_asset: id
                    })
                }).then(() => location.reload());
            }

            function searchUsers() {
                const q = document.getElementById('userSearch').value;
                fetch(`search_users.php?query=${q}`)
                    .then(r => r.json())
                    .then(users => {
                        const list = document.getElementById('userList');
                        list.innerHTML = '';
                        users.forEach(u => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item list-group-item-action';
                            li.textContent = u.name;
                            li.onclick = () => assignUser(u.id);
                            list.appendChild(li);
                        });
                    });
            }

            function assignUser(uid) {
                fetch('assign_asset.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_asset: currentAssetId,
                        assigned_to: uid
                    })
                }).then(() => location.reload());
            }
        </script>
</body>

</html>