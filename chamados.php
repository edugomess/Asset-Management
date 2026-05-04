<?php
/**
 * GESTÃO DE CHAMADOS: chamados.php
 * Interface central de suporte para usuários e técnicos, com lógica de SLA em tempo real.
 */
include_once 'auth.php';    // Autenticação de sessão
include_once 'conexao.php'; // Banco de dados
date_default_timezone_set('America/Sao_Paulo'); // Alinha o tempo com o fuso local

// === CONFIGURAÇÃO DE PAGINAÇÃO ===
$results_per_page = 10; // Define o número de registros por página

// === LÓGICA DE FILTRAGEM: Busca o status selecionado ou define 'aberto' como padrão ===
$filtro_status = isset($_GET['filtro_status']) ? $_GET['filtro_status'] : 'aberto';
$where_clause = "";

switch ($filtro_status) {
    case 'aberto':
        $where_clause = "WHERE c.status = 'Aberto'";
        break;
    case 'em_andamento':
        $where_clause = "WHERE c.status IN ('Em Andamento', 'Em Atendimento')";
        break;
    case 'pendente':
        $where_clause = "WHERE c.status = 'Pendente'";
        break;
    case 'finalizados':
        $where_clause = "WHERE c.status IN ('Resolvido', 'Fechado', 'Cancelado')";
        break;
    case 'todos':
        $where_clause = "";
        break;
    default:
        $where_clause = "WHERE c.status = 'Aberto'";
        $filtro_status = 'aberto';
        break;
}

// === BUSCA GLOBAL: Integração com filtros de texto (Título, ID, Tag, Solicitante ou Técnico) ===
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
if (!empty($search)) {
    $search_condition = "(c.titulo LIKE '%$search%' OR c.id LIKE '%$search%' OR c.service_tag LIKE '%$search%' OR u.nome LIKE '%$search%' OR u.sobrenome LIKE '%$search%' OR r.nome LIKE '%$search%' OR r.sobrenome LIKE '%$search%')";
    if (empty($where_clause)) {
        $where_clause = "WHERE $search_condition";
    } else {
        $where_clause .= " AND $search_condition";
    }
}

// === RESTRIÇÃO DE USUÁRIO: Garante que usuários comuns vejam seus próprios tickets E os que precisam de sua aprovação ===
if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    $user_id = $_SESSION['id_usuarios'];
    if (empty($where_clause)) {
        $where_clause = "WHERE (c.usuario_id = $user_id OR c.id_gestor_aprovador = $user_id)";
    } else {
        $where_clause .= " AND (c.usuario_id = $user_id OR c.id_gestor_aprovador = $user_id)";
    }
}

$sql_count = "SELECT COUNT(*) AS total FROM chamados c 
              LEFT JOIN usuarios u ON c.usuario_id = u.id_usuarios 
              LEFT JOIN usuarios r ON c.responsavel_id = r.id_usuarios 
              $where_clause";
$result_count = mysqli_query($conn, $sql_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_results = $row_count['total'];
$total_pages = ceil($total_results / $results_per_page);
$pg_atual = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($pg_atual < 1) $pg_atual = 1;
$start_from = ($pg_atual - 1) * $results_per_page;

$sql = "SELECT c.*, u.nome, u.sobrenome, r.nome AS resp_nome, r.sobrenome AS resp_sobrenome 
        FROM chamados c 
        LEFT JOIN usuarios u ON c.usuario_id = u.id_usuarios 
        LEFT JOIN usuarios r ON c.responsavel_id = r.id_usuarios
        $where_clause
        ORDER BY c.data_abertura DESC
        LIMIT $start_from, $results_per_page";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Chamados'); ?> - Asset Mgt</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=f3d9abe8d5aa7831c01bfaa2a1563712">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=41e93b37bc495fd67938799bb3a6adaf">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="/assets/css/Simple-footer-by-krissy.css?h=73316da5ae5ad6b51632cd2e5413f263">
    <?php include_once 'sidebar_style.php'; ?>
    <?php include_once 'pagination_style.php'; ?>
    <link rel="stylesheet" href="/assets/css/help_system.css">
    <style>
        .btn-tamanho-fixo {
            width: 130px;
        }

        .btn-edit {
            width: 50px;
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
            background-color: #ffc107 !important;
            color: #212529 !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(44, 64, 74, 0.05);
            transition: background-color 0.2s;
        }
    </style>
    <style>
        .clickable-row {
            cursor: pointer;
            transition: all 0.2s;
        }

        .clickable-row:hover {
            background-color: rgba(0, 0, 0, 0.05) !important;
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
                    <h3 class="text-dark mb-4">
                        <i class="fas fa-ticket-alt mr-2 text-primary"></i>
                        <?php echo __('Chamados'); ?>
                        <div class="help-indicator animate__animated animate__fadeIn" data-toggle="modal" data-target="#helpModal" title="<?php echo __('Guia da Tela'); ?>">
                            <i class="fas fa-question"></i>
                        </div>
                    </h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-xl-2 text-nowrap">
                                    <div class=""><a
                                            class="btn-premium-cadastro pulse animated"
                                            role="button"
                                            href="/cadastro_de_chamados.php"><?php echo __('Novo Chamado'); ?></a></div>
                                </div>
                                <div class="col-md-6 col-xl-6">
                                    <form method="GET" class="form-inline" style="margin-top: 0px;">
                                        <label for="filtro_status" class="mr-2 font-weight-bold"><?php echo __('Filtrar por:'); ?></label>
                                        <select name="filtro_status" id="filtro_status" class="form-control mr-2"
                                            onchange="this.form.submit()">
                                            <option value="aberto" <?php echo ($filtro_status == 'aberto') ? 'selected' : ''; ?>><?php echo __('Abertos'); ?></option>
                                            <option value="em_andamento" <?php echo ($filtro_status == 'em_andamento') ? 'selected' : ''; ?>><?php echo __('Em Andamento'); ?></option>
                                            <option value="pendente" <?php echo ($filtro_status == 'pendente') ? 'selected' : ''; ?>><?php echo __('Pendentes'); ?></option>
                                            <option value="finalizados" <?php echo ($filtro_status == 'finalizados') ? 'selected' : ''; ?>><?php echo __('Finalizados'); ?></option>
                                            <option value="todos" <?php echo ($filtro_status == 'todos') ? 'selected' : ''; ?>><?php echo __('Todos'); ?></option>
                                        </select>
                                        <input type="search" name="search" class="form-control form-control-sm"
                                            placeholder="<?php echo __('Buscar...'); ?>"
                                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                    </form>
                                </div>
                            </div>
                            <div class="table-responsive table mt-2" id="chamadosWrapper" role="grid"
                                aria-describedby="dataTable_info">
                                <table class="table table-hover my-0" id="chamadosTable">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('ID'); ?></th>
                                            <th><?php echo __('Título'); ?></th>
                                            <th><?php echo __('Categoria'); ?></th>
                                            <th><?php echo __('Prioridade'); ?></th>
                                            <th><?php echo __('Data Abertura'); ?></th>
                                            <th><?php echo __('Solicitante'); ?></th>
                                            <th><?php echo __('Responsável'); ?></th>
                                            <th><?php echo __('Status'); ?></th>
                                            <th><?php echo __('SLA STATUS'); ?></th>
                                            <th class="text-center"><?php echo __('Ações'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (mysqli_num_rows($result) > 0) {
                                            // 1. GESTÃO DE SLA: Busca metas configuradas no painel administrativo
                                            $sla_configs = [];
                                            $res_config = mysqli_query($conn, "SELECT categoria, tempo_sla_minutos FROM configuracoes_sla");

                                            if ($res_config) {
                                                while ($row_config = mysqli_fetch_assoc($res_config)) {
                                                    $sla_configs[$row_config['categoria']] = $row_config['tempo_sla_minutos'];
                                                }
                                            }

                                            // Fallback: Tempos padrão em minutos caso não haja definição no banco
                                            $defaults = ['Incidente' => 360, 'Mudança' => 1440, 'Requisição' => 2880];

                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $categoria = $row['categoria'];
                                                $prioridade = isset($row['prioridade']) ? $row['prioridade'] : 'Média';

                                                // 2. CÁLCULO DE TEMPO BASE: Ajusta SLA conforme prioridade (Alta reduz o tempo drasticamente)
                                                $cat_sla = $sla_configs[$categoria] ?? ($defaults[$categoria] ?? 360);

                                                if ($prioridade === 'P1') {
                                                    $sla_total_minutos = round($cat_sla / 6);
                                                } elseif ($prioridade === 'P2' || $prioridade === 'Alta') {
                                                    $sla_total_minutos = round($cat_sla / 3);
                                                } elseif ($prioridade === 'P3' || $prioridade === 'Média') {
                                                    $sla_total_minutos = round(($cat_sla * 2) / 3);
                                                } else {
                                                    $sla_total_minutos = $cat_sla;
                                                }

                                                $data_abertura = new DateTime($row['data_abertura']);
                                                $agora = new DateTime();
                                                $intervalo = $data_abertura->diff($agora);

                                                // 3. LÓGICA DE CONGELAMENTO: Desconta tempo em que o chamado ficou 'Pendente'
                                                $minutos_decorridos = ($intervalo->days * 24 * 60) + ($intervalo->h * 60) + $intervalo->i;

                                                $tempo_congelado = intval($row['tempo_congelado_minutos'] ?? 0);
                                                $minutos_decorridos -= $tempo_congelado;

                                                if ($row['status'] === 'Pendente' && !empty($row['data_ultimo_congelamento'])) {
                                                    $data_congelamento = new DateTime($row['data_ultimo_congelamento']);
                                                    $intervalo_congelamento = $data_congelamento->diff($agora);
                                                    $minutos_congelamento_atual = ($intervalo_congelamento->days * 24 * 60) + ($intervalo_congelamento->h * 60) + $intervalo_congelamento->i;
                                                    $minutos_decorridos -= $minutos_congelamento_atual;
                                                }

                                                $minutos_decorridos = max(0, $minutos_decorridos);

                                                // 3. Cálculo da porcentagem do SLA
                                                if ($sla_total_minutos > 0) {
                                                    $sla_percentage = min(100, ($minutos_decorridos / $sla_total_minutos) * 100);
                                                } else {
                                                    $sla_percentage = 100; // Se SLA for 0, considera estourado se já passou algum tempo
                                                }

                                                $sla_status_text = '';
                                                $progress_bar_class = '';
                                                $sla_status_html = '';
                                                $tempo_formatado = ''; // Inicializa para evitar warnings

                                                if ($row['status'] == 'Aberto' || $row['status'] == 'Em Andamento' || $row['status'] == 'Em Atendimento' || $row['status'] == 'Pendente') {
                                                    if ($row['status'] == 'Pendente') {
                                                        $sla_status_text = __('Pendente');
                                                        $progress_bar_class = 'bg-secondary';
                                                        $animation_class = '';
                                                    } else {
                                                        $animation_class = 'progress-bar-striped progress-bar-animated';
                                                        if ($minutos_decorridos >= $sla_total_minutos) {
                                                            $sla_status_text = __('Vencido');
                                                            $progress_bar_class = 'bg-danger';
                                                        } elseif ($minutos_decorridos >= ($sla_total_minutos * 0.8)) { // > 80%
                                                            $sla_status_text = __('Atenção');
                                                            $progress_bar_class = 'bg-warning';
                                                        } else {
                                                            $sla_status_text = __('No Prazo');
                                                            $progress_bar_class = 'bg-success';
                                                        }
                                                    }

                                                    // Formatação do tempo decorrido para exibição
                                                    if ($minutos_decorridos < 60) {
                                                        $tempo_formatado = round($minutos_decorridos) . 'm';
                                                    } elseif ($minutos_decorridos < 1440) {
                                                        $horas = floor($minutos_decorridos / 60);
                                                        $minutos = $minutos_decorridos % 60;
                                                        $tempo_formatado = "{$horas}h {$minutos}m";
                                                    } else {
                                                        $dias = floor($minutos_decorridos / 1440);
                                                        $horas = floor(($minutos_decorridos % 1440) / 60);
                                                        $tempo_formatado = "{$dias}d {$horas}h";
                                                    }

                                                    $deadline_timestamp = $data_abertura->getTimestamp() + ($sla_total_minutos * 60) + ($tempo_congelado * 60);

                                                    $sla_status_html = '
                <div class="d-flex flex-column sla-container" 
                     data-deadline="' . $deadline_timestamp . '" 
                     data-sla-total="' . ($sla_total_minutos * 60) . '" 
                     data-status="' . $row['status'] . '">
                    <span class="small font-weight-bold mb-1 sla-timer-text" style="font-size: 0.75rem;">' . $sla_status_text . ' (' . $tempo_formatado . ')</span>
                    <div class="progress" style="height: 15px; min-width: 130px;">
                        <div class="progress-bar sla-progress-bar ' . $progress_bar_class . ' ' . $animation_class . '" role="progressbar" style="width: ' . $sla_percentage . '%" aria-valuenow="' . $sla_percentage . '" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>';
                                                } elseif ($row['status'] == 'Resolvido' || $row['status'] == 'Fechado') {
                                                    $sla_status_html = '<span class="status-badge badge-success font-weight-bold">' . __('Concluído') . '</span>';
                                                } elseif ($row['status'] == 'Cancelado') {
                                                    $sla_status_html = '<span class="status-badge badge-danger font-weight-bold">' . __('Cancelado') . '</span>';
                                                } else {
                                                    $sla_status_html = '<span class="status-badge badge-secondary font-weight-bold">-</span>';
                                                }

                                                // Definição das cores dos Badges de Status
                                                $status_class = 'badge-secondary';
                                                switch ($row['status']) {
                                                    case 'Aberto':
                                                        $status_class = 'badge-primary';
                                                        break;
                                                    case 'Em Andamento':
                                                    case 'Em Atendimento':
                                                        $status_class = 'badge-info';
                                                        break;
                                                    case 'Pendente':
                                                        $status_class = 'badge-warning';
                                                        break;
                                                    case 'Resolvido':
                                                        $status_class = 'badge-success';
                                                        break;
                                                    case 'Cancelado':
                                                        $status_class = 'badge-danger';
                                                        break;
                                                    case 'Fechado':
                                                        $status_class = 'badge-dark';
                                                        break;
                                                }

                                                $solicitante = $row['nome'] ? $row['nome'] . ' ' . $row['sobrenome'] : __('Não identificado');
                                                $responsavel = $row['resp_nome'] ? $row['resp_nome'] . ' ' . $row['resp_sobrenome'] : __('Não Atribuído');
                                                $responsavel_class = $row['resp_nome'] ? 'badge-dark' : 'badge-secondary';

                                                $prioridade_class = 'badge-secondary';
                                                switch ($prioridade) {
                                                    case 'P1':
                                                        $prioridade_class = 'badge-danger border border-white';
                                                        break;
                                                    case 'P2':
                                                    case 'Alta':
                                                        $prioridade_class = 'badge-danger';
                                                        break;
                                                    case 'P3':
                                                    case 'Média':
                                                        $prioridade_class = 'badge-warning text-dark';
                                                        break;
                                                    case 'P4':
                                                    case 'Baixa':
                                                        $prioridade_class = 'badge-info';
                                                        break;
                                                }

                                                $pode_cancelar = ($row['usuario_id'] == $_SESSION['id_usuarios'] && in_array($row['status'], ['Aberto', 'Pendente']));
                                                $btn_cancelar = $pode_cancelar ? "
                                                    <button class='btn btn-sm btn-outline-danger border-0' 
                                                            onclick='event.stopPropagation(); confirmarCancelamento(" . $row['id'] . ")' 
                                                            title='" . __('Cancelar Chamado') . "'
                                                            style='border-radius: 8px;'>
                                                        <i class='fas fa-times-circle'></i>
                                                    </button>" : "";

                                                $categoria_display = __($row['categoria']);
                                                if (!empty($row['tipo_servico'])) {
                                                    $categoria_display .= " <small class='text-muted'>(" . __($row['tipo_servico']) . ")</small>";
                                                }

                                                echo "<tr class='clickable-row' data-href='editar_chamado.php?id=" . $row['id'] . "'>
                <td class='font-weight-bold text-dark'>" . htmlspecialchars($row['id']) . "</td>
                <td><a href='editar_chamado.php?id=" . $row['id'] . "' class='font-weight-bold text-dark' onclick='event.stopPropagation()'>" . htmlspecialchars($row['titulo']) . "</a></td>
                <td class='font-weight-bold text-dark'>" . $categoria_display . "</td>
                <td><span class='status-badge " . $prioridade_class . " font-weight-bold'>" . __($prioridade) . "</span></td>
                <td class='font-weight-bold text-dark'>" . __date($row['data_abertura'], true) . "</td>
                <td class='font-weight-bold text-dark'>" . htmlspecialchars($solicitante) . "</td>
                <td><span class='status-badge " . $responsavel_class . " font-weight-bold'>" . htmlspecialchars($responsavel) . "</span></td>
                <td><span class='status-badge " . $status_class . " font-weight-bold'>" . __($row['status']) . "</span></td>
                <td style='vertical-align: middle;'>" . $sla_status_html . "</td>
                <td class='text-center' style='vertical-align: middle;'>" . $btn_cancelar . "</td>
            </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='10' class='text-center'>" . __('Nenhum chamado encontrado.') . "</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-start mt-4 mb-4">
                                <ul class="pagination-custom">
                                    <?php
                                    $p_params = "filtro_status=" . urlencode($filtro_status);
                                    if (!empty($search)) {
                                        $p_params .= "&search=" . urlencode($search);
                                    }

                                    $total_p = (int)$total_pages;

                                    // Botão Anterior
                                    if ($pg_atual > 1) {
                                        echo "<li><a href='?page=" . ($pg_atual - 1) . "&" . $p_params . "'>« " . __('Anterior') . "</a></li>";
                                    } else {
                                        echo "<li class='disabled'><span>« " . __('Anterior') . "</span></li>";
                                    }

                                    // Janela de páginas (exibir até 6 páginas como no exemplo)
                                    $max_links = 6;
                                    $start_p = max(1, $pg_atual - 2);
                                    $end_p = min($total_p, $start_p + $max_links - 1);
                                    if ($end_p - $start_p < $max_links - 1) {
                                        $start_p = max(1, $end_p - $max_links + 1);
                                    }

                                    for ($p = $start_p; $p <= $end_p; $p++) {
                                        if ($p == $pg_atual) {
                                            echo "<li class='active'><span>$p</span></li>";
                                        } else {
                                            echo "<li><a href='?page=$p&" . $p_params . "'>$p</a></li>";
                                        }
                                    }

                                    // Botão Próximo
                                    if ($pg_atual < $total_p) {
                                        echo "<li><a href='?page=" . ($pg_atual + 1) . "&" . $p_params . "'>" . __('Próxima') . " »</a></li>";
                                    } else {
                                        echo "<li class='disabled'><span>" . __('Próxima') . " »</span></li>";
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

        <!-- Help Modal -->
        <div class="modal fade glass-help-modal" id="helpModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content border-0">
                    <div class="modal-header border-0">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-info-circle mr-2"></i><?php echo __('Guia de Gestão de Chamados'); ?>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="help-item">
                                    <h6><i class="fas fa-filter mr-2"></i><?php echo __('Filtros Rápidos'); ?></h6>
                                    <p class="small text-muted mb-0"><?php echo __('Use o seletor de status para alternar entre chamados Abertos, Pendentes ou Finalizados rapidamente.'); ?></p>
                                </div>
                                <div class="help-item">
                                    <h6><i class="fas fa-stopwatch mr-2"></i><?php echo __('Controle de SLA'); ?></h6>
                                    <p class="small text-muted mb-0"><?php echo __('A barra de progresso indica o tempo restante. Verde (No Prazo), Amarelo (Atenção) e Vermelho (Vencido).'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="help-item">
                                    <h6><i class="fas fa-search mr-2"></i><?php echo __('Busca Inteligente'); ?></h6>
                                    <p class="small text-muted mb-0"><?php echo __('Pesquise por ID, Título, Service Tag ou Nomes para encontrar qualquer ticket instantaneamente.'); ?></p>
                                </div>
                                <div class="help-item">
                                    <h6><i class="fas fa-mouse-pointer mr-2"></i><?php echo __('Acesso Rápido'); ?></h6>
                                    <p class="small text-muted mb-0"><?php echo __('Clique em qualquer linha da tabela para visualizar e editar os detalhes completos do chamado.'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <a href="documentacao.php" class="help-footer-btn text-decoration-none">
                            <i class="fas fa-book mr-2"></i><?php echo __('Ver Documentação Completa'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
    
    <script>
        $(document).ready(function() {
            // Lógica para clique na linha da tabela (delegação)
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



    <script>
    function updateSLATimers() {
        const now = Math.floor(Date.now() / 1000);
        
        document.querySelectorAll('.sla-container').forEach(container => {
            const status = container.dataset.status;
            if (status === 'Pendente') return; // Skip dynamic update for frozen tickets

            const deadline = parseInt(container.dataset.deadline);
            const slaTotal = parseInt(container.dataset.slaTotal);
            const textElement = container.querySelector('.sla-timer-text');
            const progressBar = container.querySelector('.sla-progress-bar');
            
            if (!textElement || !progressBar) return;

            let diff = deadline - now;
            let statusText = '';
            let barClass = 'bg-success';
            
            if (diff <= 0) {
                statusText = '<?php echo __("Vencido"); ?>';
                diff = Math.abs(diff);
                barClass = 'bg-danger';
            } else {
                if (diff <= (slaTotal * 0.2)) { // Less than 20% time remaining (80% used)
                    statusText = '<?php echo __("Atenção"); ?>';
                    barClass = 'bg-warning';
                } else {
                    statusText = '<?php echo __("No Prazo"); ?>';
                }
            }

            // Time Formatting
            let timeStr = '';
            const days = Math.floor(diff / 86400);
            const hours = Math.floor((diff % 86400) / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;

            if (days > 0) {
                timeStr = `${days}d ${hours}h`;
            } else if (hours > 0) {
                timeStr = `${hours}h ${minutes}m`;
            } else {
                timeStr = `${minutes}m ${seconds}s`;
            }

            textElement.textContent = `${statusText} (${timeStr})`;
            
            // Update Progress Bar
            progressBar.className = `progress-bar sla-progress-bar ${barClass} progress-bar-striped progress-bar-animated`;
            
            let percentage = 100;
            if (slaTotal > 0) {
                const usedSeconds = slaTotal - (deadline - now);
                percentage = Math.min(100, Math.max(0, (usedSeconds / slaTotal) * 100));
            }
            if (deadline - now < 0) percentage = 100;
            progressBar.style.width = percentage + '%';
        });
    }

    function confirmarCancelamento(id) {
        if (confirm("<?php echo __('Deseja realmente cancelar este chamado?'); ?>")) {
            $.ajax({
                url: 'ajax_chamados.php',
                type: 'POST',
                data: { acao: 'cancelar', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message || "<?php echo __('Erro ao cancelar chamado.'); ?>");
                    }
                },
                error: function() {
                    alert("<?php echo __('Erro de rede ao tentar cancelar o chamado.'); ?>");
                }
            });
        }
    }

    // Start timer
    setInterval(updateSLATimers, 1000);
    updateSLATimers(); // Initial call
    </script>
</body>

</html>