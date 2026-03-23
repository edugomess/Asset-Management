<?php
/**
 * DETALHES E EDIÇÃO DE CHAMADO: editar_chamado.php
 * Interface para técnicos e usuários visualizarem e atualizarem tickets.
 * Gerencia status, atribuição de responsável, SLA (congelamento) e histórico de notas.
 */
include_once 'auth.php'; // Proteção de sessão
include_once 'conexao.php'; // Banco de Dados

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
    if ($conn->query($sql_aprov) === TRUE) {
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

        if ($conn->query($sql_update) === TRUE) {
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
        .lightbox-modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:9999; justify-content:center; align-items:center; }
        .lightbox-close { position:absolute; top:20px; right:30px; color:#fff; font-size:35px; cursor:pointer; z-index:10000; line-height:1; }
        .nota-historico { border: 1px solid #e3e6f0; border-radius: 8px; padding: 12px; background: #f8f9fc; position: relative; transition: all 0.2s ease; }
        .nota-solicitante { background-color: #f0f7ff !important; border-left: 5px solid #007bff !important; border-top-left-radius: 0; border-bottom-left-radius: 0; }
        .nota-tecnico { background-color: #f0fff4 !important; border-left: 5px solid #28a745 !important; border-top-left-radius: 0; border-bottom-left-radius: 0; }
        .nota-historico textarea { transition: background 0.2s ease; }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include_once 'sidebar_brand.php'; ?>
                <?php include_once 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <?php include_once 'topbar.php'; ?>
                <div class="container-fluid">
                    <h3 class="text-dark mb-1"><?php echo __('Detalhes do Chamado'); ?> #<?php echo $chamado['id']; ?></h3>
                    <div class="text-center mb-3"><?php echo $msg; ?></div>

                    <div class="card shadow">
                        <div class="card-body">
                            <form method="post" id="form-editar-chamado">
                                <!-- Row 1: Identificação Principal -->
                                <div class="row mb-3">
                                    <div class="col-md-9">
                                        <div class="form-group mb-0">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Assunto / Título'); ?></label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($chamado['titulo']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Categoria'); ?></label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($chamado['categoria']); ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 2: Solicitante e Cronologia -->
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Solicitante'); ?></label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($chamado['sol_nome'] . ' ' . $chamado['sol_sobrenome']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Data de Abertura'); ?></label>
                                            <input type="text" class="form-control" value="<?php echo date('d/m/Y H:i', strtotime($chamado['data_abertura'])); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Prioridade'); ?></label>
                                            <select class="form-control" name="prioridade" <?php echo !$is_tecnico ? 'disabled' : ''; ?>>
                                                <option value="Baixa" <?php echo ($chamado['prioridade'] == 'Baixa') ? 'selected' : ''; ?>><?php echo __('Baixa'); ?></option>
                                                <option value="Média" <?php echo ($chamado['prioridade'] == 'Média' || empty($chamado['prioridade'])) ? 'selected' : ''; ?>><?php echo __('Média'); ?></option>
                                                <option value="Alta" <?php echo ($chamado['prioridade'] == 'Alta') ? 'selected' : ''; ?>><?php echo __('Alta'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label class="text-gray-600 small font-weight-bold"><i class="fas fa-paperclip"></i> <?php echo __('Anexo / Evidência'); ?></label>
                                            <?php if (!empty($chamado['anexo'])): ?>
                                                <div class="d-flex align-items-center bg-light p-1 rounded border" style="height: 38px;">
                                                    <?php 
                                                    $ext = strtolower(pathinfo($chamado['anexo'], PATHINFO_EXTENSION));
                                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])): ?>
                                                        <i class="fas fa-image text-primary mx-2"></i>
                                                        <small class="text-primary font-weight-bold" style="cursor: pointer;" onclick="document.getElementById('imgLightbox').style.display='flex'">
                                                            <?php echo __('Ver Imagem'); ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <a href="/<?php echo htmlspecialchars($chamado['anexo']); ?>" target="_blank" class="text-primary small font-weight-bold ml-2">
                                                            <i class="fas fa-file-download mr-1"></i> <?php echo __('Baixar'); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <input type="text" class="form-control" value="<?php echo __('Nenhum'); ?>" readonly>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 3: Atribuição, Status e Ativo -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Responsável Técnico'); ?></label>
                                            <select class="form-control" name="responsavel_id" <?php echo !$is_tecnico ? 'disabled' : ''; ?>>
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
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Status Atual'); ?></label>
                                            <select class="form-control" name="status" <?php echo !$is_tecnico ? 'disabled' : ''; ?>>
                                                <option value="Aberto" <?php echo $chamado['status'] == 'Aberto' ? 'selected' : ''; ?>><?php echo __('Aberto'); ?></option>
                                                <option value="Em Andamento" <?php echo ($chamado['status'] == 'Em Andamento' || $chamado['status'] == 'Em Atendimento') ? 'selected' : ''; ?>><?php echo __('Em Andamento'); ?></option>
                                                <option value="Pendente" <?php echo $chamado['status'] == 'Pendente' ? 'selected' : ''; ?>><?php echo __('Pendente'); ?></option>
                                                <option value="Resolvido" <?php echo $chamado['status'] == 'Resolvido' ? 'selected' : ''; ?>><?php echo __('Resolvido'); ?></option>
                                                <option value="Fechado" <?php echo $chamado['status'] == 'Fechado' ? 'selected' : ''; ?>><?php echo __('Fechado'); ?></option>
                                                <option value="Cancelado" <?php echo $chamado['status'] == 'Cancelado' ? 'selected' : ''; ?>><?php echo __('Cancelado'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="row" id="row-service-tag" <?php echo ($chamado['categoria'] != 'Incidente' && empty($chamado['service_tag'])) ? 'style="display:none;"' : ''; ?>>
                                            <div class="col-md-12">
                                                <div class="form-group mb-0">
                                                    <label class="text-gray-600 small font-weight-bold"><?php echo __('Service Tag / Ativo'); ?></label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" name="service_tag" class="form-control" value="<?php echo htmlspecialchars($chamado['service_tag'] ?? ''); ?>" readonly>
                                                        <?php if (!empty($chamado['resolvido_id_asset']) && $is_tecnico): ?>
                                                            <div class="input-group-append">
                                                                <a href="perfil_ativo.php?id=<?php echo $chamado['resolvido_id_asset']; ?>" class="btn btn-info" title="<?php echo __('Ver Ativo'); ?>" target="_blank">
                                                                    <i class="fas fa-desktop"></i>
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <input type="hidden" name="id_asset" value="<?php echo $chamado['resolvido_id_asset'] ?? ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Manager Approval Field -->
                                <?php if ($chamado['categoria'] === 'Requisição' || $chamado['categoria'] === 'Mudança'): ?>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="card border-left-primary shadow-sm bg-white">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-md-auto">
                                                        <i class="fas fa-user-tie fa-2x text-primary mr-3"></i>
                                                    </div>
                                                    <div class="col">
                                                        <label class="text-gray-600 small font-weight-bold mb-1 d-block"><?php echo __('Gestor para Aprovação'); ?></label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars(($chamado['gestor_nome'] ?? '') . ' ' . ($chamado['gestor_sobrenome'] ?? '')); ?>" readonly>
                                                            <input type="hidden" name="id_gestor_aprovador" value="<?php echo $chamado['id_gestor_aprovador'] ?? ''; ?>">
                                                        </div>
                                                        <?php if (!empty($chamado['gestor_email'])): ?>
                                                            <small class="text-muted"><?php echo $chamado['gestor_email']; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-auto mt-2 mt-md-0">
                                                        <?php if ($chamado['aprovado_gestor']): ?>
                                                            <span class="badge badge-success p-2"><i class="fas fa-check-circle mr-1"></i> <?php echo __('APROVADO'); ?></span>
                                                        <?php else: ?>
                                                            <span class="badge badge-warning p-2"><i class="fas fa-clock mr-1"></i> <?php echo __('AGUARDANDO APROVAÇÃO'); ?></span>
                                                            <?php if ($id_usuario_logado == $chamado['id_gestor_aprovador']): ?>
                                                                <form method="POST" style="display:inline;" class="ml-2">
                                                                    <input type="hidden" name="acao_gestor" value="aprovar">
                                                                    <button type="submit" class="btn btn-sm btn-success border-0 shadow-sm"><i class="fas fa-thumbs-up mr-1"></i> <?php echo __('Aprovar Agora'); ?></button>
                                                                </form>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Sugestão de IA -->
                                <?php if ($ia_ativo): ?>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <div class="card bg-light border-left-info shadow-sm">
                                                <div class="card-body py-2">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-magic fa-lg text-info mr-3"></i>
                                                        <div>
                                                            <div class="small text-info font-weight-bold text-uppercase"><?php echo __('Sugestão da IA'); ?></div>
                                                            <div id="ai-suggestion" class="text-dark small">
                                                                <span class="spinner-border spinner-border-sm text-info"></span> <?php echo __('Analisando chamado...'); ?>
                                                            </div>
                                                       </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Descrição Detalhada -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Descrição do Problema'); ?></label>
                                            <textarea class="form-control" rows="4" readonly style="background: #f8f9fc;"><?php echo htmlspecialchars($chamado['descricao']); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- Histórico de Notas -->
                                <div class="row">
                                    <div class="col-12">
                                        <label class="text-gray-600 small font-weight-bold"><strong><?php echo __('Histórico de Ações / Notas de Resolução'); ?></strong></label>
                                        <div id="historico-notas">
                                            <?php
                                            $notas = [];
                                            if (!empty($chamado['nota_resolucao'])) {
                                                $dec = json_decode($chamado['nota_resolucao'], true);
                                                if (is_array($dec)) $notas = $dec;
                                            }
                                            
                                            foreach ($notas as $idx => $nota): 
                                                $nome_sol = ($chamado['sol_nome'] ?? '') . ' ' . ($chamado['sol_sobrenome'] ?? '');
                                                $nome_resp = ($chamado['resp_nome'] ?? '') . ' ' . ($chamado['resp_sobrenome'] ?? '');
                                                $autor = $nota['usuario'] ?? '';
                                                
                                                $class_extra = '';
                                                if ($autor === $nome_sol) $class_extra = 'nota-solicitante';
                                                elseif ($autor === $nome_resp) $class_extra = 'nota-tecnico';
                                                
                                                $bg_textarea = ($class_extra !== '') ? 'bg-transparent' : 'bg-light';
                                             ?>
                                                <div class="nota-historico mb-2 shadow-sm border <?php echo $class_extra; ?>">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <small class="text-muted">
                                                            <i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($autor ?: 'Sistema'); ?>
                                                            &mdash; <i class="fas fa-clock mx-1"></i><?php echo htmlspecialchars($nota['data'] ?? ''); ?>
                                                            <?php if (!empty($nota['editado_em'])): ?>
                                                                <span class="badge badge-warning ml-2 font-italic"><i class="fas fa-edit"></i> <?php echo __('Editado'); ?></span>
                                                            <?php endif; ?>
                                                        </small>
                                                        <?php if (isset($_SESSION['nome_usuario']) && $autor === $_SESSION['nome_usuario']): ?>
                                                            <button type="button" class="btn btn-link text-warning p-0" onclick="toggleEditNota(this, <?php echo $idx; ?>)">
                                                                <i class="fas fa-edit"></i> <?php echo __('Editar'); ?>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                    <textarea class="form-control <?php echo $bg_textarea; ?> border-0" name="notas_existentes[<?php echo $idx; ?>]" rows="2" readonly><?php echo htmlspecialchars($nota['texto']); ?></textarea>
                                                </div>
                                             <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Adicionar Nota -->
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="p-3 bg-white border rounded" style="border: 2px dashed #d1d3e2 !important;">
                                            <label class="font-weight-bold text-primary mb-1"><i class="fas fa-plus-circle mr-1"></i> <?php echo __('Adicionar Comentário ou Ação Próxima'); ?></label>
                                            <textarea class="form-control mb-2" name="nova_nota" rows="3" placeholder="<?php echo __('Descreva o que foi feito ou o diagnóstico...'); ?>"></textarea>
                                            <div class="text-right">
                                                <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                                                    <i class="fas fa-comment mr-1"></i> <?php echo __('Comentar'); ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="row mt-4 mb-3">
                                    <div class="col-12 d-flex justify-content-end align-items-center" style="gap: 15px;">
                                        <a class="btn btn-secondary" href="chamados.php" 
                                            style="border-radius: 10px; padding: 10px 30px; border: none; background: #858796; font-weight: 600;">
                                            <?php echo __('Voltar'); ?>
                                        </a>
                                        <button class="btn btn-success active pulse animated" type="submit" 
                                            style="background: #2c404a; border-radius: 10px; padding: 10px 30px; border: none; font-weight: 600;">
                                            <i class="fas fa-save mr-2"></i><?php echo __('Salvar Alterações'); ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lightbox Modal -->
            <div id="imgLightbox" class="lightbox-modal" onclick="if(event.target===this)this.style.display='none'">
                <span class="lightbox-close" onclick="document.getElementById('imgLightbox').style.display='none'">&times;</span>
                <img src="/<?php echo htmlspecialchars($chamado['anexo'] ?? ''); ?>" style="max-width:90%; max-height:90%; border-radius:8px; shadow: 0 5px 15px rgba(0,0,0,.5);">
            </div>

        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
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
        });

        function toggleEditNota(btn, idx) {
            const container = $(btn).closest('.nota-historico');
            const area = container.find('textarea');
            if (area.prop('readonly')) {
                area.prop('readonly', false).removeClass('bg-light').addClass('bg-white border').focus();
                $(btn).html('<i class="fas fa-check"></i> <?php echo __('Pronto'); ?>').removeClass('text-warning').addClass('text-success font-weight-bold');
            } else {
                area.prop('readonly', true).removeClass('bg-white border').addClass('bg-light');
                $(btn).html('<i class="fas fa-edit"></i> <?php echo __('Editar'); ?>').removeClass('text-success font-weight-bold').addClass('text-warning');
            }
        }

        document.addEventListener('keydown', e => { if (e.key === 'Escape') $('#imgLightbox').hide(); });
    </script>
    <script src="/assets/js/global_search.js"></script>
</body>

</html>