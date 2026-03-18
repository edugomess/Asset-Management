<?php
include 'auth.php';
include 'conexao.php';

$id_chamado = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = '';

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['status']) || isset($_POST['nova_nota']) || isset($_POST['notas_existentes']))) {
    // Busca os valores atuais do banco como base (caso campos venham desabilitados no POST)
    $sql_atual = "SELECT status, responsavel_id, prioridade FROM chamados WHERE id = $id_chamado";
    $res_atual = $conn->query($sql_atual);
    $chamado_base = $res_atual->fetch_assoc();

    $novo_status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : $chamado_base['status'];
    $responsavel_id = isset($_POST['responsavel_id']) ? (!empty($_POST['responsavel_id']) ? intval($_POST['responsavel_id']) : 'NULL') : (!empty($chamado_base['responsavel_id']) ? intval($chamado_base['responsavel_id']) : 'NULL');
    $prioridade = isset($_POST['prioridade']) ? mysqli_real_escape_string($conn, $_POST['prioridade']) : $chamado_base['prioridade'];

    // Restrição de Segurança: Se não for Admin/Suporte, ignora alterações de status, responsável e prioridade
    // mesmo que tentem enviar via ferramentas de desenvolvedor (já coberto acima pela lógica de base, mas mantendo para clareza)
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
                // Normaliza quebras de linha para comparação confiável
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
        $msg = '<div class="alert alert-danger"><strong>Erro:</strong> Para alterar o status (sair de "Aberto"), é obrigatório atribuir um <strong>Responsável</strong> ao chamado.</div>';
    } else {
        // Buscar status atual para lógica de congelamento
        $sql_query_atual = "SELECT status, data_ultimo_congelamento FROM chamados WHERE id = $id_chamado";
        $res_atual = $conn->query($sql_query_atual);
        $chamado_atual = $res_atual->fetch_assoc();

        // LÓGICA DE CONGELAMENTO DE SLA: Pausa o contador quando o chamado está "Pendente"
        $congelamento_sql = "";
        $status_anterior = $chamado_atual['status'];

        // Se mudou PARA Pendente, registra o início do congelamento
        if ($status_anterior !== 'Pendente' && $novo_status === 'Pendente') {
            $congelamento_sql = ", data_ultimo_congelamento = NOW()";
        }
        // Se saiu de Pendente, calcula o tempo e acumula os minutos congelados
        elseif ($status_anterior === 'Pendente' && $novo_status !== 'Pendente') {
            $data_inicio = $chamado_atual['data_ultimo_congelamento'];
            if (!empty($data_inicio)) {
                $minutos_adicionais = "TIMESTAMPDIFF(MINUTE, '$data_inicio', NOW())";
                $congelamento_sql = ", tempo_congelado_minutos = tempo_congelado_minutos + $minutos_adicionais, data_ultimo_congelamento = NULL";
            }
        }

        // Lógica para data_fechamento (Restaurada)
        $fechamento_sql = "";
        $status_fechados = ['Resolvido', 'Fechado', 'Cancelado'];
        if (in_array($novo_status, $status_fechados)) {
            $fechamento_sql = ", data_fechamento = NOW()";
        } else {
            $fechamento_sql = ", data_fechamento = NULL";
        }

        $sql_update = "UPDATE chamados SET status = '$novo_status', responsavel_id = $responsavel_id, prioridade = '$prioridade', nota_resolucao = '$nota_resolucao' $fechamento_sql $congelamento_sql WHERE id = $id_chamado";

        if ($conn->query($sql_update) === TRUE) {
            $msg = '<div class="alert alert-success">Chamado atualizado com sucesso! <a href="chamados.php">Voltar para lista</a></div>';
            // Atualizar objeto $chamado para refletir mudanças se necessário (ou redirecionar)
            $chamado['status'] = $novo_status;
        } else {
            $msg = '<div class="alert alert-danger">Erro ao atualizar: ' . $conn->error . '</div>';
        }
    }
}

// Buscar dados do chamado (Solicitante e Responsável)
$sql = "SELECT c.*, 
               u.nome AS sol_nome, u.sobrenome AS sol_sobrenome,
               r.nome AS resp_nome, r.sobrenome AS resp_sobrenome 
        FROM chamados c 
        LEFT JOIN usuarios u ON c.usuario_id = u.id_usuarios 
        LEFT JOIN usuarios r ON c.responsavel_id = r.id_usuarios
        WHERE c.id = $id_chamado";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Chamado não encontrado.");
}
$chamado = $result->fetch_assoc();

// Buscar lista de usuários para Responsável (Apenas Admin e Suporte)
$sql_users = "SELECT id_usuarios, nome, sobrenome FROM usuarios WHERE nivelUsuario IN ('Admin', 'Suporte') ORDER BY nome";
$result_users = $conn->query($sql_users);

$is_tecnico = ($_SESSION['nivelUsuario'] === 'Admin' || $_SESSION['nivelUsuario'] === 'Suporte');

// Buscar configurações de IA (Geral e Chamados)
$sql_config_ia = "SELECT ia_agente_ativo, ia_chamados_ativo FROM configuracoes_alertas LIMIT 1";
$res_config_ia = mysqli_query($conn, $sql_config_ia);
$ia_geral_ativo = true;
$ia_chamados_ativo = true;
if ($res_config_ia && mysqli_num_rows($res_config_ia) > 0) {
    $row_ia = mysqli_fetch_assoc($res_config_ia);
    $ia_geral_ativo = (bool) ($row_ia['ia_agente_ativo'] ?? 1);
    $ia_chamados_ativo = (bool) ($row_ia['ia_chamados_ativo'] ?? 1);
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Editar Chamado #<?php echo $chamado['id']; ?></title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=3265483e434712d72c41db9eebc4c8bb">
    <link rel="stylesheet" href="/assets/fonts/fontawesome-all.min.css">
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar and Navbar would go here, omitting for brevity in this specific file but keeping structure similar to template -->

        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content" style="padding: 20px;">
                <div class="container-fluid">
                    <h3 class="text-dark mb-4">Detalhes do Chamado #<?php echo $chamado['id']; ?></h3>
                    <div class="text-center"><?php echo $msg; ?></div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Informações</h6>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="text-gray-600 small font-weight-bold">Título</label>
                                        <input type="text" class="form-control"
                                            value="<?php echo htmlspecialchars($chamado['titulo']); ?>" readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="text-gray-600 small font-weight-bold">Categoria</label>
                                        <input type="text" class="form-control"
                                            value="<?php echo htmlspecialchars($chamado['categoria']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="text-gray-600 small font-weight-bold">Solicitante</label>
                                        <input type="text" class="form-control"
                                            value="<?php echo htmlspecialchars($chamado['sol_nome'] . ' ' . $chamado['sol_sobrenome']); ?>"
                                            readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="text-gray-600 small font-weight-bold">Data Abertura</label>
                                        <input type="text" class="form-control"
                                            value="<?php echo date('d/m/Y H:i', strtotime($chamado['data_abertura'])); ?>"
                                            readonly>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label class="text-gray-600 small font-weight-bold">Prioridade</label>
                                        <select class="form-control" name="prioridade" <?php echo !$is_tecnico ? 'disabled' : ''; ?>>
                                            <option value="Baixa" <?php echo ($chamado['prioridade'] ?? 'Média') == 'Baixa' ? 'selected' : ''; ?>>Baixa</option>
                                            <option value="Média" <?php echo ($chamado['prioridade'] ?? 'Média') == 'Média' ? 'selected' : ''; ?>>Média</option>
                                            <option value="Alta" <?php echo ($chamado['prioridade'] ?? 'Média') == 'Alta' ? 'selected' : ''; ?>>Alta</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="text-gray-600 small font-weight-bold">Descrição</label>
                                    <textarea id="chamado-descricao" class="form-control" rows="5"
                                        readonly><?php echo htmlspecialchars($chamado['descricao']); ?></textarea>
                                </div>

                                <!-- SUGESTÃO DE IA -->
                                <?php if ($ia_geral_ativo && $ia_chamados_ativo): ?>
                                    <div class="form-group mt-3">
                                        <div class="card bg-light border-left-info shadow-sm">
                                            <div class="card-body py-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="mr-3">
                                                        <i class="fas fa-magic fa-lg text-info"></i>
                                                    </div>
                                                    <div>
                                                        <div class="small text-info font-weight-bold text-uppercase">
                                                            Sugestão de Ação (IA)</div>
                                                        <div id="ai-suggestion-text" class="text-dark small">
                                                            <span class="spinner-border spinner-border-sm text-info"
                                                                role="status"></span> Analisando chamado...
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($chamado['anexo'])): ?>
                                    <div class="form-group">
                                        <label class="text-gray-600 small font-weight-bold"><i class="fas fa-paperclip"></i> Anexo</label>
                                        <div
                                            style="border: 1px solid #e3e6f0; border-radius: 8px; padding: 15px; background: #f8f9fc; display: flex; align-items: center; gap: 12px;">
                                            <?php
                                            $ext = strtolower(pathinfo($chamado['anexo'], PATHINFO_EXTENSION));
                                            $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp']);
                                            if ($is_image): ?>
                                                <img src="/<?php echo htmlspecialchars($chamado['anexo']); ?>"
                                                    style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #dee2e6; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;"
                                                    alt="Anexo do chamado"
                                                    onclick="document.getElementById('imageModal').style.display='flex'"
                                                    onmouseover="this.style.transform='scale(1.08)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.25)'"
                                                    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'"
                                                    title="Clique para expandir">
                                                <small class="text-muted"><i class="fas fa-search-plus"></i> Clique para
                                                    expandir</small>

                                                <!-- Image Lightbox Modal -->
                                                <div id="imageModal" onclick="if(event.target===this)this.style.display='none'"
                                                    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:9999; justify-content:center; align-items:center;">
                                                    <span onclick="document.getElementById('imageModal').style.display='none'"
                                                        style="position:absolute; top:20px; right:30px; color:#fff; font-size:35px; cursor:pointer; z-index:10000; line-height:1;">&times;</span>
                                                    <img src="/<?php echo htmlspecialchars($chamado['anexo']); ?>"
                                                        style="max-width:90%; max-height:90%; border-radius:8px; box-shadow:0 8px 32px rgba(0,0,0,0.5);"
                                                        alt="Anexo expandido">
                                                </div>
                                                <?php
                                            else: ?>
                                                <a href="/<?php echo htmlspecialchars($chamado['anexo']); ?>" target="_blank"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i
                                                        class="fas fa-<?php echo ($ext === 'pdf') ? 'file-pdf' : 'file-word'; ?>"></i>
                                                    Baixar Anexo (<?php echo strtoupper($ext); ?>)
                                                </a>
                                                <?php
                                            endif; ?>
                                        </div>
                                    </div>
                                    <?php
                                endif; ?>

                                <div class="form-group">
                                    <label class="text-gray-600 small font-weight-bold"><strong>Notas de Resolução</strong> <small class="text-muted">(Registre aqui
                                            as ações realizadas para resolver o chamado)</small></label>
                                    <?php
                                    $notas_display = [];
                                    $raw = $chamado['nota_resolucao'] ?? '';
                                    if (!empty($raw)) {
                                        $dec = json_decode($raw, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) {
                                            $notas_display = $dec;
                                        } else {
                                            // Fallback para nomes caso a nota seja legada (texto simples)
                                            $autor_legacy = 'Sistema';
                                            if (!empty($chamado['resp_nome'])) {
                                                $autor_legacy = $chamado['resp_nome'] . ' ' . $chamado['resp_sobrenome'];
                                            } elseif (!empty($chamado['sol_nome'])) {
                                                $autor_legacy = $chamado['sol_nome'] . ' ' . $chamado['sol_sobrenome'];
                                            }
                                            $notas_display = [['texto' => $raw, 'data' => date('d/m/Y H:i', strtotime($chamado['data_abertura'])), 'usuario' => $autor_legacy]];
                                        }
                                    }
                                    if (count($notas_display) > 0):
                                        foreach ($notas_display as $idx => $nota_item): ?>
                                            <div class="nota-historico mb-2"
                                                style="border: 1px solid #e3e6f0; border-radius: 8px; padding: 12px; background: #f8f9fc; position: relative;">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small class="text-muted">
                                                        <i class="fas fa-user"></i>
                                                        <?php
                                                        $autor_display = $nota_item['usuario'] ?? 'Sistema';
                                                        if ($autor_display === 'Sistema' && !empty($chamado['resp_nome'])) {
                                                            $autor_display = $chamado['resp_nome'] . ' ' . $chamado['resp_sobrenome'];
                                                        }
                                                        echo htmlspecialchars($autor_display);
                                                        ?>
                                                        &mdash; <i class="fas fa-clock"></i>
                                                        <?php echo htmlspecialchars($nota_item['data'] ?? ''); ?>
                                                        <?php if (!empty($nota_item['editado_em'])): ?>
                                                            <span class="badge badge-warning ml-1"
                                                                style="font-size: 85%; font-weight: 600; font-style: italic;">
                                                                <!-- Busca Global -->
                        <form class="form-inline d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search position-relative">
                            <div class="input-group">
                                <input class="bg-light form-control border-0 small" type="text" placeholder="Pesquisar..." id="globalSearchInput" autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" style="background: rgb(44,64,74); border: none;">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="globalSearchResults" class="dropdown-menu shadow animated--grow-in" style="width: 100%; display: none;"></div>
                        </form>
                        <ul class="navbar-nav flex-nowrap ml-auto"></i> Editado em
                                                                <?php echo htmlspecialchars($nota_item['editado_em']); ?>
                                                            </span>
                                                            <?php
                                                        endif; ?>
                                                    </small>
                                                    <?php if (isset($_SESSION['nome_usuario']) && ($nota_item['usuario'] ?? '') === $_SESSION['nome_usuario']): ?>
                                                        <button type="button" class="btn btn-outline-warning btn-sm"
                                                            onclick="toggleEditNota(this, <?php echo $idx; ?>)" title="Editar nota">
                                                            <i class="fas fa-edit"></i> Editar
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                                <textarea class="form-control" name="notas_existentes[<?php echo $idx; ?>]"
                                                    rows="2" readonly
                                                    style="background: #eaecf4; cursor: default;"><?php echo htmlspecialchars($nota_item['texto']); ?></textarea>
                                            </div>
                                            <?php
                                        endforeach;
                                    endif; ?>

                                    <!-- Nova nota -->
                                    <div class="mt-2"
                                        style="border: 2px dashed #b7c2d0; border-radius: 8px; padding: 12px; background: #fff;">
                                        <label class="mb-1"><i class="fas fa-plus-circle text-primary"></i>
                                            <strong>Adicionar Nova Nota</strong></label>
                                        <textarea class="form-control mb-2" name="nova_nota" rows="3"
                                            placeholder="Descreva as ações tomadas, a solução aplicada ou observações relevantes..."></textarea>
                                        <div class="text-right">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-comment"></i> Comentar
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <strong>Alterar Status</strong>
                                </div>
                                <div class="form-row align-items-end">
                                    <div class="form-group col-md-4">
                                        <label class="text-gray-600 small font-weight-bold" for="status">Status Atual</label>
                                        <select class="form-control" name="status" id="status" <?php echo !$is_tecnico ? 'disabled' : ''; ?>>
                                            <option value="Aberto" <?php if ($chamado['status'] == 'Aberto')
                                                echo 'selected'; ?>>Aberto</option>
                                            <option value="Em Andamento" <?php if ($chamado['status'] == 'Em Andamento')
                                                echo 'selected'; ?>>Em Andamento</option>
                                            <option value="Pendente" <?php if ($chamado['status'] == 'Pendente')
                                                echo 'selected'; ?>>Pendente</option>
                                            <option value="Resolvido" <?php if ($chamado['status'] == 'Resolvido')
                                                echo 'selected'; ?>>Resolvido</option>
                                            <option value="Cancelado" <?php if ($chamado['status'] == 'Cancelado')
                                                echo 'selected'; ?>>Cancelado</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="text-gray-600 small font-weight-bold" for="responsavel">Atribuir Responsável</label>
                                        <select class="form-control" name="responsavel_id" id="responsavel" <?php echo !$is_tecnico ? 'disabled' : ''; ?>>
                                            <option value="">-- Selecione --</option>
                                            <?php
                                            if ($result_users->num_rows > 0) {
                                                while ($user = $result_users->fetch_assoc()) {
                                                    $selected = ($chamado['responsavel_id'] == $user['id_usuarios']) ? 'selected' : '';
                                                    echo '<option value="' . $user['id_usuarios'] . '" ' . $selected . '>' . htmlspecialchars($user['nome'] . ' ' . $user['sobrenome']) . '</option>';
                                                }
                                                // Reset pointer if needed, but not needed here as we query once
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <button type="submit" class="btn btn-primary btn-block">Salvar</button>
                                    </div>
                                    <?php if (!empty($msg)): ?>
                                        <div class="form-group col-md-2">
                                            <a href="chamados.php" class="btn btn-success btn-block">Voltar para lista</a>
                                        </div>
                                        <?php
                                    else: ?>
                                        <div class="form-group col-md-2">
                                            <a href="chamados.php" class="btn btn-secondary btn-block">Voltar</a>
                                        </div>
                                        <?php
                                    endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            // Carregar sugestão da IA para o chamado
            const titulo = "<?php echo addslashes($chamado['titulo']); ?>";
            const descricao = <?php echo json_encode($chamado['descricao']); ?>;

            fetch('agent_chamado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `titulo=${encodeURIComponent(titulo)}&descricao=${encodeURIComponent(descricao)}`
            })
                .then(response => response.json())
                .then(data => {
                    const textContainer = document.getElementById('ai-suggestion-text');
                    let reply = data.reply.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                    reply = reply.replace(/\n/g, '<br>');
                    textContainer.innerHTML = reply;
                })
                .catch(error => {
                    document.getElementById('ai-suggestion-text').innerHTML = '⚠️ Não foi possível obter sugestão da IA.';
                });
        });

        function toggleEditNota(btn, idx) {
            var container = btn.closest('.nota-historico');
            var textarea = container.querySelector('textarea');

            if (textarea.readOnly) {
                // Modo Edição
                textarea.readOnly = false;
                textarea.style.background = '#fff';
                textarea.style.cursor = 'text';
                textarea.focus();
                btn.innerHTML = '<i class="fas fa-check"></i> Pronto';
                btn.classList.remove('btn-outline-warning');
                btn.classList.add('btn-success', 'text-white');
            } else {
                // Modo Visualização
                textarea.readOnly = true;
                textarea.style.background = '#eaecf4';
                textarea.style.cursor = 'default';
                btn.innerHTML = '<i class="fas fa-edit"></i> Editar';
                btn.classList.remove('btn-success', 'text-white');
                btn.classList.add('btn-outline-warning');
            }
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                var modal = document.getElementById('imageModal');
                if (modal) modal.style.display = 'none';
            }
        });
    </script>
        <script src="/assets/js/global_search.js"></script>
</body>

</html>