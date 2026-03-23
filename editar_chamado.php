<?php
/**
 * DETALHES E EDIÇÃO DE CHAMADO: editar_chamado.php
 * Interface para técnicos e usuários visualizarem e atualizarem tickets.
 * Gerencia status, atribuição de responsável, SLA (congelamento) e histórico de notas.
 */
include_once 'auth.php'; // Proteção de sessão
include_once 'conexao.php'; // Banco de Dados
date_default_timezone_set('America/Sao_Paulo');

$id_chamado = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = '';

// Processar atualização (POST para si mesmo)
$id_usuario_logado = $_SESSION['id_usuarios'];

// Para o bloco de aprovação, precisamos do id_gestor_aprovador_base antes do POST principal
// Busca os valores atuais do banco como base para aprovação
$sql_base_aprovacao = "SELECT id_gestor_aprovador FROM chamados WHERE id = $id_chamado";
$res_base_aprovacao = $conn->query($sql_base_aprovacao);
$chamado_base_aprovacao = $res_base_aprovacao->fetch_assoc();
$id_gestor_aprovador_base = $chamado_base_aprovacao['id_gestor_aprovador'] ?? null;


// Processar Aprovação do Gestor
if (isset($_POST['acao_gestor']) && $id_usuario_logado == $id_gestor_aprovador_base) {
    $aprovacao = ($_POST['acao_gestor'] === 'aprovar') ? 1 : 0;
    $sql_aprov = "UPDATE chamados SET aprovado_gestor = $aprovacao WHERE id = $id_chamado";
    if ($conn->query($sql_aprov) === true) {
        $msg = '<div class="alert alert-success">' . ($aprovacao ? __('Chamado aprovado com sucesso!') : __('Chamado marcado como pendente de aprovação.')) . '</div>';
    } else {
        $msg = '<div class="alert alert-danger">Erro ao processar aprovação: ' . $conn->error . '</div>';
    }
    // Recarregar os dados do chamado após a aprovação para refletir a mudança
    // A recarga completa será feita mais abaixo, mas para o fluxo do POST, podemos atualizar o $chamado_base_aprovacao
    // ou simplesmente deixar que o fetch final do chamado atualize tudo.
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['status']) || isset($_POST['nova_nota']) || isset($_POST['notas_existentes']))) {
    // Busca os valores atuais do banco como base (caso campos venham desabilitados no POST)
    $sql_atual = "SELECT status, responsavel_id, prioridade, service_tag, categoria, aprovado_gestor FROM chamados WHERE id = $id_chamado";
    $res_atual = $conn->query($sql_atual);
    $chamado_base = $res_atual->fetch_assoc();

    $novo_status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : $chamado_base['status'];
    $responsavel_id = isset($_POST['responsavel_id']) ? (!empty($_POST['responsavel_id']) ? intval($_POST['responsavel_id']) : 'NULL') : (!empty($chamado_base['responsavel_id']) ? intval($chamado_base['responsavel_id']) : 'NULL');
    $prioridade = isset($_POST['prioridade']) ? mysqli_real_escape_string($conn, $_POST['prioridade']) : $chamado_base['prioridade'];
    $service_tag = isset($_POST['service_tag']) ? mysqli_real_escape_string($conn, $_POST['service_tag']) : ($chamado_base['service_tag'] ?? null);

    // Restrição de Segurança: Se não for Admin/Suporte, ignora alterações de status, responsável e prioridade
    $is_tecnico = ($_SESSION['nivelUsuario'] === 'Admin' || $_SESSION['nivelUsuario'] === 'Suporte');
    if (!$is_tecnico) {
        $novo_status = $chamado_base['status'];
        $responsavel_id = !empty($chamado_base['responsavel_id']) ? intval($chamado_base['responsavel_id']) : 'NULL';
        $prioridade = $chamado_base['prioridade'];
    }

    // HISTÓRICO DE NOTAS: Recupera as notas de resolução armazenadas em formato JSON
    $sql_notas = "SELECT nota_resolucao FROM chamados WHERE id = $id_chamado";
    $res_notas = $conn->query($sql_notas);
    $row_notas = $res_notas->fetch_assoc();
    $raw_notas = $row_notas['nota_resolucao'] ?? '';

    $notas_array = [];
    if (!empty($raw_notas)) {
        $decoded = json_decode($raw_notas, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $notas_array = $decoded;
        } else {
            // Nota legada (texto simples) - converter para formato array
            $notas_array = [['texto' => $raw_notas, 'data' => date('d/m/Y H:i'), 'usuario' => $_SESSION['nome_usuario'] ?? 'Sistema']];
        }
    }

    // Atualizar notas existentes que foram editadas
    if (isset($_POST['notas_existentes']) && is_array($_POST['notas_existentes'])) {
        foreach ($_POST['notas_existentes'] as $i => $texto) {
            $i = intval($i);
            if (isset($notas_array[$i])) {
                $texto_original = str_replace("\r\n", "\n", $notas_array[$i]['texto'] ?? '');
                $novo_texto = str_replace("\r\n", "\n", trim($texto));

                // Só marca como editado se o texto realmente mudou
                if ($texto_original !== $novo_texto) {
                    $notas_array[$i]['texto'] = $novo_texto;
                    $notas_array[$i]['editado_em'] = date('d/m/Y H:i');
                }
            }
        }
    }

    // Adicionar nova nota se preenchida
    $nova_nota_texto = isset($_POST['nova_nota']) ? trim($_POST['nova_nota']) : '';
    if (!empty($nova_nota_texto)) {
        $notas_array[] = [
            'texto' => $nova_nota_texto,
            'data' => date('d/m/Y H:i'),
            'usuario' => $_SESSION['nome_usuario'] ?? 'Sistema'
        ];
    }

    $nota_resolucao = mysqli_real_escape_string($conn, json_encode($notas_array, JSON_UNESCAPED_UNICODE));

    // Validação: Impedir alteração de status (se não for Aberto) sem responsável
    if ($novo_status !== 'Aberto' && $responsavel_id === 'NULL') {
        $msg = '<div class="alert alert-danger"><strong>' . __('Erro:') . '</strong> ' . __('Para alterar o status (sair de "Aberto"), é obrigatório atribuir um') . ' <strong>' . __('Responsável') . '</strong> ' . __('ao chamado.') . '</div>';
    } elseif ($novo_status === 'Em Andamento' && ($chamado_base['categoria'] === 'Requisição' || $chamado_base['categoria'] === 'Mudança') && !$chamado_base['aprovado_gestor']) {
        $msg = '<div class="alert alert-warning"><strong>' . __('Atenção:') . '</strong> ' . __('Este chamado requer aprovação do gestor antes de ser colocado "Em Andamento".') . '</div>';
    } else {
        // LÓGICA DE CONGELAMENTO DE SLA e DATA FECHAMENTO
        $congelamento_sql = "";
        $fechamento_sql = "";
        
        $sql_old = "SELECT status, data_ultimo_congelamento FROM chamados WHERE id = $id_chamado";
        $res_old = $conn->query($sql_old);
        $old = $res_old->fetch_assoc();
        
        if ($old['status'] !== 'Pendente' && $novo_status === 'Pendente') {
            $congelamento_sql = ", data_ultimo_congelamento = NOW()";
        } elseif ($old['status'] === 'Pendente' && $novo_status !== 'Pendente') {
            $inicio = $old['data_ultimo_congelamento'];
            if (!empty($inicio)) {
                $congelamento_sql = ", tempo_congelado_minutos = tempo_congelado_minutos + TIMESTAMPDIFF(MINUTE, '$inicio', NOW()), data_ultimo_congelamento = NULL";
            }
        }

        $status_fechados = ['Resolvido', 'Fechado', 'Cancelado'];
        if (in_array($novo_status, $status_fechados)) {
            $fechamento_sql = ", data_fechamento = NOW()";
        } else {
            $fechamento_sql = ", data_fechamento = NULL";
        }

        $id_asset = isset($_POST['id_asset']) && !empty($_POST['id_asset']) ? (int)$_POST['id_asset'] : ($chamado_base['id_asset'] ?? 'NULL');
        $id_gestor_aprovador = isset($_POST['id_gestor_aprovador']) && !empty($_POST['id_gestor_aprovador']) ? (int)$_POST['id_gestor_aprovador'] : ($chamado_base['id_gestor_aprovador'] ?? 'NULL');
        $sql_update = "UPDATE chamados SET status = '$novo_status', responsavel_id = $responsavel_id, prioridade = '$prioridade', service_tag = '$service_tag', id_asset = $id_asset, id_gestor_aprovador = $id_gestor_aprovador, nota_resolucao = '$nota_resolucao' $fechamento_sql $congelamento_sql WHERE id = $id_chamado";

        if ($conn->query($sql_update) === true) {
            $msg = '<div class="alert alert-success">' . __('Chamado atualizado com sucesso!') . '</div>';
        } else {
            $msg = '<div class="alert alert-danger">Erro ao atualizar: ' . $conn->error . '</div>';
        }
    }
}

// Buscar dados finais do chamado
$sql = "SELECT c.*, 
               u.nome AS sol_nome, u.sobrenome AS sol_sobrenome,
               r.nome AS resp_nome, r.sobrenome AS resp_sobrenome,
               g.nome AS gestor_nome, g.sobrenome AS gestor_sobrenome,
               COALESCE(c.id_asset, a.id_asset) AS resolvido_id_asset
        FROM chamados c 
        LEFT JOIN usuarios u ON c.usuario_id = u.id_usuarios 
        LEFT JOIN usuarios r ON c.responsavel_id = r.id_usuarios
        LEFT JOIN usuarios g ON c.id_gestor_aprovador = g.id_usuarios
        LEFT JOIN ativos a ON c.service_tag = a.tag
        WHERE c.id = $id_chamado";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: chamados.php");
    exit();
}
$chamado = $result->fetch_assoc();

// Lista de Técnicos
$sql_users = "SELECT id_usuarios, nome, sobrenome FROM usuarios WHERE nivelUsuario IN ('Admin', 'Suporte') ORDER BY nome";
$result_users = $conn->query($sql_users);

$is_tecnico = ($_SESSION['nivelUsuario'] === 'Admin' || $_SESSION['nivelUsuario'] === 'Suporte');

// Configurações de IA
$sql_ia = "SELECT ia_agente_ativo, ia_chamados_ativo FROM configuracoes_alertas LIMIT 1";
$res_ia = mysqli_query($conn, $sql_ia);
$ia_ativo = true;
if ($res_ia && mysqli_num_rows($res_ia) > 0) {
    $row_ia = mysqli_fetch_assoc($res_ia);
    $ia_ativo = (bool)($row_ia['ia_agente_ativo'] && $row_ia['ia_chamados_ativo']);
}

// Configurações de SLA
$sla_configs = [];
$res_config = mysqli_query($conn, "SELECT categoria, tempo_sla_minutos FROM configuracoes_sla");
if ($res_config) {
    while ($row_config = mysqli_fetch_assoc($res_config)) {
        $sla_configs[$row_config['categoria']] = $row_config['tempo_sla_minutos'];
    }
}
$sla_defaults = ['Incidente' => 360, 'Mudança' => 1440, 'Requisição' => 2880];
?>
<!DOCTYPE html>
<html lang="<?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'pt-BR') ? 'pt-br' : 'en'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Editar Chamado'); ?> #<?php echo $chamado['id']; ?> - Asset MGT</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=3265483e434712d72c41db9eebc4c8bb">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=d6a29779d310462e7fcdde7b9a80e0db">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=5f41e73f827c7b56616237a1da13b6e2">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=19488c1c6619bc9bd5c02de5f7ffbfd4">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=193916adb9d7af47fe74d9a2270caac3">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <?php include_once 'sidebar_style.php'; ?>
    <style>
        /* Estilos Premium para Detalhes do Chamado */
        :root {
            --primary-dark: #2c404a;
            --accent-blue: #4e73df;
            --accent-green: #1cc88a;
            --accent-info: #36b9cc;
            --accent-warning: #f6c23e;
            --accent-danger: #e74a3b;
            --bg-light: #f8f9fc;
            --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }

        .ticket-header-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px 30px;
            margin-bottom: 25px;
            box-shadow: var(--card-shadow);
            border-left: 6px solid var(--primary-dark);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .sla-progress-container {
            min-width: 220px;
            margin: 0 15px;
            padding-left: 15px;
            border-left: 1px solid #eaecf4;
            flex-grow: 1;
            max-width: 350px;
        }

        .sla-timer-text {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #5a5c69;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
        }

        .progress-premium {
            height: 8px;
            background-color: #eaecf4;
            border-radius: 10px;
            overflow: hidden;
        }

        .input-group-premium {
            display: flex;
            align-items: center;
            background: #f8faff !important;
            border: 1px solid #d1d3e2;
            border-radius: 12px;
            padding: 4px 8px;
            transition: all 0.2s;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        }

        .input-group-premium:focus-within {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1), inset 0 1px 2px rgba(0,0,0,0.05);
            background: #fff !important;
        }

        .input-group-premium input {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            font-weight: 700;
            color: var(--primary-dark);
            flex-grow: 1;
            font-size: 0.9rem;
            height: 38px;
        }

        .btn-view-asset {
            background: var(--accent-blue);
            color: #fff !important;
            border-radius: 10px !important;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            box-shadow: 0 4px 6px rgba(78, 115, 223, 0.2);
            text-decoration: none !important;
        }

        .btn-view-asset:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(44, 64, 74, 0.3);
        }

        .ticket-title-section h3 {
            font-weight: 800;
            color: var(--primary-dark);
            margin: 0;
            font-size: 1.5rem;
        }

        .ticket-badges-section {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .badge-premium {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .info-card {
            background: #fff;
            border-radius: 15px;
            border: none;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
            overflow: hidden;
            transition: transform 0.2s ease;
        }

        .info-card .card-header {
            background: #fcfcfd;
            border-bottom: 1px solid #f1f1f1;
            padding: 15px 20px;
            font-weight: 800;
            color: var(--primary-dark);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card .card-body {
            padding: 20px;
        }

        /* Timeline de Interações */
        .interaction-timeline {
            position: relative;
            padding: 10px 0;
        }

        .interaction-timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #eaecf4;
        }

        .timeline-entry {
            position: relative;
            margin-bottom: 25px;
            padding-left: 55px;
        }

        .timeline-dot {
            position: absolute;
            left: 13px;
            top: 5px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid var(--primary-dark);
            z-index: 2;
        }

        .timeline-bubble {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #f1f1f1;
            box-shadow: var(--card-shadow);
            position: relative;
            transition: transform 0.2s ease;
        }

        .timeline-bubble:hover { transform: translateX(5px); }

        .timeline-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.8rem;
        }

        .timeline-user {
            font-weight: 800;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .timeline-date {
            color: #aeb1be;
        }

        .timeline-text {
            color: #4a4a4a;
            white-space: pre-wrap;
            font-size: 0.95rem;
            line-height: 1.5;
            border: none !important;
            background: transparent !important;
            padding: 0 !important;
            resize: none;
            overflow: hidden;
        }

        .entry-solicitante .timeline-bubble { border-left: 5px solid var(--accent-blue); background: #f8faff; }
        .entry-tecnico .timeline-bubble { border-left: 5px solid var(--accent-green); background: #f8fffb; }

        .timeline-edited {
            font-size: 0.65rem;
            color: #856404;
            background: rgba(246, 194, 62, 0.15);
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid rgba(246, 194, 62, 0.2);
        }

        /* Utilitários */
        .form-label-premium {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #b7b9cc;
            margin-bottom: 5px;
            display: block;
            letter-spacing: 0.5px;
        }

        .form-control-premium {
            border-radius: 10px;
            border: 1px solid #e3e6f0;
            padding: 0 15px; /* Usei padding horizontal e deixei a altura fixa com line-height */
            font-size: 0.95rem;
            color: var(--primary-dark);
            font-weight: 600;
            background: #fff;
            height: 45px !important; 
            line-height: 43px; /* Centraliza verticalmente o texto */
        }

        .form-control-premium:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1);
        }

        .field-readonly {
            background: #fdfdfe !important;
            border-color: #f1f1f1 !important;
            color: #5a5c69;
        }

        .btn-premium {
            border-radius: 12px;
            padding: 12px 25px;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-premium:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .btn-save { background: var(--primary-dark); color: #fff; }
        .btn-back { background: #eaecf4; color: #5a5c69; }

        .lightbox-modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:9999; justify-content:center; align-items:center; backdrop-filter: blur(5px); }
        .lightbox-close { position:absolute; top:20px; right:30px; color:#fff; font-size:35px; cursor:pointer; }

        .ai-suggestion-card {
            background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);
            border-left: 5px solid var(--accent-info);
        }

        .approval-card {
            background: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
            border-left: 5px solid var(--accent-warning);
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: var(--primary-dark);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include_once 'sidebar_brand.php'; ?>
                <?php include_once 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <?php include_once 'topbar.php'; ?>
                <div class="container-fluid">
                    
                    <form method="post" id="form-editar-chamado">
                        <!-- Header Dinâmico -->
                        <div class="ticket-header-card animate__animated animate__fadeInDown">
                            <div class="ticket-title-section">
                                <small class="text-muted font-weight-bold text-uppercase" style="letter-spacing: 1px;">Ticket ID #<?php echo $chamado['id']; ?></small>
                                <h3><?php echo htmlspecialchars($chamado['titulo']); ?></h3>
                            </div>

                            <!-- SLA Timer Bar -->
                            <div class="ticket-sla-section d-none d-lg-block">
                                <?php
                                $data_abertura = new DateTime($chamado['data_abertura']);
                                $cat_sla = $sla_configs[$chamado['categoria']] ?? ($sla_defaults[$chamado['categoria']] ?? 360);
                                if ($chamado['prioridade'] === 'Alta') {
                                    $sla_total_minutos = round($cat_sla / 3);
                                } elseif ($chamado['prioridade'] === 'Média') {
                                    $sla_total_minutos = round(($cat_sla * 2) / 3);
                                } else {
                                    $sla_total_minutos = $cat_sla;
                                }

                                if ($chamado['status'] === 'Pendente' && !empty($chamado['data_ultimo_congelamento'])) {
                                    $agora = new DateTime();
                                    $data_congelamento = new DateTime($chamado['data_ultimo_congelamento']);
                                    $intervalo_congelamento = $data_congelamento->diff($agora);
                                    $minutos_congelamento_atual = ($intervalo_congelamento->days * 24 * 60) + ($intervalo_congelamento->h * 60) + $intervalo_congelamento->i;
                                    $deadline_timestamp = $data_abertura->getTimestamp() + ($sla_total_minutos * 60) + (intval($chamado['tempo_congelado_minutos'] ?? 0) * 60) + ($minutos_congelamento_atual * 60);
                                } else {
                                    $deadline_timestamp = $data_abertura->getTimestamp() + ($sla_total_minutos * 60) + (intval($chamado['tempo_congelado_minutos'] ?? 0) * 60);
                                }
                                ?>
                                <div class="sla-progress-container" 
                                     id="sla-header-container"
                                     data-deadline="<?php echo $deadline_timestamp; ?>" 
                                     data-sla-total="<?php echo $sla_total_minutos * 60; ?>" 
                                     data-status="<?php echo $chamado['status']; ?>">
                                    <div class="sla-timer-text">
                                        <span>SLA STATUS</span>
                                        <span id="sla-header-text">--:--</span>
                                    </div>
                                    <div class="progress progress-premium">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="sla-header-bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="ticket-badges-section">
                                <?php
                                $status_class = 'bg-secondary';
                                if ($chamado['status'] == 'Aberto') { $status_class = 'bg-info'; }
                                elseif ($chamado['status'] == 'Em Andamento') { $status_class = 'bg-primary'; }
                                elseif ($chamado['status'] == 'Pendente') { $status_class = 'bg-warning'; }
                                elseif ($chamado['status'] == 'Resolvido') { $status_class = 'bg-success'; }
                                elseif ($chamado['status'] == 'Cancelado') { $status_class = 'bg-danger'; }
                                ?>
                                <span class="badge-premium <?php echo $status_class; ?> text-white"><i class="fas fa-circle mr-2" style="font-size: 8px;"></i><?php echo __($chamado['status']); ?></span>
                                
                                 <?php
                                 $prio_class = 'bg-light text-dark';
                                 if ($chamado['prioridade'] == 'P1') { $prio_class = 'bg-danger text-white border-left-danger'; }
                                 elseif ($chamado['prioridade'] == 'P2') { $prio_class = 'bg-warning text-dark border-left-warning'; }
                                 elseif ($chamado['prioridade'] == 'P3') { $prio_class = 'bg-info text-dark border-left-info'; }
                                 elseif ($chamado['prioridade'] == 'P4') { $prio_class = 'bg-success text-white border-left-success'; }
                                 elseif ($chamado['prioridade'] == 'Alta') { $prio_class = 'bg-danger text-white'; }
                                 elseif ($chamado['prioridade'] == 'Média') { $prio_class = 'bg-warning text-dark'; }
                                ?>
                                <span class="badge-premium <?php echo $prio_class; ?>"><i class="fas fa-bolt mr-2"></i><?php echo __($chamado['prioridade'] ?: 'Sem Prioridade'); ?></span>
                            </div>
                        </div>

                        <div class="text-center mb-4"><?php echo $msg; ?></div>

                        <div class="row">
                            <!-- Coluna Esquerda: Conteúdo Principal -->
                            <div class="col-lg-8">
                                
                                <!-- Descrição -->
                                <div class="info-card mb-4 animate__animated animate__fadeInLeft" style="animation-delay: 0.1s;">
                                    <div class="card-header">
                                        <i class="fas fa-align-left text-primary"></i> <?php echo __('Descrição do Problema'); ?>
                                    </div>
                                    <div class="card-body">
                                        <div class="p-3 bg-light rounded" style="border: 1px solid #f1f1f1; min-height: 100px;">
                                            <?php echo nl2br(htmlspecialchars($chamado['descricao'])); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Histórico -->
                                <div class="info-card mb-4 animate__animated animate__fadeInLeft" style="animation-delay: 0.2s;">
                                    <div class="card-header">
                                        <i class="fas fa-history text-success"></i> <?php echo __('Linha do Tempo de Atendimento'); ?>
                                    </div>
                                    <div class="card-body">
                                        <div class="interaction-timeline">
                                            <?php
                                            $notas = [];
                                            if (!empty($chamado['nota_resolucao'])) {
                                                $dec = json_decode($chamado['nota_resolucao'], true);
                                                if (is_array($dec)) $notas = $dec;
                                            }
                                            
                                            if (empty($notas)): ?>
                                                <div class="text-center text-muted py-4">
                                                    <i class="fas fa-comment-slash fa-3x mb-3 opacity-25"></i>
                                                    <p><?php echo __('Nenhuma interação registrada ainda.'); ?></p>
                                                </div>
                                            <?php endif;

                                            foreach ($notas as $idx => $nota): 
                                                $nome_sol = ($chamado['sol_nome'] ?? '') . ' ' . ($chamado['sol_sobrenome'] ?? '');
                                                $nome_resp = ($chamado['resp_nome'] ?? '') . ' ' . ($chamado['resp_sobrenome'] ?? '');
                                                $autor = $nota['usuario'] ?? '';
                                                
                                                // Verifica se o autor é técnico (para estilização)
                                                // Como não temos o ID do autor na nota, comparamos o nome com a lista de técnicos obtida no início
                                                $is_tecnico_autor = false;
                                                if (isset($result_users)) {
                                                    $result_users->data_seek(0);
                                                    while($u = $result_users->fetch_assoc()) {
                                                        if (($u['nome'] . ' ' . $u['sobrenome']) === $autor) {
                                                            $is_tecnico_autor = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                                
                                                $entry_class = '';
                                                if ($autor === $nome_sol) {
                                                    $entry_class = 'entry-solicitante';
                                                } elseif ($autor === $nome_resp || (isset($is_tecnico_autor) && $is_tecnico_autor)) {
                                                    $entry_class = 'entry-tecnico';
                                                }
                                             ?>
                                                <div class="timeline-entry <?php echo $entry_class; ?>">
                                                    <div class="timeline-dot"></div>
                                                    <div class="timeline-bubble">
                                                        <div class="timeline-meta">
                                                            <span class="timeline-user">
                                                                <i class="fas <?php echo ($entry_class == 'entry-tecnico') ? 'fa-user-shield' : 'fa-user'; ?>"></i>
                                                                <?php echo htmlspecialchars($autor ?: 'Sistema'); ?>
                                                            </span>
                                                            <span class="timeline-date"><?php echo htmlspecialchars($nota['data'] ?? ''); ?></span>
                                                        </div>
                                                        <textarea class="timeline-text form-control" name="notas_existentes[<?php echo $idx; ?>]" rows="1" readonly oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"><?php echo htmlspecialchars($nota['texto']); ?></textarea>
                                                        
                                                        <?php if (isset($nota['editado_em'])): ?>
                                                            <div class="timeline-edited">
                                                                <i class="fas fa-pen-nib"></i> <?php echo __('Editado em'); ?> <?php echo htmlspecialchars($nota['editado_em']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (isset($_SESSION['nome_usuario']) && $autor === $_SESSION['nome_usuario']): ?>
                                                            <div class="text-right mt-2">
                                                                <button type="button" class="btn btn-sm btn-link text-warning p-0" onclick="toggleEditNota(this, <?php echo $idx; ?>)">
                                                                    <i class="fas fa-edit"></i> <?php echo __('Editar'); ?>
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                             <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Novo Comentário -->
                                <div class="info-card mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.3s; border: 2px dashed #eaecf4;">
                                    <div class="card-header bg-white">
                                        <i class="fas fa-plus-circle text-primary"></i> <?php echo __('Adicionar Comentário'); ?>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control form-control-premium mb-3" name="nova_nota" rows="3" placeholder="<?php echo __('Descreva o que foi feito ou responda ao solicitante...'); ?>"></textarea>
                                        <div class="text-right">
                                            <button type="submit" class="btn-premium btn-save">
                                                <i class="fas fa-comment"></i> <?php echo __('Enviar Comentário'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Coluna Direita: Metadados e Sidebars -->
                            <div class="col-lg-4">
                                
                                <!-- Detalhes do Chamado -->
                                <div class="info-card mb-4 animate__animated animate__fadeInRight">
                                    <div class="card-header">
                                        <i class="fas fa-info-circle text-primary"></i> <?php echo __('Informações'); ?>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="form-label-premium"><?php echo __('Solicitante'); ?></div>
                                            <div class="form-value"><?php echo htmlspecialchars($chamado['sol_nome'] . ' ' . $chamado['sol_sobrenome']); ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-label-premium"><?php echo __('Categoria'); ?></div>
                                            <div class="form-value"><?php echo htmlspecialchars($chamado['categoria']); ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-label-premium"><?php echo __('Aberto em'); ?></div>
                                            <div class="form-value text-muted small"><i class="fas fa-calendar-alt mr-1"></i> <?php echo date('d/m/Y H:i', strtotime($chamado['data_abertura'])); ?></div>
                                        </div>
                                        <?php if (!empty($chamado['anexo'])): ?>
                                            <div class="mt-4 p-2 bg-light rounded text-center">
                                                <div class="form-label-premium mb-2"><?php echo __('Anexo Enviado'); ?></div>
                                                <?php 
                                                $ext = strtolower(pathinfo($chamado['anexo'], PATHINFO_EXTENSION));
                                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                                    <button type="button" class="btn p-0 border-0 bg-transparent mb-2" onclick="document.getElementById('imgLightbox').style.display='flex'">
                                                        <img src="/<?php echo htmlspecialchars($chamado['anexo']); ?>" class="img-fluid rounded shadow-sm" style="max-height: 100px; cursor: pointer;" alt="<?php echo __('Anexo do chamado'); ?>">
                                                    </button>
                                                    <br><button type="button" class="btn btn-sm btn-link text-primary font-weight-bold p-0" onclick="document.getElementById('imgLightbox').style.display='flex'"><i class="fas fa-search-plus"></i> <?php echo __('Ver Detalhes'); ?></button>
                                                <?php else: ?>
                                                    <a href="/<?php echo htmlspecialchars($chamado['anexo']); ?>" target="_blank" class="btn btn-sm btn-outline-primary btn-block">
                                                        <i class="fas fa-file-download mr-1"></i> <?php echo __('Baixar Arquivo'); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Gestão de Atendimento -->
                                <div class="info-card mb-4 animate__animated animate__fadeInRight" style="animation-delay: 0.1s;">
                                    <div class="card-header">
                                        <i class="fas fa-cog text-warning"></i> <?php echo __('Gestão de Atendimento'); ?>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="responsavel_id" class="form-label-premium"><?php echo __('Técnico Responsável'); ?></label>
                                            <select id="responsavel_id" class="form-control form-control-premium" name="responsavel_id" <?php echo !$is_tecnico ? 'disabled' : ''; ?>>
                                                <option value=""><?php echo __('Não Atribuído'); ?></option>
                                                <?php
                                                if ($result_users && $result_users->num_rows > 0) {
                                                    mysqli_data_seek($result_users, 0);
                                                    while ($u = $result_users->fetch_assoc()) {
                                                        $sel = ($chamado['responsavel_id'] == $u['id_usuarios']) ? 'selected' : '';
                                                        echo '<option value="'.$u['id_usuarios'].'" '.$sel.'>'.htmlspecialchars($u['nome'].' '.$u['sobrenome']).'</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                             <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label for="prioridade" class="form-label-premium mb-0"><?php echo __('Prioridade'); ?></label>
                                                <?php if ($is_tecnico): ?>
                                                <button type="button" class="btn btn-sm btn-link p-0 text-primary font-weight-bold" id="btn-ai-priority" style="text-decoration: none; font-size: 0.7rem;">
                                                    <i class="fas fa-magic mr-1"></i><?php echo __('Sugerir com IA'); ?>
                                                </button>
                                                <?php endif; ?>
                                             </div>
                                             <select id="prioridade" class="form-control form-control-premium" name="prioridade" <?php echo !$is_tecnico ? 'disabled' : ''; ?>>
                                                 <option value="P1" <?php echo ($chamado['prioridade'] == 'P1') ? 'selected' : ''; ?> style="color: #8b0000; font-weight: bold;">P1 - <?php echo __('Crítica (SLA Urgente)'); ?></option>
                                                 <option value="P2" <?php echo ($chamado['prioridade'] == 'P2' || $chamado['prioridade'] == 'Alta') ? 'selected' : ''; ?> style="color: #e74a3b;">P2 - <?php echo __('Alta'); ?></option>
                                                 <option value="P3" <?php echo ($chamado['prioridade'] == 'P3' || $chamado['prioridade'] == 'Média' || empty($chamado['prioridade'])) ? 'selected' : ''; ?> style="color: #f6c23e;">P3 - <?php echo __('Média'); ?></option>
                                                 <option value="P4" <?php echo ($chamado['prioridade'] == 'P4' || $chamado['prioridade'] == 'Baixa') ? 'selected' : ''; ?> style="color: #1cc88a;">P4 - <?php echo __('Baixa'); ?></option>
                                             </select>
                                             <div id="ai-priority-reason" class="small text-info mt-1" style="display: none; line-height: 1.2;"></div>
                                         </div>
                                        <div class="mb-3">
                                            <label for="status" class="form-label-premium"><?php echo __('Alterar Status'); ?></label>
                                            <select id="status" class="form-control form-control-premium" name="status" <?php echo !$is_tecnico ? 'disabled' : ''; ?>>
                                                <option value="Aberto" <?php echo $chamado['status'] == 'Aberto' ? 'selected' : ''; ?>><?php echo __('Aberto'); ?></option>
                                                <option value="Em Andamento" <?php echo ($chamado['status'] == 'Em Andamento' || $chamado['status'] == 'Em Atendimento') ? 'selected' : ''; ?>><?php echo __('Em Andamento'); ?></option>
                                                <option value="Pendente" <?php echo $chamado['status'] == 'Pendente' ? 'selected' : ''; ?>><?php echo __('Pendente'); ?></option>
                                                <option value="Resolvido" <?php echo $chamado['status'] == 'Resolvido' ? 'selected' : ''; ?>><?php echo __('Resolvido'); ?></option>
                                                <option value="Fechado" <?php echo $chamado['status'] == 'Fechado' ? 'selected' : ''; ?>><?php echo __('Fechado'); ?></option>
                                                <option value="Cancelado" <?php echo $chamado['status'] == 'Cancelado' ? 'selected' : ''; ?>><?php echo __('Cancelado'); ?></option>
                                            </select>
                                        </div>
                                        
                                        <!-- Service Tag / Ativo -->
                                        <?php if ($chamado['categoria'] == 'Incidente' || !empty($chamado['service_tag'])): ?>
                                        <div class="mb-0 mt-4">
                                            <label for="service_tag" class="form-label-premium"><?php echo __('Ativo Vinculado'); ?></label>
                                            <div class="input-group-premium">
                                                <div class="px-2 text-muted"><i class="fas fa-tag"></i></div>
                                                <input id="service_tag" type="text" name="service_tag" class="form-control" value="<?php echo htmlspecialchars($chamado['service_tag'] ?? ''); ?>" readonly>
                                                <?php if (!empty($chamado['resolvido_id_asset']) && $is_tecnico): ?>
                                                    <a href="perfil_ativo.php?id=<?php echo $chamado['resolvido_id_asset']; ?>" class="btn-view-asset" title="<?php echo __('Ver Ativo'); ?>" target="_blank">
                                                        <i class="fas fa-desktop"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <input type="hidden" name="id_asset" value="<?php echo $chamado['resolvido_id_asset'] ?? ''; ?>">
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Aprovação do Gestor -->
                                <?php if ($chamado['categoria'] === 'Requisição' || $chamado['categoria'] === 'Mudança'): ?>
                                <div class="info-card approval-card mb-4 animate__animated animate__fadeInRight" style="animation-delay: 0.2s;">
                                    <div class="card-header">
                                        <i class="fas fa-user-tie text-warning"></i> <?php echo __('Aprovação do Gestor'); ?>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="form-label-premium"><?php echo __('Aprovador'); ?></div>
                                            <div class="form-value small"><?php echo htmlspecialchars(($chamado['gestor_nome'] ?? '') . ' ' . ($chamado['gestor_sobrenome'] ?? '')); ?></div>
                                            <input type="hidden" name="id_gestor_aprovador" value="<?php echo $chamado['id_gestor_aprovador'] ?? ''; ?>">
                                        </div>
                                        <div class="text-center pt-2">
                                            <?php if ($chamado['aprovado_gestor']): ?>
                                                <div class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> <?php echo __('APROVADO'); ?></div>
                                            <?php else: ?>
                                                <div class="text-warning font-weight-bold mb-3"><i class="fas fa-clock mr-1"></i> <?php echo __('AGUARDANDO'); ?></div>
                                                <?php if ($id_usuario_logado == $chamado['id_gestor_aprovador']): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="acao_gestor" value="aprovar">
                                                        <button type="submit" class="btn btn-sm btn-success btn-block rounded-pill shadow-sm py-2"><i class="fas fa-thumbs-up mr-1"></i> <?php echo __('Aprovar Agora'); ?></button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- IA Sugestão -->
                                <?php if ($ia_ativo): ?>
                                <div class="info-card ai-suggestion-card mb-4 animate__animated animate__fadeInRight" style="animation-delay: 0.3s;">
                                    <div class="card-header">
                                        <i class="fas fa-robot text-info"></i> <?php echo __('Assistente IA'); ?>
                                    </div>
                                    <div class="card-body">
                                        <div id="ai-suggestion" class="text-dark small" style="line-height: 1.6;">
                                            <div class="text-center py-2">
                                                <div class="spinner-border spinner-border-sm text-info mb-2"></div>
                                                <div class="text-muted small"><?php echo __('Analisando contexto...'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                            </div>
                        </div>

                        <!-- Botões de Ação Fixos / Prominentes -->
                        <div class="row mt-2 mb-5">
                            <div class="col-12 d-flex justify-content-between align-items-center">
                                <a class="btn-premium btn-back" href="chamados.php">
                                    <i class="fas fa-arrow-left"></i> <?php echo __('Voltar'); ?>
                                </a>
                                <button type="submit" class="btn-premium btn-save">
                                    <i class="fas fa-save"></i> <?php echo __('Salvar Todas as Alterações'); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lightbox Modal -->
            <div id="imgLightbox" class="lightbox-modal" onclick="if(event.target===this)this.style.display='none'" onkeyup="if(event.key==='Escape')this.style.display='none'" tabindex="-1">
                <button type="button" class="lightbox-close" onclick="document.getElementById('imgLightbox').style.display='none'" onkeyup="if(event.key==='Enter')document.getElementById('imgLightbox').style.display='none'" style="background:none; border:none; outline:none;" aria-label="<?php echo __('Fechar'); ?>">&times;</button>
                <img src="/<?php echo htmlspecialchars($chamado['anexo'] ?? ''); ?>" style="max-width:90%; max-height:90%; border-radius:15px; box-shadow: 0 20px 50px rgba(0,0,0,0.5);" alt="<?php echo __('Visualização do anexo'); ?>">
            </div>

            <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    
    <script>
        $(document).ready(function () {
            // IA Suggestion Fetch
            <?php if ($ia_ativo): ?>
            const t = <?php echo json_encode($chamado['titulo']); ?>;
            const d = <?php echo json_encode($chamado['descricao']); ?>;
            fetch('agent_chamado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `titulo=${encodeURIComponent(t)}&descricao=${encodeURIComponent(d)}`
            })
            .then(r => r.json())
            .then(data => {
                let txt = data.reply.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
                $('#ai-suggestion').html(txt);
            })
            .catch(() => $('#ai-suggestion').html('⚠️ <?php echo __('Erro ao obter sugestão da IA.'); ?>'));
            <?php endif; ?>
            // Auto-resize existing notes
            $('.timeline-text').each(function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });

        function toggleEditNota(btn, idx) {
            const container = $(btn).closest('.timeline-bubble');
            const area = container.find('textarea');
            if (area.prop('readonly')) {
                area.prop('readonly', false).removeClass('timeline-text').addClass('form-control-premium').focus();
                // Trigger auto-resize to fit content
                area[0].style.height = 'auto';
                area[0].style.height = area[0].scrollHeight + 'px';
                $(btn).html('<i class="fas fa-check"></i> <?php echo __('Pronto'); ?>').removeClass('text-warning').addClass('text-success font-weight-bold');
            } else {
                area.prop('readonly', true).removeClass('form-control-premium').addClass('timeline-text');
                $(btn).html('<i class="fas fa-edit"></i> <?php echo __('Editar'); ?>').removeClass('text-success font-weight-bold').addClass('text-warning');
                // Automatically save and refresh the page
                $('#form-editar-chamado').submit();
            }
        }

        document.addEventListener('keydown', e => { if (e.key === 'Escape') $('#imgLightbox').hide(); });

        // SLA Timer Functionality
        function updateHeaderSLATimer() {
            const container = document.getElementById('sla-header-container');
            if (!container) return;

            const now = Math.floor(Date.now() / 1000);
            const status = container.dataset.status;
            const deadline = parseInt(container.dataset.deadline);
            const slaTotal = parseInt(container.dataset.slaTotal);
            const textElement = document.getElementById('sla-header-text');
            const progressBar = document.getElementById('sla-header-bar');
            
            if (!textElement || !progressBar) return;

            // Helper to format time
            const formatTime = (seconds) => {
                const days = Math.floor(seconds / 86400);
                const hours = Math.floor((seconds % 86400) / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                const secs = seconds % 60;
                if (days > 0) return `${days}d ${hours}h`;
                if (hours > 0) return `${hours}h ${minutes}m`;
                return `${minutes}m ${secs}s`;
            };

            // If concluded or cancelled, don't show dynamic timer
            if (status === 'Resolvido' || status === 'Fechado' || status === 'Cancelado') {
                textElement.textContent = '<?php echo __("CONCLUÍDO"); ?>';
                progressBar.style.width = '100%';
                progressBar.className = 'progress-bar bg-success';
                return;
            }

            let diff = deadline - now;
            let statusText = '';
            let barClass = 'bg-success';
            
            if (status === 'Pendente') {
                statusText = '<?php echo __("CONGELADO"); ?>';
                barClass = 'bg-secondary';
            } else if (diff <= 0) {
                statusText = '<?php echo __("VENCIDO"); ?>';
                diff = Math.abs(diff);
                barClass = 'bg-danger';
            } else {
                if (diff <= (slaTotal * 0.2)) { // Less than 20% time remaining (80% used)
                    statusText = '<?php echo __("ATENÇÃO"); ?>';
                    barClass = 'bg-warning';
                } else {
                    statusText = '<?php echo __("NO PRAZO"); ?>';
                }
            }

            textElement.textContent = `${statusText} (${formatTime(Math.abs(diff))})`;
            progressBar.className = `progress-bar progress-bar-striped progress-bar-animated ${barClass}`;
            
            let percentage = 100;
            if (slaTotal > 0) {
                const usedSeconds = slaTotal - (deadline - now);
                percentage = Math.min(100, Math.max(0, (usedSeconds / slaTotal) * 100));
            }
            if (deadline - now < 0 && status !== 'Pendente') percentage = 100;
            progressBar.style.width = percentage + '%';
        }

        setInterval(updateHeaderSLATimer, 1000);
        updateHeaderSLATimer();
    </script>
    <script src="/assets/js/global_search.js"></script>
</body>

</html>