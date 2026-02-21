<?php
include 'auth.php';
include 'conexao.php';
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Licenças - Asset Mgt</title>
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
        .badge-success {
            background-color: #28a745 !important;
            color: #fff !important;
        }

        .badge-danger {
            background-color: #dc3545 !important;
            color: #fff !important;
        }

        .badge-warning {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }

        .btn-edit {
            width: 50px;
        }

        .progress {
            height: 8px;
            border-radius: 10px;
        }

        .pagination-custom {
            display: flex;
            gap: 6px;
            list-style: none;
            padding: 0;
            margin: 16px 0;
            flex-wrap: wrap;
        }

        .pagination-custom li a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1.5px solid rgba(44, 64, 74, 0.25);
            color: rgb(44, 64, 74);
            background: #fff;
        }

        .pagination-custom li a:hover {
            background: rgba(44, 64, 74, 0.08);
            border-color: rgb(44, 64, 74);
            color: rgb(44, 64, 74);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(44, 64, 74, 0.15);
        }

        .pagination-custom li.active a {
            background: rgb(44, 64, 74);
            color: #fff;
            border-color: rgb(44, 64, 74);
            box-shadow: 0 2px 8px rgba(44, 64, 74, 0.3);
        }

        /* System Standard Buttons */
        .btn-system {
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            height: 38px;
        }

        .btn-tamanho-fixo {
            width: 130px;
        }

        .btn-system:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            filter: brightness(1.1);
        }

        .btn-info-system {
            background-color: #36b9cc;
            color: white;
        }

        .btn-dark-system {
            background-color: #2c404a;
            color: white;
        }

        .btn-warning-system {
            background-color: #f6c23e;
            color: white;
        }

        .btn-secondary-system {
            background-color: #858796;
            color: white;
        }

        .btn-danger-system {
            background-color: #e74a3b;
            color: white;
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
                                            class="d-none d-lg-inline mr-2 text-gray-600 small">
                                            <?php echo htmlspecialchars($_SESSION['nome_usuario']); ?>
                                        </span><img class="border rounded-circle img-profile"
                                            src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/Captura%20de%20Tela%202021-08-04%20às%2012.25.13.png?h=fcfb924f0ac1ab5f595f029bf526e62d'; ?>"></a>
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
                    <h3 class="text-dark mb-4">Gestão de Licenças de Software</h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-xl-3 text-nowrap">
                                    <div id="dataTable_length" class="dataTables_length" aria-controls="dataTable"><a
                                            class="btn btn-success btn-block active text-white pulse animated btn-user"
                                            role="button"
                                            style="background: rgb(44,64,74);border-radius: 10px;border-width: 0px;height: 50px;margin-top: 0px;padding: 30px, 30px;margin-bottom: 0px;padding-top: 13px;"
                                            href="/cadastro_de_licenca.php">Nova Licença</a></div>
                                </div>
                                <div class="col-md-6 col-xl-9">
                                    <div class="text-md-right dataTables_filter" id="dataTable_filter">
                                        <form method="GET" action="">
                                            <label><input type="search" name="search"
                                                    class="form-control form-control-sm" aria-controls="dataTable"
                                                    placeholder="Buscar software..."
                                                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"></label>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive table mt-2" id="dataTable" role="grid">
                                <table class="table my-0" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th>Software</th>
                                            <th>Fabricante</th>
                                            <th>Tipo</th>
                                            <th>Chave / ID</th>
                                            <th>Uso (Total/Em Uso)</th>
                                            <th>Expiração</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $results_per_page = 10;
                                        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                                        $where = !empty($search) ? "WHERE software LIKE '%$search%' OR fabricante LIKE '%$search%'" : "";

                                        $res_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM licencas $where");
                                        $row_count = mysqli_fetch_assoc($res_count);
                                        $total_pages = ceil($row_count['total'] / $results_per_page);
                                        $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                                        $start = ($current_page - 1) * $results_per_page;

                                        $sql = "SELECT * FROM licencas $where ORDER BY id_licenca DESC LIMIT $start, $results_per_page";
                                        $result = mysqli_query($conn, $sql);

                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $uso_percent = ($row['quantidade_total'] > 0) ? ($row['quantidade_uso'] / $row['quantidade_total']) * 100 : 0;
                                                $prog_class = ($uso_percent > 90) ? 'bg-danger' : (($uso_percent > 70) ? 'bg-warning' : 'bg-success');

                                                $hoje = new DateTime();
                                                $exp = $row['data_expiracao'] ? new DateTime($row['data_expiracao']) : null;
                                                $status_badge = 'badge-success';
                                                if ($row['status'] == 'Expirada' || ($exp && $exp < $hoje)) {
                                                    $status_badge = 'badge-danger';
                                                    $row['status'] = 'Expirada';
                                                } elseif ($row['status'] == 'Cancelada') {
                                                    $status_badge = 'badge-secondary';
                                                }
                                                ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($row['software']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($row['fabricante']); ?></td>
                                                    <td><?php echo $row['tipo']; ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <code id="key-<?php echo $row['id_licenca']; ?>"
                                                                data-key="<?php echo htmlspecialchars($row['chave']); ?>"
                                                                class="mr-2">********</code>
                                                            <button class="btn btn-link btn-sm p-0 text-muted"
                                                                onclick="toggleKey(<?php echo $row['id_licenca']; ?>)">
                                                                <i class="fas fa-eye"
                                                                    id="eye-<?php echo $row['id_licenca']; ?>"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="small font-weight-bold">
                                                            <?php echo $row['quantidade_uso']; ?> /
                                                            <?php echo $row['quantidade_total']; ?>
                                                        </div>
                                                        <div class="progress">
                                                            <div class="progress-bar <?php echo $prog_class; ?>"
                                                                role="progressbar" style="width: <?php echo $uso_percent; ?>%"
                                                                aria-valuenow="<?php echo $uso_percent; ?>" aria-valuemin="0"
                                                                aria-valuemax="100"></div>
                                                        </div>
                                                    </td>
                                                    <td><?php echo $row['data_expiracao'] ? date('d/m/Y', strtotime($row['data_expiracao'])) : 'N/A'; ?>
                                                    </td>
                                                    <td><span
                                                            class="badge <?php echo $status_badge; ?>"><?php echo $row['status']; ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <!-- Botão de Atribuir ou Desatribuir (Dinâmico) -->
                                                            <?php if ($row['quantidade_uso'] >= $row['quantidade_total']): ?>
                                                                <button class="btn btn-dark-system btn-system btn-tamanho-fixo mr-2"
                                                                    onclick="openDetailsModal(<?php echo $row['id_licenca']; ?>, '<?php echo addslashes($row['software']); ?>')">
                                                                    Desatribuir <i class="fas fa-user-minus"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                <button class="btn btn-info-system btn-system btn-tamanho-fixo mr-2"
                                                                    onclick="openAssignModal(<?php echo $row['id_licenca']; ?>)">
                                                                    Atribuir <i class="fas fa-address-card"></i>
                                                                </button>
                                                            <?php endif; ?>

                                                            <!-- Botão de Editar -->
                                                            <a class="btn btn-warning-system btn-system btn-edit mr-2"
                                                                href="editar_licenca.php?id=<?php echo $row['id_licenca']; ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </a>

                                                            <!-- Botão de Detalhes -->
                                                            <button class="btn btn-secondary-system btn-system btn-edit mr-2"
                                                                onclick="openDetailsModal(<?php echo $row['id_licenca']; ?>, '<?php echo addslashes($row['software']); ?>')"
                                                                title="Detalhes de Atribuição">
                                                                <i class="fas fa-info-circle"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='8' class='text-center'>Nenhuma licença encontrada.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-start mt-3">
                                <ul class="pagination-custom">
                                    <?php
                                    if ($current_page > 1) {
                                        echo "<li><a href='?page=" . ($current_page - 1) . "&search=$search'>« Anterior</a></li>";
                                    }
                                    for ($i = 1; $i <= $total_pages; $i++) {
                                        if ($i == $current_page) {
                                            echo "<li class='active'><span>$i</span></li>";
                                        } else {
                                            echo "<li><a href='?page=$i&search=$search'>$i</a></li>";
                                        }
                                    }
                                    if ($current_page < $total_pages) {
                                        echo "<li><a href='?page=" . ($current_page + 1) . "&search=$search'>Próximo »</a></li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para atribuir licença -->
            <div id="assignModal" class="modal" tabindex="-1" role="dialog" style="display: none;">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Atribuir Licença ao Usuário</h5>
                            <button type="button" class="close" onclick="closeAssignModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small">Pesquise o usuário para vincular esta licença.</p>
                            <input type="text" id="userSearch" class="form-control" placeholder="Pesquisar usuário..."
                                oninput="searchUsers()">
                            <ul id="userList" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;"></ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeAssignModal()">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para detalhes da licença -->
            <div id="detailsModal" class="modal" tabindex="-1" role="dialog" style="display: none;">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Atribuições: <span id="detailsSoftwareName"></span></h5>
                            <button type="button" class="close" onclick="closeDetailsModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Usuário</th>
                                            <th>Email</th>
                                            <th>Data de Atribuição</th>
                                            <th class="text-right">Ação</th>
                                        </tr>
                                    </thead>
                                    <ul id="assignmentList" class="list-unstyled mb-0">
                                        <!-- Carregado via JS -->
                                    </ul>
                                    <tbody id="assignmentTableBody">
                                        <!-- Carregado via JS -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="noAssignmentsMsg" class="text-center p-3 d-none">
                                <p class="text-muted">Nenhuma atribuição encontrada para esta licença.</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger-system btn-system"
                                onclick="window.open('relatorio_atribuicoes_geral.php', '_blank')">
                                <i class="fas fa-file-pdf"></i> Gerar Relatório PDF
                            </button>
                            <button type="button" class="btn btn-secondary"
                                onclick="closeDetailsModal()">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function toggleKey(id) {
                    const keyElement = document.getElementById('key-' + id);
                    const eyeIcon = document.getElementById('eye-' + id);
                    const realKey = keyElement.getAttribute('data-key');

                    if (keyElement.innerText === '********') {
                        keyElement.innerText = realKey;
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                    } else {
                        keyElement.innerText = '********';
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                    }
                }

                let currentLicenseId = null;

                function openAssignModal(id) {
                    currentLicenseId = id;
                    document.getElementById('assignModal').style.display = 'block';
                }

                function closeAssignModal() {
                    document.getElementById('assignModal').style.display = 'none';
                    document.getElementById('userList').innerHTML = '';
                    document.getElementById('userSearch').value = '';
                }

                function searchUsers() {
                    const query = document.getElementById('userSearch').value;
                    if (query.length < 2) {
                        document.getElementById('userList').innerHTML = '';
                        return;
                    }

                    fetch(`search_users.php?query=${query}`)
                        .then(response => response.json())
                        .then(users => {
                            const userList = document.getElementById('userList');
                            userList.innerHTML = '';

                            if (users.length === 0) {
                                userList.innerHTML = '<li class="list-group-item">Nenhum usuário encontrado</li>';
                            } else {
                                users.forEach(user => {
                                    const li = document.createElement('li');
                                    li.className = 'list-group-item list-group-item-action cursor-pointer';
                                    li.style.cursor = 'pointer';
                                    li.textContent = user.name;
                                    li.onclick = () => assignLicense(user.id, user.name);
                                    userList.appendChild(li);
                                });
                            }
                        })
                        .catch(error => console.error('Erro na busca:', error));
                }

                function assignLicense(userId, userName) {
                    if (confirm(`Deseja atribuir esta licença para ${userName}?`)) {
                        fetch('assign_license.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id_licenca: currentLicenseId,
                                id_usuario: userId
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Licença atribuída com sucesso!');
                                    location.reload();
                                } else {
                                    alert('Erro: ' + data.message);
                                }
                            })
                            .catch(error => console.error('Erro ao atribuir:', error));
                    }
                }

                function openDetailsModal(id, softwareName) {
                    document.getElementById('detailsSoftwareName').innerText = softwareName;
                    const tbody = document.getElementById('assignmentTableBody');
                    const msg = document.getElementById('noAssignmentsMsg');
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center">Carregando...</td></tr>';
                    msg.classList.add('d-none');

                    document.getElementById('detailsModal').style.display = 'block';

                    fetch(`get_license_assignments.php?id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            tbody.innerHTML = '';
                            if (data.success && data.atribuicoes.length > 0) {
                                data.atribuicoes.forEach(attr => {
                                    const tr = document.createElement('tr');
                                    tr.innerHTML = `
                                        <td><strong>${attr.usuario}</strong></td>
                                        <td><small class="text-muted">${attr.email}</small></td>
                                        <td>${attr.data}</td>
                                        <td class="text-right">
                                            <button class="btn btn-danger btn-sm" onclick="removeAssignment(${attr.id})">
                                                <i class="fas fa-user-minus"></i>
                                            </button>
                                        </td>
                                    `;
                                    tbody.appendChild(tr);
                                });
                            } else {
                                msg.classList.remove('d-none');
                                tbody.innerHTML = '';
                            }
                        })
                        .catch(error => {
                            console.error('Erro ao buscar detalhes:', error);
                            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Erro ao carregar dados.</td></tr>';
                        });
                }

                function closeDetailsModal() {
                    document.getElementById('detailsModal').style.display = 'none';
                }

                function removeAssignment(id) {
                    if (confirm('Deseja remover esta atribuição?')) {
                        fetch('remove_license_assignment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id_atribuicao: id
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Atribuição removida com sucesso!');
                                    // Recarrega a lista do modal sem fechar
                                    const currentId = currentLicenseId;
                                    const currentName = document.getElementById('detailsSoftwareName').innerText;
                                    openDetailsModal(currentId, currentName);
                                } else {
                                    alert('Erro: ' + data.message);
                                }
                            })
                            .catch(error => console.error('Erro ao remover:', error));
                    }
                }

                function gerarRelatorioLicenca() {
                    const softwareName = document.getElementById('detailsSoftwareName').innerText;
                    // Abre o gerador de relatório oficial do sistema passando o ID da licença
                    window.open(`relatorio_atribuicoes_licenca.php?id=${currentLicenseId}`, '_blank');
                }

                function zerarAtribuicoes() {
                    if (confirm('ATENÇÃO: Isso irá remover TODOS os usuários vinculados a esta licença. Deseja continuar?')) {
                        fetch('clear_license_assignments.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id_licenca: currentLicenseId
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Todas as atribuições foram removidas!');
                                    location.reload(); // Recarrega para atualizar os contadores na tabela principal
                                } else {
                                    alert('Erro: ' + data.message);
                                }
                            })
                            .catch(error => console.error('Erro ao zerar:', error));
                    }
                }
            </script>
            <footer class="sticky-footer" style="background: transparent; padding: 0;">
                <section class="text-center footer" style="padding: 10px; margin-top: 70px;">
                    <p style="margin-bottom: 0px; font-size: 15px;">DEGB&nbsp;Copyright © 2015-2024<br></p>
                </section>
            </footer>
        </div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="/assets/js/global_search.js"></script>
</body>

</html>