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
    $nota_resolucao = isset($_POST['nota_resolucao']) ? mysqli_real_escape_string($conn, $_POST['nota_resolucao']) : '';

    // Validação: Impedir alteração de status (se não for Aberto) sem responsável
    if ($novo_status !== 'Aberto' && $responsavel_id === 'NULL') {
        $msg = '<div class="alert alert-danger"><strong>Erro:</strong> Para alterar o status (sair de "Aberto"), é obrigatório atribuir um <strong>Responsável</strong> ao chamado.</div>';
    }
    else {
        // Lógica para data_fechamento
        $fechamento_sql = "";
        $status_fechados = ['Resolvido', 'Fechado', 'Cancelado'];

        if (in_array($novo_status, $status_fechados)) {
            $fechamento_sql = ", data_fechamento = NOW()";
        }
        else {
            $fechamento_sql = ", data_fechamento = NULL";
        }

        $sql_update = "UPDATE chamados SET status = '$novo_status', responsavel_id = $responsavel_id, prioridade = '$prioridade', nota_resolucao = '$nota_resolucao' $fechamento_sql WHERE id = $id_chamado";

        if ($conn->query($sql_update) === TRUE) {
            $msg = '<div class="alert alert-success">Chamado atualizado com sucesso! <a href="chamados.php">Voltar para lista</a></div>';
        }
        else {
            $msg = '<div class="alert alert-danger">Erro ao atualizar: ' . $conn->error . '</div>';
        }
    }
}

// Buscar dados do chamado
$sql = "SELECT c.*, u.nome, u.sobrenome FROM chamados c LEFT JOIN usuarios u ON c.usuario_id = u.id_usuarios WHERE c.id = $id_chamado";
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
                    <?php echo $msg; ?>
                    
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Informações</h6>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Título</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($chamado['titulo']); ?>" readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Categoria</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($chamado['categoria']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Solicitante</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($chamado['nome'] . ' ' . $chamado['sobrenome']); ?>" readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Data Abertura</label>
                                        <input type="text" class="form-control" value="<?php echo date('d/m/Y H:i', strtotime($chamado['data_abertura'])); ?>" readonly>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Prioridade</label>
                                        <select class="form-control" name="prioridade">
                                            <option value="Baixa" <?php echo($chamado['prioridade'] ?? 'Média') == 'Baixa' ? 'selected' : ''; ?>>Baixa</option>
                                            <option value="Média" <?php echo($chamado['prioridade'] ?? 'Média') == 'Média' ? 'selected' : ''; ?>>Média</option>
                                            <option value="Alta" <?php echo($chamado['prioridade'] ?? 'Média') == 'Alta' ? 'selected' : ''; ?>>Alta</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Descrição</label>
                                    <textarea class="form-control" rows="5" readonly><?php echo htmlspecialchars($chamado['descricao']); ?></textarea>
                                </div>

                                <?php if (!empty($chamado['anexo'])): ?>
                                <div class="form-group">
                                    <label><i class="fas fa-paperclip"></i> Anexo</label>
                                    <div style="border: 1px solid #e3e6f0; border-radius: 8px; padding: 15px; background: #f8f9fc;">
                                        <?php
    $ext = strtolower(pathinfo($chamado['anexo'], PATHINFO_EXTENSION));
    $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp']);
    if ($is_image): ?>
                                            <a href="/<?php echo htmlspecialchars($chamado['anexo']); ?>" target="_blank">
                                                <img src="/<?php echo htmlspecialchars($chamado['anexo']); ?>" style="max-width: 100%; max-height: 300px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" alt="Anexo do chamado">
                                            </a>
                                            <br><small class="text-muted mt-2 d-inline-block">Clique na imagem para abrir em tamanho completo</small>
                                        <?php
    else: ?>
                                            <a href="/<?php echo htmlspecialchars($chamado['anexo']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-<?php echo($ext === 'pdf') ? 'file-pdf' : 'file-word'; ?>"></i>
                                                Baixar Anexo (<?php echo strtoupper($ext); ?>)
                                            </a>
                                        <?php
    endif; ?>
                                    </div>
                                </div>
                                <?php
endif; ?>

                                <div class="form-group">
                                    <label><strong>Nota de Resolução</strong> <small class="text-muted">(Registre aqui as ações realizadas para resolver o chamado)</small></label>
                                    <textarea class="form-control" name="nota_resolucao" rows="4" placeholder="Descreva as ações tomadas, a solução aplicada ou observações relevantes..."><?php echo htmlspecialchars($chamado['nota_resolucao'] ?? ''); ?></textarea>
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
                                    <div class="form-group col-md-2">
                                        <a href="chamados.php" class="btn btn-secondary btn-block">Voltar</a>
                                    </div>
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
</body>
</html>
