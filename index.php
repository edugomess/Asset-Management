<?php
/**
 * TELA DE LOGIN: index.php
 * Ponto de entrada do sistema. Gerencia a autenticação inicial e redirecionamento de sessão.
 */
session_start();
include_once 'auth.php';     // Validação de segurança obrigatória
include_once 'conexao.php';  // Conexão com a base de dados

// === Toda a sua lógica de SQL deve vir aqui, antes do HTML ===
$meses = [
    1 => __('Janeiro'),
    2 => __('Fevereiro'),
    3 => __('Março'),
    4 => __('Abril'),
    5 => __('Maio'),
    6 => __('Junho'),
    7 => __('Julho'),
    8 => __('Agosto'),
    9 => __('Setembro'),
    10 => __('Outubro'),
    11 => __('Novembro'),
    12 => __('Dezembro')
];
$ano_atual = date('Y');
$count_aberto = 0;
$count_andamento = 0;
// ... (mantenha o restante das suas queries SQL que estavam no corpo do código aqui)

// Apenas após processar tudo, começamos o HTML
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Dashboard'); ?> - Asset Mgt</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=f3d9abe8d5aa7831c01bfaa2a1563712">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=41e93b37bc495fd67938799bb3a6adaf">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet" href="/assets/css/Simple-footer-by-krissy.css?h=73316da5ae5ad6b51632cd2e5413f263">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <?php include_once 'sidebar_style.php'; ?>
    <style>
        .card.shadow {
            transition: all 0.3s ease-in-out;
            cursor: pointer;
        }

        .card-shadow { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-shadow:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important; 
        }
    </style>
</head>

<?php
// === PROCESSAMENTO DE DADOS: Contagem de Chamados com base no nível de acesso ===
$count_aberto = 0;
$count_andamento = 0;
$count_pendente = 0;
$data = [];
$total_ativos = 0;

$where_chamados = "";
// Filtro de Segurança: Usuários comuns veem apenas seus próprios chamados
if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    $where_chamados = " AND usuario_id = " . $_SESSION['id_usuarios'];
}

$res = mysqli_query($conn, "SELECT status, COUNT(*) as total FROM chamados WHERE status IN ('Aberto', 'Em Andamento', 'Em Atendimento', 'Pendente') $where_chamados GROUP BY status");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $data[$row['status']] = $row['total'];
        $total_ativos += $row['total'];
        if ($row['status'] == 'Aberto')
            $count_aberto = $row['total'];
        elseif ($row['status'] == 'Em Andamento' || $row['status'] == 'Em Atendimento')
            $count_andamento += $row['total'];
        elseif ($row['status'] == 'Pendente')
            $count_pendente = $row['total'];
    }
}
$data_string = implode(",", [
    isset($data['Aberto']) ? $data['Aberto'] : 0,
    $count_andamento,
    isset($data['Pendente']) ? $data['Pendente'] : 0
]);

// === Chamados Fechados por Mês (QUERY UNICA) ===
$closed_data = array_fill(1, 12, 0);
$res_closed = mysqli_query($conn, "SELECT MONTH(data_fechamento) as month, COUNT(*) as count FROM chamados WHERE status IN ('Resolvido', 'Fechado', 'Cancelado') AND YEAR(data_fechamento) = YEAR(CURRENT_DATE()) $where_chamados GROUP BY MONTH(data_fechamento)");
if ($res_closed) {
    while ($row = mysqli_fetch_assoc($res_closed)) {
        $closed_data[$row['month']] = $row['count'];
    }
}
$closed_string = implode(",", $closed_data);
?>

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
                    <div class="d-sm-flex justify-content-between align-items-center mb-4">
                        <h3 class="text-dark mb-0"><?php echo __('Dashboard'); ?></h3>
                        <div class="d-flex align-items-center">
                            <?php
                            $where_closed = "";
                            if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
                                $where_closed = " AND usuario_id = " . $_SESSION['id_usuarios'];
                            }
                            $sql_total_fechados = "SELECT COUNT(*) as total FROM chamados WHERE status IN ('Resolvido', 'Fechado', 'Cancelado') $where_closed";
                            $res_total_fechados = mysqli_query($conn, $sql_total_fechados);
                            $total_fechados = mysqli_fetch_assoc($res_total_fechados)['total'] ?? 0;
                            ?>
                            <div class="bg-success text-white px-2 py-1 rounded shadow-sm d-flex align-items-center mr-2"
                                style="font-size: 0.75rem; height: 31px;">
                                <i class="fas fa-check-circle mr-1"></i>
                                <span class="font-weight-bold"><?php echo __('Total de chamados fechados:'); ?>
                                    <?php echo $total_fechados; ?></span>
                            </div>
                            <a class="text-white px-2 py-1 rounded shadow-sm d-flex align-items-center text-decoration-none"
                                role="button" href="relatorio_resumo_geral.php" target="_blank"
                                style="background: #e74a3b; font-size: 0.75rem; height: 31px;">
                                <i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i>
                                <span class="font-weight-bold"><?php echo __('Gerar Relatório'); ?></span>
                            </a>
                        </div>
                    </div>
                    <div class="row px-2 flex-nowrap overflow-auto">
                        <?php
                        // 1. GESTÃO DE ATIVOS: Coleta métricas de hardware por categoria
                        $where_ativos = "";
                        if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
                            $where_ativos = " WHERE assigned_to = " . $_SESSION['id_usuarios'];
                        }

                        // Busca quantidades totais e disponibilidade (ativos não atribuídos)
                        if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
                            $sql_ativos = "SELECT categoria, COUNT(*) as total, 0 as disponiveis 
                                           FROM ativos WHERE assigned_to = " . $_SESSION['id_usuarios'] . " GROUP BY categoria";
                        } else {
                            $sql_ativos = "SELECT categoria, COUNT(*) as total, SUM(CASE WHEN assigned_to IS NULL OR assigned_to = 0 THEN 1 ELSE 0 END) as disponiveis 
                                           FROM ativos GROUP BY categoria";
                        }
                        $res_ativos = mysqli_query($conn, $sql_ativos);
                        $dados_ativos = [];
                        if ($res_ativos) {
                            while ($row = mysqli_fetch_assoc($res_ativos)) {
                                $dados_ativos[$row['categoria']] = $row;
                            }
                        }

                        // 2. RANKING DE EFICIÊNCIA (SLA): Melhores técnicos do período
                        $mes_filtro = isset($_GET['mes_ranking']) ? intval($_GET['mes_ranking']) : date('m');
                        $ano_filtro = isset($_GET['ano_ranking']) ? intval($_GET['ano_ranking']) : date('Y');

                        if ($_SESSION['nivelUsuario'] !== 'Usuário') {
                            $sql_ranking = "SELECT 
    r.nome, r.sobrenome, r.id_usuarios, r.foto_perfil,
    COUNT(*) as total,
    SUM(CASE WHEN (TIMESTAMPDIFF(MINUTE, c.data_abertura, c.data_fechamento) - COALESCE(c.tempo_congelado_minutos, 0)) <= 
        (CASE 
            WHEN s.tempo_sla_minutos IS NOT NULL THEN s.tempo_sla_minutos
            WHEN c.categoria = 'Incidente' THEN 360
            WHEN c.categoria = 'Mudança' THEN 1440
            WHEN c.categoria = 'Requisição' THEN 2880
            ELSE 1440 
        END * 
        CASE 
            WHEN c.prioridade = 'P1' THEN 1/6.0
            WHEN c.prioridade = 'P2' OR c.prioridade = 'Alta' THEN 1/3.0
            WHEN c.prioridade = 'P3' OR c.prioridade = 'Média' THEN 2/3.0
            ELSE 1.0
        END) THEN 1 ELSE 0 END) as met_sla
FROM chamados c
JOIN usuarios r ON c.responsavel_id = r.id_usuarios
LEFT JOIN configuracoes_sla s ON c.categoria = s.categoria
WHERE c.status IN ('Resolvido', 'Fechado', 'Cancelado')
AND MONTH(c.data_fechamento) = $mes_filtro 
AND YEAR(c.data_fechamento) = $ano_filtro
GROUP BY r.id_usuarios
ORDER BY (SUM(CASE WHEN (TIMESTAMPDIFF(MINUTE, c.data_abertura, c.data_fechamento) - COALESCE(c.tempo_congelado_minutos, 0)) <= 
    (CASE 
        WHEN s.tempo_sla_minutos IS NOT NULL THEN s.tempo_sla_minutos
        WHEN c.categoria = 'Incidente' THEN 360
        WHEN c.categoria = 'Mudança' THEN 1440
        WHEN c.categoria = 'Requisição' THEN 2880
        ELSE 1440 
    END * 
    CASE 
        WHEN c.prioridade = 'Alta' THEN 1/3.0
        WHEN c.prioridade = 'Média' THEN 2/3.0
        ELSE 1.0
    END) THEN 1 ELSE 0 END) / COUNT(*)) DESC";
                            $res_ranking = mysqli_query($conn, $sql_ranking);
                            $ranking_data = [];
                            if ($res_ranking) {
                                while ($row = mysqli_fetch_assoc($res_ranking)) {
                                    $total = (int) $row['total'];
                                    $met_sla = (int) $row['met_sla'];
                                    $row['percentage'] = ($total > 0) ? round(($met_sla / $total) * 100) : 0;
                                    $ranking_data[] = $row;
                                }
                            }
                        }
                        // (Closed data already computed above, no duplicate query needed)
                        
                        if ($_SESSION['nivelUsuario'] !== 'Usuário') {
                            // Ranking de Chamados por Recorrência (Top 5 títulos mais frequentes) - Filtro de período INDEPENDENTE
                            $mes_rec_filtro = isset($_GET['mes_recorrencia']) ? intval($_GET['mes_recorrencia']) : date('m');
                            $ano_rec_filtro = isset($_GET['ano_recorrencia']) ? intval($_GET['ano_recorrencia']) : date('Y');

                            $sql_recorrencia = "SELECT titulo, COUNT(*) as total 
                                                FROM chamados 
                                                WHERE MONTH(data_abertura) = $mes_rec_filtro 
                                                AND YEAR(data_abertura) = $ano_rec_filtro
                                                GROUP BY titulo 
                                                ORDER BY total DESC LIMIT 5";
                            $res_recorrencia = mysqli_query($conn, $sql_recorrencia);
                            $recorrencia_data = [];
                            $max_recorrencia = 0;
                            if ($res_recorrencia) {
                                while ($row = mysqli_fetch_assoc($res_recorrencia)) {
                                    $recorrencia_data[] = $row;
                                    if ($row['total'] > $max_recorrencia) {
                                        $max_recorrencia = $row['total'];
                                    }
                                }
                            }
                        }


                        // 4. Licenças - Métricas Específicas
                        $where_lic = "";
                        $join_lic = "";
                        if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
                            $join_lic = " JOIN atribuicoes_licencas al ON l.id_licenca = al.id_licenca ";
                            $where_lic = " AND al.id_usuario = " . $_SESSION['id_usuarios'];
                        }

                        // M365
                        $sql_m365 = "SELECT SUM(l.quantidade_total) as total, SUM(l.quantidade_uso) as em_uso FROM licencas l $join_lic WHERE l.software LIKE '%365%' $where_lic";
                        if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
                            $sql_m365 = "SELECT COUNT(*) as total, COUNT(*) as em_uso FROM licencas l $join_lic WHERE l.software LIKE '%365%' $where_lic";
                        }
                        $res_m365 = mysqli_query($conn, $sql_m365);
                        $data_m365 = mysqli_fetch_assoc($res_m365);
                        $total_m365 = $data_m365['total'] ?? 0;
                        $disp_m365 = ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') ? 0 : ($total_m365 - ($data_m365['em_uso'] ?? 0));

                        // Adobe
                        $sql_adobe = "SELECT SUM(l.quantidade_total) as total, SUM(l.quantidade_uso) as em_uso FROM licencas l $join_lic WHERE (l.software LIKE '%Adobe%' OR l.fabricante LIKE '%Adobe%') $where_lic";
                        if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
                            $sql_adobe = "SELECT COUNT(*) as total, COUNT(*) as em_uso FROM licencas l $join_lic WHERE (l.software LIKE '%Adobe%' OR l.fabricante LIKE '%Adobe%') $where_lic";
                        }
                        $res_adobe = mysqli_query($conn, $sql_adobe);
                        $data_adobe = mysqli_fetch_assoc($res_adobe);
                        $total_adobe = $data_adobe['total'] ?? 0;
                        $disp_adobe = ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') ? 0 : ($total_adobe - ($data_adobe['em_uso'] ?? 0));

                        // Windows
                        $sql_win = "SELECT SUM(l.quantidade_total) as total, SUM(l.quantidade_uso) as em_uso FROM licencas l $join_lic WHERE l.software LIKE '%Windows%' $where_lic";
                        if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
                            $sql_win = "SELECT COUNT(*) as total, COUNT(*) as em_uso FROM licencas l $join_lic WHERE l.software LIKE '%Windows%' $where_lic";
                        }
                        $res_win = mysqli_query($conn, $sql_win);
                        $data_win = mysqli_fetch_assoc($res_win);
                        $total_win = $data_win['total'] ?? 0;
                        $disp_win = ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') ? 0 : ($total_win - ($data_win['em_uso'] ?? 0));

                        // Expiradas ou prestes a vencer (60 dias)
                        $sql_exp = "SELECT COUNT(*) as total FROM licencas l $join_lic WHERE (l.status = 'Expirada' OR (l.data_expiracao IS NOT NULL AND l.data_expiracao <= DATE_ADD(CURDATE(), INTERVAL 60 DAY))) $where_lic";
                        $res_exp = mysqli_query($conn, $sql_exp);
                        $count_exp = mysqli_fetch_assoc($res_exp)['total'] ?? 0;

                        // Mapeamento manual para os cards (ajuste as chaves conforme o banco de dados)
                        // Exemplo: 'Computadores' no banco pode mapear para o card 'Computadores'
                        // Se não houver correspondencia exata, você pode criar cards genéricos ou ajustar o array $categorias_interesse
                        
                        // Para simplificar e atender o pedido, vamos criar cards dinâmicos baseados no que tem no banco, 
                        // ou manter o layout fixo e preencher com o que encontrar.
                        // Vamos tentar preencher os 4 cards fixos com os dados mais prováveis.
                        
                        // Card 1: Desktops
                        $total_pc = isset($dados_ativos['Desktop']) ? $dados_ativos['Desktop']['total'] : 0;
                        $disp_pc = isset($dados_ativos['Desktop']) ? $dados_ativos['Desktop']['disponiveis'] : 0;

                        // Card 2: Notebooks
                        $total_note = isset($dados_ativos['Notebook']) ? $dados_ativos['Notebook']['total'] : (isset($dados_ativos['Notebooks']) ? $dados_ativos['Notebooks']['total'] : 0);
                        $disp_note = isset($dados_ativos['Notebook']) ? $dados_ativos['Notebook']['disponiveis'] : (isset($dados_ativos['Notebooks']) ? $dados_ativos['Notebooks']['disponiveis'] : 0);

                        // Card 3: Monitores (Monitor, Monitores)
                        $total_mon = (isset($dados_ativos['Monitor']) ? $dados_ativos['Monitor']['total'] : 0) +
                            (isset($dados_ativos['Monitores']) ? $dados_ativos['Monitores']['total'] : 0);
                        $disp_mon = (isset($dados_ativos['Monitor']) ? $dados_ativos['Monitor']['disponiveis'] : 0) +
                            (isset($dados_ativos['Monitores']) ? $dados_ativos['Monitores']['disponiveis'] : 0);

                        // Card 4: Impressoras
                        $total_imp = isset($dados_ativos['Impressoras']) ? $dados_ativos['Impressoras']['total'] : 0;
                        $disp_imp = isset($dados_ativos['Impressoras']) ? $dados_ativos['Impressoras']['disponiveis'] : 0;

                        // Se os totais forem 0, exibir pelo menos um placeholder ou buscar tudo
                        // Vamos exibir todos:
                        ?>

                        <div class="col-xl-1-5 col-md-3 mb-4 px-1 animate__animated animate__fadeInUp"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px; animation-delay: 0.1s;">
                            <div class="card shadow card-shadow border-left-primary py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-primary font-weight-bold text-sm mb-1">
                                                <span><?php echo __('Desktops'); ?></span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_pc; ?></span>
                                                <span class="text-muted small ml-1"
                                                    style="font-size: 0.85rem;">(<?php echo $disp_pc; ?>
                                                    <?php echo __('Disponíveis'); ?>)</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-desktop fa-lg text-gray-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-1-5 col-md-3 mb-4 px-1 animate__animated animate__fadeInUp"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px; animation-delay: 0.2s;">
                            <div class="card shadow card-shadow border-left-success py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-success font-weight-bold text-sm mb-1">
                                                <span><?php echo __('Notebooks'); ?></span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_note; ?></span>
                                                <span class="text-muted small ml-1"
                                                    style="font-size: 0.85rem;">(<?php echo $disp_note; ?>
                                                    <?php echo __('Disponíveis'); ?>)</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-laptop fa-lg text-gray-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-1-5 col-md-3 mb-4 px-1 animate__animated animate__fadeInUp"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px; animation-delay: 0.3s;">
                            <div class="card shadow card-shadow border-left-info py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-info font-weight-bold text-sm mb-1">
                                                <span><?php echo __('Monitores'); ?></span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_mon; ?></span>
                                                <span class="text-muted small ml-1"
                                                    style="font-size: 0.85rem;">(<?php echo $disp_mon; ?>
                                                    <?php echo __('Disponíveis'); ?>)</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-desktop fa-lg text-gray-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-1-5 col-md-3 mb-4 px-1 animate__animated animate__fadeInUp"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px; animation-delay: 0.4s;">
                            <div class="card shadow card-shadow border-left-warning py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-warning font-weight-bold text-sm mb-1">
                                                <span><?php echo __('Impressoras'); ?></span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_imp; ?></span>
                                                <span class="text-muted small ml-1"
                                                    style="font-size: 0.85rem;">(<?php echo $disp_imp; ?>
                                                    <?php echo __('Disponíveis'); ?>)</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-print fa-lg text-gray-600"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Novas Licenças na mesma fileira -->
                        <div class="col-xl-1-5 col-md-3 mb-4 px-1 animate__animated animate__fadeInUp"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px; animation-delay: 0.5s;">
                            <div class="card shadow card-shadow border-left-secondary py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-secondary font-weight-bold text-sm mb-1">
                                                <span>Office 365</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_m365; ?></span>
                                                <span class="text-muted small ml-1" style="font-size: 0.85rem;">(
                                                    <?php echo $disp_m365; ?> <?php echo __('Disponíveis'); ?>)</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-cloud fa-lg text-gray-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-1-5 col-md-3 mb-4 px-1 animate__animated animate__fadeInUp"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px; animation-delay: 0.6s;">
                            <div class="card shadow card-shadow border-left-danger py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-danger font-weight-bold text-sm mb-1">
                                                <span>Adobe CC</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_adobe; ?></span>
                                                <span class="text-muted small ml-1"
                                                    style="font-size: 0.85rem;">(<?php echo $disp_adobe; ?>
                                                    <?php echo __('Disponíveis'); ?>)</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-palette fa-lg text-gray-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-1-5 col-md-3 mb-4 px-1 animate__animated animate__fadeInUp"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px; animation-delay: 0.7s;">
                            <div class="card shadow card-shadow border-left-dark py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-dark font-weight-bold text-sm mb-1">
                                                <span>Windows 11</span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $total_win; ?></span>
                                                <span class="text-muted small ml-1"
                                                    style="font-size: 0.85rem;">(<?php echo $disp_win; ?>
                                                    <?php echo __('Disponíveis'); ?>)</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fab fa-windows fa-lg text-gray-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-1-5 col-md-3 mb-4 px-1 animate__animated animate__fadeInUp"
                            style="flex: 0 0 12.5%; max-width: 12.5%; min-width: 140px; animation-delay: 0.8s;">
                            <div class="card shadow card-shadow border-left-warning py-2"
                                style="border-left-color: #f6c23e !important;">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col mr-2">
                                            <div class="text-uppercase text-warning font-weight-bold text-sm mb-1">
                                                <span><?php echo __('Alertas'); ?></span>
                                            </div>
                                            <div class="text-dark font-weight-bold h5 mb-0">
                                                <span><?php echo $count_exp; ?></span>
                                                <span class="text-muted small ml-1"
                                                    style="font-size: 0.85rem;"><?php echo __('Expirando'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i
                                                class="fas fa-exclamation-circle fa-lg text-gray-600"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="col-lg-7 col-xl-8">
                            <div class="card shadow mb-4 h-100">
                                <div class="card-header d-flex justify-content-between align-items-center"
                                    style="background: rgb(248, 249, 252);">
                                    <h6 class="text-primary font-weight-bold m-0"><?php echo __('Chamados Finalizados (Mês)'); ?></h6>
                                    <div class="dropdown no-arrow"><button class="btn btn-link btn-sm dropdown-toggle"
                                            aria-expanded="false" data-toggle="dropdown" type="button"><i
                                                class="fas fa-ellipsis-v text-gray-400"></i></button>
                                        <div class="dropdown-menu shadow dropdown-menu-right animated--fade-in">
                                            <p class="text-center dropdown-header">Opções:</p><a class="dropdown-item"
                                                href="chamados.php?filtro_status=finalizados">&nbsp;<?php echo __('Ver Finalizados'); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-center">
                                    <div class="chart-area"><canvas
                                            data-bss-chart="{&quot;type&quot;:&quot;line&quot;,&quot;data&quot;:{&quot;labels&quot;:[&quot;<?php echo __('Jan'); ?>&quot;,&quot;<?php echo __('Fev'); ?>&quot;,&quot;<?php echo __('Mar'); ?>&quot;,&quot;<?php echo __('Abr'); ?>&quot;,&quot;<?php echo __('Mai'); ?>&quot;,&quot;<?php echo __('Jun'); ?>&quot;,&quot;<?php echo __('Jul'); ?>&quot;,&quot;<?php echo __('Ago'); ?>&quot;,&quot;<?php echo __('Set'); ?>&quot;,&quot;<?php echo __('Out'); ?>&quot;,&quot;<?php echo __('Nov'); ?>&quot;,&quot;<?php echo __('Dez'); ?>&quot;],&quot;datasets&quot;:[{&quot;label&quot;:&quot;<?php echo __('Chamados'); ?>&quot;,&quot;fill&quot;:true,&quot;data&quot;:[<?php echo $closed_string; ?>],&quot;backgroundColor&quot;:&quot;rgba(78, 115, 223, 0.05)&quot;,&quot;borderColor&quot;:&quot;rgb(26, 121, 158)&quot;}]},&quot;options&quot;:{&quot;maintainAspectRatio&quot;:false,&quot;legend&quot;:{&quot;display&quot;:false,&quot;labels&quot;:{&quot;fontStyle&quot;:&quot;normal&quot;}},&quot;title&quot;:{&quot;fontStyle&quot;:&quot;normal&quot;},&quot;scales&quot;:{&quot;xAxes&quot;:[{&quot;gridLines&quot;:{&quot;color&quot;:&quot;rgb(234, 236, 244)&quot;,&quot;zeroLineColor&quot;:&quot;rgb(234, 236, 244)&quot;,&quot;drawBorder&quot;:false,&quot;drawTicks&quot;:false,&quot;borderDash&quot;:[&quot;2&quot;],&quot;zeroLineBorderDash&quot;:[&quot;2&quot;],&quot;drawOnChartArea&quot;:false},&quot;ticks&quot;:{&quot;fontColor&quot;:&quot;#858796&quot;,&quot;fontStyle&quot;:&quot;normal&quot;,&quot;padding&quot;:20}}],&quot;yAxes&quot;:[{&quot;gridLines&quot;:{&quot;color&quot;:&quot;rgb(234, 236, 244)&quot;,&quot;zeroLineColor&quot;:&quot;rgb(234, 236, 244)&quot;,&quot;drawBorder&quot;:false,&quot;drawTicks&quot;:false,&quot;borderDash&quot;:[&quot;2&quot;],&quot;zeroLineBorderDash&quot;:[&quot;2&quot;]},&quot;ticks&quot;:{&quot;fontColor&quot;:&quot;#858796&quot;,&quot;fontStyle&quot;:&quot;normal&quot;,&quot;padding&quot;:20}}]}}}"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 col-xl-4">
                            <div class="card shadow mb-4 h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="text-primary font-weight-bold m-0"><?php echo __('Status dos Chamados'); ?></h6>
                                    <div class="dropdown no-arrow"><button class="btn btn-link btn-sm dropdown-toggle"
                                            aria-expanded="false" data-toggle="dropdown" type="button"><i
                                                class="fas fa-ellipsis-v text-gray-400"></i></button>
                                        <div class="dropdown-menu shadow dropdown-menu-right animated--fade-in">
                                            <p class="text-center dropdown-header">Opções:</p><a class="dropdown-item"
                                                href="chamados.php">&nbsp;Ver Chamados</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div class="chart-area" style="position: relative;">
                                        <canvas
                                            data-bss-chart="{&quot;type&quot;:&quot;doughnut&quot;,&quot;data&quot;:{&quot;labels&quot;:[&quot;<?php echo __('Aberto'); ?>&quot;,&quot;<?php echo __('Em Andamento'); ?>&quot;,&quot;<?php echo __('Pendente'); ?>&quot;],&quot;datasets&quot;:[{&quot;label&quot;:&quot;&quot;,&quot;backgroundColor&quot;:[&quot;#4e73df&quot;,&quot;#36b9cc&quot;,&quot;#f6c23e&quot;],&quot;borderColor&quot;:[&quot;#ffffff&quot;,&quot;#ffffff&quot;,&quot;#ffffff&quot;],&quot;data&quot;:[<?php echo $data_string; ?>]}]},&quot;options&quot;:{&quot;maintainAspectRatio&quot;:false,&quot;cutoutPercentage&quot;:80,&quot;legend&quot;:{&quot;display&quot;:false,&quot;labels&quot;:{&quot;fontStyle&quot;:&quot;normal&quot;}},&quot;title&quot;:{&quot;fontStyle&quot;:&quot;normal&quot;},&quot;animation&quot;:{&quot;animateRotate&quot;:true,&quot;animateScale&quot;:true,&quot;duration&quot;:2500},&quot;tooltips&quot;:{&quot;backgroundColor&quot;:&quot;#fff&quot;,&quot;bodyFontColor&quot;:&quot;#858796&quot;,&quot;borderColor&quot;:&quot;#dddfeb&quot;,&quot;borderWidth&quot;:1,&quot;xPadding&quot;:15,&quot;yPadding&quot;:15,&quot;displayColors&quot;:false,&quot;caretPadding&quot;:10}}}"></canvas>
                                        <div
                                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 4.7rem; font-weight: 800; color: #5a5c69; pointer-events: none;">
                                            <?php echo $total_ativos; ?>
                                        </div>
                                    </div>
                                    <div class="text-center small mt-4"><span class="mr-2"><i
                                                class="fas fa-circle text-primary"></i> <?php echo __('Aberto'); ?></span><span
                                            class="mr-2"><i class="fas fa-circle text-info"></i> <?php echo __('Em And.'); ?></span><span
                                            class="mr-2"><i class="fas fa-circle text-warning"></i> <?php echo __('Pendente'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- End: Charts -->
                    <!-- Start: SLA Ranking -->
                    <?php if ($_SESSION['nivelUsuario'] !== 'Usuário'): ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                        <h6 class="m-0 font-weight-bold text-primary"><?php echo __('Ranking de SLA - Melhores Técnicos'); ?>
                                        </h6>
                                        <form method="GET" class="form-inline">
                                            <!-- Preservar filtro de recorrência ao filtrar SLA -->
                                            <input type="hidden" name="mes_recorrencia"
                                                value="<?php echo $mes_rec_filtro; ?>">
                                            <input type="hidden" name="ano_recorrencia"
                                                value="<?php echo $ano_rec_filtro; ?>">

                                            <select name="mes_ranking" id="mes_ranking"
                                                class="form-control form-control-sm mr-2" style="font-size: 0.75rem;">
                                                <?php
                                                foreach ($meses as $num => $nome) {
                                                    $selected = ($num == $mes_filtro) ? 'selected' : '';
                                                    echo "<option value='$num' $selected>$nome</option>";
                                                }
                                                ?>
                                            </select>
                                            <select name="ano_ranking" id="ano_ranking"
                                                class="form-control form-control-sm mr-2" style="font-size: 0.75rem;">
                                                <?php
                                                for ($i = $ano_atual; $i >= $ano_atual - 2; $i--) {
                                                    $selected = ($i == $ano_filtro) ? 'selected' : '';
                                                    echo "<option value='$i' $selected>$i</option>";
                                                }
                                                ?>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm mr-2"
                                                style="font-size: 0.7rem; background: rgb(44,64,74); border-color: rgb(44,64,74);"><?php echo __('Filtrar'); ?></button>
                                            <a href="relatorio_ranking_sla.php?mes=<?php echo $mes_filtro; ?>&ano=<?php echo $ano_filtro; ?>"
                                                id="btn_pdf_sla" target="_blank" class="btn btn-danger btn-sm"
                                                style="font-size: 0.7rem; background: #e74a3b;">
                                                <i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i> PDF
                                            </a>
                                        </form>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo __('Responsável'); ?></th>
                                                        <th><?php echo __('Chamados Resolvidos'); ?></th>
                                                        <th><?php echo __('Dentro do Prazo'); ?> </th>
                                                        <th><?php echo __('% SLA Atingido'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($ranking_data as $rank): ?>
                                                        <tr>
                                                            <td class="align-middle">
                                                                <img class="img-profile rounded-circle"
                                                                    style="width: 30px; height: 30px; margin-right: 10px; object-fit: cover;"
                                                                    src="<?php echo !empty($rank['foto_perfil']) ? htmlspecialchars($rank['foto_perfil']) : '/assets/img/avatars/avatar1.jpeg'; ?>">
                                                                <?php echo htmlspecialchars($rank['nome'] . ' ' . $rank['sobrenome']); ?>
                                                            </td>
                                                            <td class="align-middle"><?php echo $rank['total']; ?></td>
                                                            <td class="align-middle"><?php echo $rank['met_sla']; ?></td>
                                                            <td class="align-middle">
                                                                <div class="progress" style="height: 20px;">
                                                                    <?php
                                                                    $color = 'bg-danger';
                                                                    if ($rank['percentage'] >= 80)
                                                                        $color = 'bg-success';
                                                                    elseif ($rank['percentage'] >= 50)
                                                                        $color = 'bg-warning';
                                                                    ?>
                                                                    <div class="progress-bar <?php echo $color; ?>"
                                                                        role="progressbar"
                                                                        style="width: <?php echo $rank['percentage']; ?>%"
                                                                        aria-valuenow="<?php echo $rank['percentage']; ?>"
                                                                        aria-valuemin="0" aria-valuemax="100">
                                                                        <?php echo $rank['percentage']; ?>%
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    endforeach; ?>
                                                    <?php if (empty($ranking_data)): ?>
                                                        <tr>
                                                            <td colspan="4" class="text-center"><?php echo __('Nenhum chamado finalizado neste período.'); ?></td>
                                                        </tr>
                                                        <?php
                                                    endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- End: SLA Ranking -->
                    <?php if ($_SESSION['nivelUsuario'] !== 'Usuário'): ?>
                        <div class="row">
                            <div class="col-lg-12 mb-4">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                        <h6 class="text-primary font-weight-bold m-0"><?php echo __('Ranking de Chamados por Recorrência'); ?>
                                        </h6>
                                        <form method="GET" class="form-inline">
                                            <!-- Preservar filtro de SLA al filtrar Recorrência -->
                                            <input type="hidden" name="mes_ranking" value="<?php echo $mes_filtro; ?>">
                                            <input type="hidden" name="ano_ranking" value="<?php echo $ano_filtro; ?>">

                                            <select name="mes_recorrencia" id="mes_recorrencia"
                                                class="form-control form-control-sm mr-2" style="font-size: 0.75rem;">
                                                <?php
                                                foreach ($meses as $num => $nome) {
                                                    $selected = ($num == $mes_rec_filtro) ? 'selected' : '';
                                                    echo "<option value='$num' $selected>$nome</option>";
                                                }
                                                ?>
                                            </select>
                                            <select name="ano_recorrencia" id="ano_recorrencia"
                                                class="form-control form-control-sm mr-2" style="font-size: 0.75rem;">
                                                <?php
                                                for ($i = $ano_atual; $i >= $ano_atual - 2; $i--) {
                                                    $selected = ($i == $ano_rec_filtro) ? 'selected' : '';
                                                    echo "<option value='$i' $selected>$i</option>";
                                                }
                                                ?>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm mr-2"
                                                style="font-size: 0.7rem; background: rgb(44,64,74); border-color: rgb(44,64,74);"><?php echo __('Filtrar'); ?></button>
                                            <a href="relatorio_ranking_recorrencia.php?mes=<?php echo $mes_rec_filtro; ?>&ano=<?php echo $ano_rec_filtro; ?>"
                                                id="btn_pdf_recorrencia" target="_blank" class="btn btn-danger btn-sm"
                                                style="font-size: 0.7rem; background: #e74a3b;">
                                                <i class="fas fa-file-pdf fa-sm text-white-50 mr-1"></i> PDF
                                            </a>
                                        </form>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        if (!empty($recorrencia_data)) {
                                            $cores = ['bg-danger', 'bg-warning', 'bg-primary', 'bg-info', 'bg-success'];
                                            foreach ($recorrencia_data as $i => $rec) {
                                                $pct = ($max_recorrencia > 0) ? round(($rec['total'] / $max_recorrencia) * 100) : 0;
                                                $cor = $cores[$i % count($cores)];
                                                $titulo_chamado = htmlspecialchars(mb_strimwidth($rec['titulo'], 0, 45, '...'));
                                                ?>
                                                <h4 class="small font-weight-bold"><?php echo $titulo_chamado; ?><span
                                                        class="float-right"><?php echo $rec['total']; ?> <?php echo __('chamado(s)'); ?></span></h4>
                                                <div class="progress mb-4">
                                                    <div class="progress-bar <?php echo $cor; ?>" role="progressbar"
                                                        aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100"
                                                        style="width: <?php echo $pct; ?>%;">
                                                        <?php echo $rec['total']; ?>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        } else {
                                            echo '<p class="text-center text-muted">' . __('Nenhum chamado registrado.') . '</p>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
    <script>
        // Inline script removed, moved to global_search.js
    </script>
    <script>
        // Atualizar links de PDF dinamicamente ao mudar os selects
        function updateSlaPdfLink() {
            const mes = document.getElementById('mes_ranking').value;
            const ano = document.getElementById('ano_ranking').value;
            document.getElementById('btn_pdf_sla').href = `relatorio_ranking_sla.php?mes=${mes}&ano=${ano}`;
        }

        function updateRecPdfLink() {
            const mes = document.getElementById('mes_recorrencia').value;
            const ano = document.getElementById('ano_recorrencia').value;
            document.getElementById('btn_pdf_recorrencia').href = `relatorio_ranking_recorrencia.php?mes=${mes}&ano=${ano}`;
        }

        document.getElementById('mes_ranking').addEventListener('change', updateSlaPdfLink);
        document.getElementById('ano_ranking').addEventListener('change', updateSlaPdfLink);
        document.getElementById('mes_recorrencia').addEventListener('change', updateRecPdfLink);
        document.getElementById('ano_recorrencia').addEventListener('change', updateRecPdfLink);
    </script>
</body>

</html>