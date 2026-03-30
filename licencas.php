<?php
/**
 * GESTÃO DE LICENÇAS: licencas.php
 * Inventário de softwares, chaves de ativação e controle de vencimentos de assinaturas.
 */
include 'auth.php';    // Proteção de sessão
include 'conexao.php'; // Vínculo com banco

// SEGURANÇA: Apenas perfis 'Admin' ou 'Suporte' podem gerenciar o licenciamento corporativo
if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    header("Location: index.php");
    exit();
}
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
    <?php include 'pagination_style.php'; ?>

    <style>
        .clickable-row {
            cursor: pointer;
            transition: background 0.2s;
        }
        .clickable-row:hover {
            background-color: rgba(0,0,0,0.05) !important;
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
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include 'sidebar_brand.php'; ?>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <?php include 'topbar.php'; ?>
                <div class="container-fluid" style="padding-left: 23px; padding-right: 23px;">
                    <h3 class="text-dark mb-4"><?php echo __('Gestão de Licenças'); ?></h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-xl-2 text-nowrap">
                                    <div id="dataTable_length" class="dataTables_length" aria-controls="dataTable"><a
                                            class="btn-premium-cadastro pulse animated"
                                            role="button"
                                            href="/cadastro_de_licenca.php"><?php echo __('Nova Licença'); ?></a></div>
                                </div>
                                <div class="col-md-6 col-xl-10">
                                    <div class="text-md-right dataTables_filter" id="dataTable_filter">
                                        <form method="GET" action="" class="form-inline justify-content-end">
                                            <div class="form-group mr-2">
                                                <input type="search" name="search"
                                                    class="form-control form-control-sm premium-filter"
                                                    aria-controls="dataTable" placeholder="<?php echo __('Buscar software...'); ?>"
                                                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive table mt-2" id="dataTable" role="grid">
                                <table class="table my-0" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('Software'); ?></th>
                                            <th><?php echo __('Fabricante'); ?></th>
                                            <th><?php echo __('Tipo'); ?></th>
                                            <th><?php echo __('Chave / ID'); ?></th>
                                            <th><?php echo __('Uso (Total/Em Uso)'); ?></th>
                                            <th><?php echo __('Expiração'); ?></th>
                                            <th><?php echo __('Status'); ?></th>
                                            <th><?php echo __('Ações'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // CONFIGURAÇÃO DA LISTAGEM: Paginação e Filtros
                                        $results_per_page = 10;
                                        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                                        $where = !empty($search) ? "WHERE software LIKE '%$search%' OR fabricante LIKE '%$search%'" : "";

                                        // Busca o total de registros para calcular as páginas
                                        $res_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM licencas $where");
                                        $row_count = mysqli_fetch_assoc($res_count);
                                        $total_pages = ceil($row_count['total'] / $results_per_page);
                                        $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                                        $start = ($current_page - 1) * $results_per_page;

                                        // Consulta as licenças com limite de página
                                        $sql = "SELECT * FROM licencas $where ORDER BY id_licenca DESC LIMIT $start, $results_per_page";
                                        $result = mysqli_query($conn, $sql);

                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                // LÓGICA DE UTILIZAÇÃO: Calcula a barra de progresso (uso vs total)
                                                $uso_percent = ($row['quantidade_total'] > 0) ? ($row['quantidade_uso'] / $row['quantidade_total']) * 100 : 0;
                                                $prog_class = ($uso_percent > 90) ? 'bg-danger' : (($uso_percent > 70) ? 'bg-warning' : 'bg-success');

                                                // LÓGICA DE VALIDADE: Verifica se a licença já expirou
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
                                                <tr class="clickable-row" data-href="perfil_licenca.php?id=<?php echo $row['id_licenca']; ?>">
                                                    <td>
                                                        <a href="perfil_licenca.php?id=<?php echo $row['id_licenca']; ?>" class="font-weight-bold">
                                                            <?php echo htmlspecialchars($row['software']); ?>
                                                        </a>
                                                    </td>
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
                                                            class="status-badge <?php echo $status_badge; ?>"><?php echo __($row['status']); ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <!-- Botão de Atribuir ou Desatribuir (Dinâmico) -->
                                                            <?php if ($row['quantidade_uso'] >= $row['quantidade_total']): ?>
                                                                <button class="btn btn-dark-system btn-system btn-tamanho-fixo mr-2"
                                                                    onclick="openDetailsModal(<?php echo $row['id_licenca']; ?>, '<?php echo addslashes($row['software']); ?>')">
                                                                    <?php echo __('Desatribuir'); ?> <i class="fas fa-user-minus"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                <button class="btn btn-info-system btn-system btn-tamanho-fixo mr-2"
                                                                    onclick="openAssignModal(<?php echo $row['id_licenca']; ?>)">
                                                                    <?php echo __('Atribuir'); ?> <i class="fas fa-address-card"></i>
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
                                                                title="<?php echo __('Detalhes de Atribuição'); ?>">
                                                                <i class="fas fa-info-circle"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='8' class='text-center'>" . __('Nenhuma licença encontrada.') . "</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-start mt-3">
                                <ul class="pagination-custom">
                                    <?php
                                    if ($current_page > 1) {
                                        echo "<li><a href='?page=" . ($current_page - 1) . "&search=$search'>" . __('« Anterior') . "</a></li>";
                                    }
                                    for ($i = 1; $i <= $total_pages; $i++) {
                                        if ($i == $current_page) {
                                            echo "<li class='active'><span>$i</span></li>";
                                        } else {
                                            echo "<li><a href='?page=$i&search=$search'>$i</a></li>";
                                        }
                                    }
                                    if ($current_page < $total_pages) {
                                        echo "<li><a href='?page=" . ($current_page + 1) . "&search=$search'>" . __('Próximo »') . "</a></li>";
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
                    <div class="modal-content" style="border-radius: 15px; border: none; overflow: hidden;">
                        <div class="modal-header" style="background: #2c404a; color: white;">
                            <h5 class="modal-title"><?php echo __('Atribuir Licença ao Usuário'); ?></h5>
                            <button type="button" class="close" onclick="closeAssignModal()" style="color: white; opacity: 0.8;">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small"><?php echo __('Pesquise o usuário para vincular esta licença.'); ?></p>
                            <input type="text" id="userSearch" class="form-control" placeholder="<?php echo __('Pesquisar usuário...'); ?>"
                                oninput="searchUsers()">
                            <ul id="userList" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;"></ul>
                        </div>
                        <div class="modal-footer" style="background: #f8f9fc;">
                            <button type="button" class="btn btn-secondary" onclick="closeAssignModal()" style="border-radius: 10px; background: #6c757d; border: none;"><?php echo __('Fechar'); ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para detalhes da licença -->
            <div id="detailsModal" class="modal" tabindex="-1" role="dialog" style="display: none;">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content" style="border-radius: 15px; border: none; overflow: hidden;">
                        <div class="modal-header" style="background: #2c404a; color: white;">
                            <h5 class="modal-title"><?php echo __('Detalhes de Atribuição'); ?>: <span id="detailsSoftwareName"></span></h5>
                            <button type="button" class="close" onclick="closeDetailsModal()" style="color: white; opacity: 0.8;">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th><?php echo __('Usuário'); ?></th>
                                            <th><?php echo __('Email'); ?></th>
                                            <th><?php echo __('Data de Atribuição'); ?></th>
                                            <th class="text-right"><?php echo __('Ação'); ?></th>
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
                                <p class="text-muted"><?php echo __('Nenhuma atribuição encontrada para esta licença.'); ?></p>
                            </div>
                        </div>
                        <div class="modal-footer" style="background: #f8f9fc;">
                            <button type="button" class="btn btn-danger-system btn-system" style="border-radius: 10px;"
                                onclick="window.open('relatorio_atribuicoes_geral.php', '_blank')">
                                <i class="fas fa-file-pdf"></i> <?php echo __('Gerar Relatório PDF'); ?>
                            </button>
                            <button type="button" class="btn btn-secondary" style="border-radius: 10px; background: #6c757d; border: none;"
                                onclick="closeDetailsModal()"><?php echo __('Fechar'); ?></button>
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
                                userList.innerHTML = '<li class="list-group-item"><?php echo __('Nenhum usuário encontrado'); ?></li>';
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
                    if (confirm(`<?php echo __('Deseja atribuir esta licença para'); ?> ${userName}?`)) {
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
                                    alert('<?php echo __('Licença atribuída com sucesso!'); ?>');
                                    location.reload();
                                } else {
                                    alert('<?php echo __('Erro:'); ?> ' + data.message);
                                }
                            })
                            .catch(error => console.error('Erro ao atribuir:', error));
                    }
                }

                function openDetailsModal(id, softwareName) {
                    document.getElementById('detailsSoftwareName').innerText = softwareName;
                    const tbody = document.getElementById('assignmentTableBody');
                    const msg = document.getElementById('noAssignmentsMsg');
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center"><?php echo __('Carregando...'); ?></td></tr>';
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
                            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger"><?php echo __('Erro ao carregar dados.'); ?></td></tr>';
                        });
                }

                function closeDetailsModal() {
                    document.getElementById('detailsModal').style.display = 'none';
                }

                function removeAssignment(id) {
                    if (confirm('<?php echo __('Deseja remover esta atribuição?'); ?>')) {
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
                                    alert('<?php echo __('Atribuição removida com sucesso!'); ?>');
                                    // Recarrega a lista do modal sem fechar
                                    const currentId = currentLicenseId;
                                    const currentName = document.getElementById('detailsSoftwareName').innerText;
                                    openDetailsModal(currentId, currentName);
                                } else {
                                    alert('<?php echo __('Erro:'); ?> ' + data.message);
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
                    if (confirm('<?php echo __('Atenção_Remover_Todos'); ?>')) {
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
                                alert('<?php echo __('Todas as atribuições foram removidas!'); ?>');
                                location.reload(); // Recarrega para atualizar os contadores na tabela principal
                            } else {
                                alert('<?php echo __('Erro:'); ?> ' + data.message);
                            }
                        })
                        .catch(error => console.error('Erro ao zerar:', error));
                    }
                }

                // Lógica para clique na linha (delegando para evitar navegação ao clicar em botões)
                $(document).ready(function() {
                    $('.clickable-row').on('click', function(e) {
                        if (!$(e.target).closest('button, a, .btn, .badge-action').length) {
                            const href = $(this).data('href');
                            if (href) {
                                window.location = href;
                            }
                        }
                    });
                });
            </script>
            </div>
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