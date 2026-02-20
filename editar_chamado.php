<?php
include 'auth.php';
include 'conexao.php';

$id_chamado = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = '';

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status'])) {
    $novo_status = mysqli_real_escape_string($conn, $_POST['status']); // Update POST handler
    $responsavel_id = !empty($_POST['responsavel_id']) ? intval($_POST['responsavel_id']) : 'NULL';
    $prioridade = isset($_POST['prioridade']) ? mysqli_real_escape_string($conn, $_POST['prioridade']) : 'Média';
    // Processar notas de resolução como histórico (JSON)
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
                $novo_texto = trim($texto);
                // Só marca como editado se o texto realmente mudou
                if ($notas_array[$i]['texto'] !== $novo_texto) {
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
        // Lógica para data_fechamento
        $fechamento_sql = "";
        $status_fechados = ['Resolvido', 'Fechado', 'Cancelado'];

        if (in_array($novo_status, $status_fechados)) {
            $fechamento_sql = ", data_fechamento = NOW()";
        } else {
            $fechamento_sql = ", data_fechamento = NULL";
        }

        $sql_update = "UPDATE chamados SET status = '$novo_status', responsavel_id = $responsavel_id, prioridade = '$prioridade', nota_resolucao = '$nota_resolucao' $fechamento_sql WHERE id = $id_chamado";

        if ($conn->query($sql_update) === TRUE) {
            $msg = '<div class="alert alert-success">Chamado atualizado com sucesso! <a href="chamados.php">Voltar para lista</a></div>';
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

// Buscar lista de usuários para Responsável
$sql_users = "SELECT id_usuarios, nome, sobrenome FROM usuarios ORDER BY nome";
$result_users = $conn->query($sql_users);

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
                                        <label>Título</label>
                                        <input type="text" class="form-control"
                                            value="<?php echo htmlspecialchars($chamado['titulo']); ?>" readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Categoria</label>
                                        <input type="text" class="form-control"
                                            value="<?php echo htmlspecialchars($chamado['categoria']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Solicitante</label>
                                        <input type="text" class="form-control"
                                            value="<?php echo htmlspecialchars($chamado['sol_nome'] . ' ' . $chamado['sol_sobrenome']); ?>"
                                            readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Data Abertura</label>
                                        <input type="text" class="form-control"
                                            value="<?php echo date('d/m/Y H:i', strtotime($chamado['data_abertura'])); ?>"
                                            readonly>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Prioridade</label>
                                        <select class="form-control" name="prioridade">
                                            <option value="Baixa" <?php echo ($chamado['prioridade'] ?? 'Média') == 'Baixa' ? 'selected' : ''; ?>>Baixa</option>
                                            <option value="Média" <?php echo ($chamado['prioridade'] ?? 'Média') == 'Média' ? 'selected' : ''; ?>>Média</option>
                                            <option value="Alta" <?php echo ($chamado['prioridade'] ?? 'Média') == 'Alta' ? 'selected' : ''; ?>>Alta</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Descrição</label>
                                    <textarea class="form-control" rows="5"
                                        readonly><?php echo htmlspecialchars($chamado['descricao']); ?></textarea>
                                </div>

                                <?php if (!empty($chamado['anexo'])): ?>
                                    <div class="form-group">
                                        <label><i class="fas fa-paperclip"></i> Anexo</label>
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
                                    <label><strong>Notas de Resolução</strong> <small class="text-muted">(Registre aqui
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
                                                            <span class="badge badge-warning ml-1">Editado em
                                                                <?php echo htmlspecialchars($nota_item['editado_em']); ?></span>
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
                                        <textarea class="form-control" name="nova_nota" rows="3"
                                            placeholder="Descreva as ações tomadas, a solução aplicada ou observações relevantes..."></textarea>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <strong>Alterar Status</strong>
                                </div>
                                <div class="form-row align-items-end">
                                    <div class="form-group col-md-4">
                                        <label for="status">Status Atual</label>
                                        <select class="form-control" name="status" id="status">
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
                                        <label for="responsavel">Atribuir Responsável</label>
                                        <select class="form-control" name="responsavel_id" id="responsavel">
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
</body>

</html>