<?php
// zabbix_empresas.php

require 'conexao.php';

// Variáveis para mensagens
$msg = '';
$msgType = '';

// Processamento do formulário (Adicionar/Excluir)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $nome = trim($_POST['nome']);
        $sistema_tipo = trim($_POST['sistema_tipo']);
        $url = trim($_POST['url']);
        $user = trim($_POST['user']);
        $pass = trim($_POST['pass']); // Idealmente, criptografar a senha do zabbix em um projeto real
        $email_destinatario = trim($_POST['email_destinatario']);
        $gemini_api_key = trim($_POST['gemini_api_key']);

        $stmt = $conn->prepare("INSERT INTO zabbix_empresas (nome, sistema_tipo, url, user, pass, email_destinatario, gemini_api_key) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $nome, $sistema_tipo, $url, $user, $pass, $email_destinatario, $gemini_api_key);
        
        if ($stmt->execute()) {
            $msg = "Empresa adicionada com sucesso no banco de dados!";
            $msgType = "success";
        } else {
            $msg = "Erro ao adicionar empresa: " . $conn->error;
            $msgType = "danger";
        }
        $stmt->close();
    } 
    elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $idToDelete = (int) $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM zabbix_empresas WHERE id = ?");
        $stmt->bind_param("i", $idToDelete);
        
        if ($stmt->execute()) {
            $msg = "Empresa excluída com sucesso!";
            $msgType = "danger";
        } else {
            $msg = "Erro ao excluir empresa: " . $conn->error;
            $msgType = "danger";
        }
        $stmt->close();
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = (int) $_POST['id'];
        $nome = trim($_POST['nome']);
        $sistema_tipo = trim($_POST['sistema_tipo']);
        $url = trim($_POST['url']);
        $user = trim($_POST['user']);
        $pass = trim($_POST['pass']);
        $email_destinatario = trim($_POST['email_destinatario']);
        $gemini_api_key = trim($_POST['gemini_api_key']);

        $stmt = $conn->prepare("UPDATE zabbix_empresas SET nome=?, sistema_tipo=?, url=?, user=?, pass=?, email_destinatario=?, gemini_api_key=? WHERE id=?");
        $stmt->bind_param("sssssssi", $nome, $sistema_tipo, $url, $user, $pass, $email_destinatario, $gemini_api_key, $id);
        
        if ($stmt->execute()) {
            $msg = "Empresa atualizada com sucesso!";
            $msgType = "success";
        } else {
            $msg = "Erro ao atualizar empresa: " . $conn->error;
            $msgType = "danger";
        }
        $stmt->close();
    }
}

// Carrega as empresas existentes do MySQL
$empresas = [];
$result = $conn->query("SELECT * FROM zabbix_empresas ORDER BY nome ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $empresas[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified Monitor Analytics - Gestão Multi-Empresas</title>
    <!-- Fontes Premium: Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap & Ícones -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Design System Premium */
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary-color: #3b82f6;
            --primary-hover: #60a5fa;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --danger-color: #ef4444;
            --input-bg: rgba(15, 23, 42, 0.6);
            --input-border: rgba(255, 255, 255, 0.1);
        }

        body {
            background: var(--bg-gradient);
            font-family: 'Outfit', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            padding-bottom: 3rem;
        }

        .page-header {
            padding: 3rem 0;
            text-align: center;
        }

        .page-header h2 {
            font-weight: 700;
            font-size: 2.5rem;
            background: linear-gradient(to right, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 1.1rem;
            font-weight: 300;
        }

        /* Glassmorphism Cards */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .glass-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--card-border);
            font-weight: 600;
            font-size: 1.25rem;
            color: #fff;
            display: flex;
            align-items: center;
        }

        .glass-header i {
            color: var(--primary-color);
        }

        /* Formulários Customizados */
        .form-label {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
        }

        .form-control {
            background-color: var(--input-bg);
            border: 1px solid var(--input-border);
            color: #fff;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: var(--input-bg);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
            color: #fff;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.2);
        }

        /* Botões */
        .btn-modern {
            background: linear-gradient(135deg, var(--primary-color), #2563eb);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-modern:hover {
            background: linear-gradient(135deg, var(--primary-hover), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            color: white;
        }

        /* Tabela Moderna */
        .table {
            color: var(--text-main);
            margin-bottom: 0;
        }

        .table thead th {
            background: transparent;
            color: var(--text-muted);
            border-bottom: 1px solid var(--card-border);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 1.2rem 1rem;
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
            border-bottom: 1px solid var(--card-border);
        }

        .table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border: none;
        }

        .btn-danger-glass {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 6px;
            padding: 0.4rem 0.8rem;
            transition: all 0.2s ease;
        }

        .btn-danger-glass:hover {
            background: var(--danger-color);
            color: white;
            transform: scale(1.05);
        }

        .alert-glass {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #34d399;
            backdrop-filter: blur(10px);
            border-radius: 10px;
        }

        .alert-danger-glass {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-muted);
        }
        
        .empty-state i {
            font-size: 3rem;
            opacity: 0.5;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="page-header">
            <div>
                <h2>Unified Monitor Analytics</h2>
                <p>Gerenciamento Multi-Plataforma para Relatórios Inteligentes</p>
            </div>
            <div>
                <a href="dashboard_zabbix.php" class="btn btn-primary" style="border-radius: 8px;"><i class="fas fa-chart-line me-2"></i> Ver Dashboard</a>
            </div>
        </div>
        
        <?php if ($msg): ?>
            <div class="alert <?= $msgType === 'success' ? 'alert-glass' : 'alert-danger-glass' ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas <?= $msgType === 'success' ? 'fa-check-circle' : 'fa-info-circle' ?> me-2"></i> <?= $msg ?>
                <button type="button" class="btn-close btn-close-white opacity-50" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Formulário de Cadastro -->
            <div class="col-lg-4">
                <div class="glass-card h-100">
                    <div class="glass-header">
                        <i class="fas fa-bolt me-2"></i> Adicionar Empresa
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="mb-3">
                                <label class="form-label">Nome da Empresa <span class="text-danger">*</span></label>
                                <input type="text" name="nome" class="form-control" required placeholder="Ex: Cliente Alpha">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Sistema de Monitoramento <span class="text-danger">*</span></label>
                                <select name="sistema_tipo" class="form-select" style="background-color: var(--input-bg); border: 1px solid var(--input-border); color: #fff;">
                                    <option value="zabbix">Zabbix (JSON-RPC)</option>
                                    <option value="prtg">PRTG Network Monitor</option>
                                    <option value="nagios">Nagios Core / XI</option>
                                    <option value="grafana">Grafana / Prometheus</option>
                                    <option value="dynatrace">Dynatrace (SaaS/Managed)</option>
                                    <option value="outro">Outro (API Genérica)</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">URL da API / Endpoint <span class="text-danger">*</span></label>
                                <input type="url" name="url" class="form-control" required placeholder="Ex: http://192.168.1.1/api/...">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Usuário <span class="text-danger">*</span></label>
                                    <input type="text" name="user" class="form-control" required placeholder="Admin">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Senha <span class="text-danger">*</span></label>
                                    <input type="password" name="pass" class="form-control" required placeholder="••••••••">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">E-mail Recebedor <span class="text-danger">*</span></label>
                                <input type="email" name="email_destinatario" class="form-control" required placeholder="gestor@empresa.com">
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Chave Google Gemini (Opcional)</label>
                                <input type="text" name="gemini_api_key" class="form-control" placeholder="Chave de IA customizada">
                                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;"><i class="fas fa-info-circle me-1"></i>Deixe em branco para usar a inteligência padrão do servidor.</small>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-modern">
                                    <i class="fas fa-plus me-2"></i>Cadastrar no Sistema
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Lista de Empresas -->
            <div class="col-lg-8">
                <div class="glass-card h-100">
                    <div class="glass-header justify-content-between">
                        <div><i class="fas fa-server me-2"></i> Ambientes Monitorados</div>
                        <span class="badge bg-primary rounded-pill"><?= count($empresas) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Empresa</th>
                                        <th>Sistema</th>
                                        <th>Endpoint API</th>
                                        <th>Destinatário</th>
                                        <th class="text-end pe-4">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($empresas)): ?>
                                        <tr>
                                            <td colspan="4">
                                                <div class="empty-state">
                                                    <i class="fas fa-inbox"></i>
                                                    <h5>Nenhuma empresa cadastrada</h5>
                                                    <p class="mb-0">Cadastre o primeiro ambiente no formulário ao lado.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($empresas as $emp): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark" style="font-size: 1.1rem;"><?= htmlspecialchars($emp['nome']) ?></div>
                                                    <small class="text-muted" style="font-size: 0.7rem;">ID: <?= substr($emp['id'], 0, 8) ?></small>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php
                                                        $icon = "fa-server";
                                                        $label = ucfirst($emp['sistema_tipo'] ?? 'zabbix');
                                                        $color = "text-muted";
                                                        if(($emp['sistema_tipo'] ?? 'zabbix') == 'zabbix') { $icon = "fa-circle-dot"; $color = "text-success"; }
                                                        if(($emp['sistema_tipo'] ?? 'zabbix') == 'prtg') { $icon = "fa-chart-pie"; $color = "text-warning"; }
                                                        if(($emp['sistema_tipo'] ?? 'zabbix') == 'nagios') { $icon = "fa-eye"; $color = "text-danger"; }
                                                        if(($emp['sistema_tipo'] ?? 'zabbix') == 'dynatrace') { $icon = "fa-bolt"; $color = "text-info"; }
                                                        if(($emp['sistema_tipo'] ?? 'zabbix') == 'grafana') { $icon = "fa-chart-line"; $color = "text-primary"; }
                                                        ?>
                                                        <div class="<?= $color ?> bg-opacity-10 rounded p-1 me-2 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; background: rgba(255,255,255,0.05);">
                                                            <i class="fas <?= $icon ?> " style="font-size: 0.8rem;"></i>
                                                        </div>
                                                        <span class="small fw-medium"><?= $label ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($emp['url']) ?>">
                                                        <i class="fas fa-link me-1 opacity-50"></i> <small><?= htmlspecialchars($emp['url']) ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary bg-opacity-25 rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                            <i class="fas fa-envelope text-primary" style="font-size: 0.7rem;"></i>
                                                        </div>
                                                        <small><?= htmlspecialchars($emp['email_destinatario']) ?></small>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <button type="button" class="btn btn-outline-primary btn-sm me-1" style="border-radius: 8px; border-color: rgba(59, 130, 246, 0.3);" 
                                                            onclick="openEditModal(<?= htmlspecialchars(json_encode($emp)) ?>)" title="Editar Ambiente">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('ATENÇÃO: Deseja realmente remover o monitoramento da empresa <?= htmlspecialchars($emp['nome']) ?>?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                                                        <button type="submit" class="btn btn-danger-glass" title="Remover Ambiente">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Edição -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card" style="background: #1e293b; border: 1px solid var(--card-border);">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title text-white"><i class="fas fa-edit me-2 text-primary"></i>Editar Ambiente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Nome da Empresa</label>
                            <input type="text" name="nome" id="edit_nome" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sistema de Monitoramento</label>
                            <select name="sistema_tipo" id="edit_sistema" class="form-select" style="background-color: var(--input-bg); border: 1px solid var(--input-border); color: #fff;">
                                <option value="zabbix">Zabbix (JSON-RPC)</option>
                                <option value="prtg">PRTG Network Monitor</option>
                                <option value="nagios">Nagios Core / XI</option>
                                <option value="grafana">Grafana / Prometheus</option>
                                <option value="dynatrace">Dynatrace (SaaS/Managed)</option>
                                <option value="outro">Outro (API Genérica)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">URL da API / Endpoint</label>
                            <input type="url" name="url" id="edit_url" class="form-control" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Usuário</label>
                                <input type="text" name="user" id="edit_user" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Senha</label>
                                <input type="password" name="pass" id="edit_pass" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">E-mail Recebedor</label>
                            <input type="email" name="email_destinatario" id="edit_email" class="form-control" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Chave Google Gemini</label>
                            <input type="text" name="gemini_api_key" id="edit_gemini" class="form-control">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-modern">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));

        function openEditModal(empresa) {
            document.getElementById('edit_id').value = empresa.id;
            document.getElementById('edit_nome').value = empresa.nome;
            document.getElementById('edit_sistema').value = empresa.sistema_tipo || 'zabbix';
            document.getElementById('edit_url').value = empresa.url;
            document.getElementById('edit_user').value = empresa.user;
            document.getElementById('edit_pass').value = empresa.pass;
            document.getElementById('edit_email').value = empresa.email_destinatario;
            document.getElementById('edit_gemini').value = empresa.gemini_api_key || '';
            
            editModal.show();
        }
    </script>
</body>
</html>
