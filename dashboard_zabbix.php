<?php
require 'conexao.php';

// Filtro de empresa e período (via GET)
$empresa_id = isset($_GET['empresa']) ? (int) $_GET['empresa'] : 0;
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
$export = isset($_GET['export']) ? $_GET['export'] : '';

// Busca todas as empresas para o dropdown
$empresas = [];
$resEmpresas = $conn->query("SELECT id, nome FROM zabbix_empresas ORDER BY nome ASC");
while ($row = $resEmpresas->fetch_assoc()) {
    $empresas[] = $row;
    if ($empresa_id === 0) {
        $empresa_id = $row['id']; // Auto-seleciona a primeira caso não tenha sido selecionada
    }
}

// Inicializa variáveis para os gráficos e tabelas
$chartLabels = [];
$chartData = [];
$historico = [];

if ($empresa_id > 0) {
    // 1. DADOS PARA A TABELA (Top alertas da empresa selecionada, ordenados pelos mais recentes)
    $sqlTable = "SELECT id, data_relatorio, alerta_nome, recorrencia, ai_sugestao, severidade, host_envolvido, ultima_ocorrencia, comentario_usuario, falso_positivo, lista_ocorrencias 
                 FROM zabbix_historico_alertas 
                 WHERE empresa_id = ? AND data_relatorio >= ? AND data_relatorio <= ?
                 ORDER BY data_relatorio DESC, recorrencia DESC 
                 LIMIT 500";
    $stmt2 = $conn->prepare($sqlTable);
    $stmt2->bind_param("iss", $empresa_id, $data_inicio, $data_fim);
    $stmt2->execute();
    $resTable = $stmt2->get_result();
    while ($row = $resTable->fetch_assoc()) {
        $historico[] = $row;
    }
    $stmt2->close();

    // LÓGICA DE EXPORTAÇÃO CSV
    if ($export === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=relatorio_zabbix_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM para Excel
        fputcsv($output, ['Data Relatorio', 'Alerta', 'Host', 'Severidade', 'Recorrencia', 'Falso Positivo', 'Comentario Tecnico', 'Sugestao IA']);
        foreach ($historico as $item) {
            fputcsv($output, [
                date('d/m/Y', strtotime($item['data_relatorio'])),
                $item['alerta_nome'],
                $item['host_envolvido'],
                $item['severidade'],
                $item['recorrencia'],
                $item['falso_positivo'] ? 'SIM' : 'NAO',
                $item['comentario_usuario'],
                strip_tags(str_replace('<br>', "\n", $item['ai_sugestao']))
            ]);
        }
        fclose($output);
        exit;
    }

    // 2. DADOS PARA O GRÁFICO (Evolução de incidentes totais no período)
    $sqlChart = "SELECT data_relatorio, SUM(recorrencia) as total_alertas 
                 FROM zabbix_historico_alertas 
                 WHERE empresa_id = ? AND data_relatorio >= ? AND data_relatorio <= ?
                 GROUP BY data_relatorio 
                 ORDER BY data_relatorio ASC LIMIT 30";
    $stmt = $conn->prepare($sqlChart);
    $stmt->bind_param("iss", $empresa_id, $data_inicio, $data_fim);
    $stmt->execute();
    $resChart = $stmt->get_result();
    while ($row = $resChart->fetch_assoc()) {
        // Formata data de YYYY-MM-DD para DD/MM
        $dataFormatada = date('d/m', strtotime($row['data_relatorio']));
        $chartLabels[] = $dataFormatada;
        $chartData[] = (int) $row['total_alertas'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified Monitor Analytics - Dashboard</title>
    <!-- Fontes Premium: Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap & Ícones -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* Design System Premium (Reutilizando a estética Dark Glassmorphism) */
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary-color: #3b82f6;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h2 {
            font-weight: 700;
            font-size: 2.2rem;
            background: linear-gradient(to right, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.2rem;
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 1.5rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }

        .form-select {
            background-color: var(--input-bg);
            border: 1px solid var(--input-border);
            color: #fff;
            padding: 0.75rem;
            border-radius: 8px;
        }
        
        .form-select:focus {
            background-color: var(--input-bg);
            border-color: var(--primary-color);
            color: #fff;
            box-shadow: none;
        }

        .table-responsive {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1rem;
        }

        .table {
            color: var(--text-main);
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 1px solid var(--card-border);
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            background: transparent;
        }

        .table tbody tr {
            border-bottom: 1px solid var(--card-border);
            transition: background 0.2s;
            cursor: pointer;
        }

        .table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .table tbody td {
            vertical-align: middle;
            border: none;
            padding: 1rem 0.5rem;
        }

        .badge-recorrencia {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            display: inline-block;
        }

        .badge-recorrencia:hover {
            background: rgba(239, 68, 68, 0.4);
        }

        .ai-tip {
            font-size: 0.85rem;
            color: #a78bfa;
            cursor: pointer;
        }

        /* Estilos de Impressão (PDF) */
        @media print {
            body { background: #fff !important; color: #000 !important; }
            .page-header, .btn, form, .modal { display: none !important; }
            .glass-card, .table-responsive { border: none !important; box-shadow: none !important; background: transparent !important; }
            .table { color: #000 !important; }
            .table thead th { color: #000 !important; border-bottom: 2px solid #000 !important; }
            .table tbody tr { border-bottom: 1px solid #ccc !important; }
            .text-white { color: #000 !important; }
            .badge-recorrencia { background: transparent !important; border: 1px solid #f87171 !important; color: #f87171 !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header & Filtro -->
        <div class="page-header">
            <div>
                <h2><i class="fas fa-chart-area me-2"></i>Dashboard Analítico</h2>
                <p class="text-muted mb-0">Evolução do Monitoramento Multi-Sistema e IA</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="zabbix_empresas.php" class="btn btn-outline-light" style="border-radius: 8px; border-color: var(--card-border);">
                    <i class="fas fa-building me-2"></i> Empresas
                </a>
                <form method="GET" class="d-flex align-items-end gap-2 m-0" id="filterForm">
                    <div>
                        <label class="form-label text-muted mb-1" style="font-size: 0.8rem;">Empresa:</label>
                        <select name="empresa" class="form-select" onchange="document.getElementById('exportField').value=''; this.form.submit()" style="width: 200px;">
                            <?php foreach ($empresas as $emp): ?>
                                <option value="<?= $emp['id'] ?>" <?= $emp['id'] == $empresa_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($emp['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-muted mb-1" style="font-size: 0.8rem;">De:</label>
                        <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($data_inicio) ?>" onchange="document.getElementById('exportField').value=''; this.form.submit()">
                    </div>
                    <div>
                        <label class="form-label text-muted mb-1" style="font-size: 0.8rem;">Até:</label>
                        <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($data_fim) ?>" onchange="document.getElementById('exportField').value=''; this.form.submit()">
                    </div>
                    <input type="hidden" name="export" id="exportField" value="">
                </form>
            </div>
        </div>

        <?php if ($empresa_id > 0): ?>
        <div class="row">
            <!-- Gráfico de Evolução -->
            <div class="col-lg-12">
                <div class="glass-card">
                    <h5 class="mb-4 text-white"><i class="fas fa-chart-line me-2 text-primary"></i> Evolução de Ocorrências (Últimos Relatórios)</h5>
                    <div style="height: 300px;">
                        <canvas id="evolutionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Histórico e Dicas da IA -->
        <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
            <h4 class="text-white m-0">Histórico de Alertas</h4>
            <div>
                <button type="button" onclick="window.open('gerador_pdf_zabbix.php?empresa=<?= $empresa_id ?>&data_inicio=<?= $data_inicio ?>&data_fim=<?= $data_fim ?>', '_blank')" class="btn btn-outline-light btn-sm me-2" style="border-color: var(--card-border);">
                    <i class="fas fa-print me-1"></i> Gerar PDF Formal
                </button>
                <button onclick="document.getElementById('exportField').value='csv'; document.getElementById('filterForm').submit();" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-csv me-1"></i> Exportar CSV
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th width="15%">Última Ocorrência</th>
                        <th width="35%">Nome do Alerta</th>
                        <th width="15%" class="text-center">Recorrência</th>
                        <th width="35%">Resumo da Solução (IA)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historico)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i><br>
                                Nenhum histórico gravado ainda para esta empresa.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historico as $item): 
                            $jsonItem = htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr onclick="openAlertModal(this)" data-info="<?= $jsonItem ?>" <?= $item['falso_positivo'] ? 'style="opacity: 0.6;"' : '' ?>>
                                <td>
                                    <div class="fw-bold text-white"><?= date('d/m/Y H:i', strtotime($item['ultima_ocorrencia'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold" style="color: #cbd5e1;">
                                        <?= htmlspecialchars($item['alerta_nome']) ?>
                                        <?php if($item['falso_positivo']): ?>
                                            <span class="badge bg-warning text-dark ms-2" style="font-size: 0.7rem;">Falso Positivo</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><i class="fas fa-server"></i> <?= htmlspecialchars($item['host_envolvido'] ?? 'N/A') ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge-recorrencia" onclick="openTimelineModal(event, this)" data-info="<?= $jsonItem ?>" title="Ver Linha do Tempo">
                                        <i class="fas fa-history me-1"></i> <?= $item['recorrencia'] ?> vezes
                                    </span><br>
                                    <small class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($item['severidade'] ?? '') ?></small>
                                </td>
                                <td>
                                    <?php 
                                        $snippet = mb_substr(strip_tags($item['ai_sugestao']), 0, 90) . "...";
                                        $fullText = htmlspecialchars(strip_tags($item['ai_sugestao']));
                                    ?>
                                    <div class="ai-tip" title="Clique para ver os detalhes">
                                        <i class="fas fa-robot me-1"></i> <?= $snippet ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($empresa_id > 0 && !empty($chartLabels)): ?>
    <script>
        // Configuração do Chart.js com design que combina com o Glassmorphism
        const ctx = document.getElementById('evolutionChart').getContext('2d');
        
        // Criando gradiente para a área abaixo da linha
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)'); // Azul
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)'); // Transparente

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Total de Disparos',
                    data: <?= json_encode($chartData) ?>,
                    borderColor: '#60a5fa',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#60a5fa',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 // Linha curva e suave
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { family: 'Outfit', size: 13 },
                        bodyFont: { family: 'Outfit', size: 14, weight: 'bold' },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { color: '#94a3b8', font: { family: 'Outfit' } }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                        ticks: { color: '#94a3b8', font: { family: 'Outfit' }, precision: 0 },
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    <?php endif; ?>

    <!-- Modal Detalhes do Alerta -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background: #1e293b; color: #f8fafc; border: 1px solid rgba(255,255,255,0.1);">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fas fa-bell me-2"></i> Detalhes do Alerta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong class="text-muted">Alerta:</strong>
                            <div id="modalAlertName" class="fw-bold fs-5"></div>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted">Host/Equipamento:</strong>
                            <div id="modalHost"></div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong class="text-muted">Severidade:</strong>
                            <div id="modalSeverity"></div>
                        </div>
                        <div class="col-md-4">
                            <strong class="text-muted">Última Ocorrência:</strong>
                            <div id="modalLastClock"></div>
                        </div>
                        <div class="col-md-4">
                            <strong class="text-muted">Recorrência na Semana:</strong>
                            <div id="modalRecurrence"></div>
                        </div>
                    </div>

                    <div class="p-3 mb-4 rounded" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                        <strong class="text-success"><i class="fas fa-robot me-2"></i>Dica da IA (Google Gemini):</strong>
                        <div id="modalAiSuggestion" class="mt-2" style="font-size: 0.95rem;"></div>
                    </div>

                    <hr style="border-color: rgba(255,255,255,0.1);">

                    <!-- Formulário de Comentário / Falso Positivo -->
                    <form id="commentForm">
                        <input type="hidden" id="modalId" name="id">
                        <div class="mb-3">
                            <label class="form-label text-muted"><i class="fas fa-comment-dots me-1"></i> Comentário Técnico / Análise:</label>
                            <textarea class="form-control" id="modalComentario" name="comentario" rows="3" style="background: rgba(0,0,0,0.2); color: white; border: 1px solid rgba(255,255,255,0.1);" placeholder="Adicione notas técnicas sobre este alerta..."></textarea>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="modalFalsoPositivo" name="falso_positivo" value="1">
                            <label class="form-check-label text-warning" for="modalFalsoPositivo">Marcar como Falso Positivo</label>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary" id="btnSalvarComentario">
                                <i class="fas fa-save me-1"></i> Salvar Comentário
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Linha do Tempo (Timestamps) -->
    <div class="modal fade" id="timelineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="background: #1e293b; color: #f8fafc; border: 1px solid rgba(255,255,255,0.1);">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger"><i class="fas fa-history me-2"></i> Histórico de Disparos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Estes são todos os horários exatos em que o alerta disparou na última semana.</p>
                    <ul class="list-group list-group-flush rounded" id="timelineList" style="max-height: 300px; overflow-y: auto;">
                        <!-- Injetado via JS -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let modalInstance;
        let timelineModalInstance;
        
        document.addEventListener('DOMContentLoaded', function() {
            modalInstance = new bootstrap.Modal(document.getElementById('alertModal'));
            timelineModalInstance = new bootstrap.Modal(document.getElementById('timelineModal'));
            
            document.getElementById('commentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('btnSalvarComentario');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
                btn.disabled = true;

                const formData = new FormData(this);

                fetch('ajax_zabbix_comentario.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload(); // Recarrega para mostrar o falso positivo na listagem
                    } else {
                        alert(data.message);
                        btn.innerHTML = '<i class="fas fa-save me-1"></i> Salvar Comentário';
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert("Erro ao salvar comentário.");
                    btn.innerHTML = '<i class="fas fa-save me-1"></i> Salvar Comentário';
                    btn.disabled = false;
                });
            });
        });

        function openAlertModal(rowElement) {
            const dataStr = rowElement.getAttribute('data-info');
            const data = JSON.parse(dataStr);

            document.getElementById('modalId').value = data.id;
            document.getElementById('modalAlertName').textContent = data.alerta_nome;
            document.getElementById('modalHost').textContent = data.host_envolvido || 'N/A';
            document.getElementById('modalSeverity').textContent = data.severidade || 'Desconhecida';
            
            // Formatar data se existir
            let dateStr = 'N/A';
            if (data.ultima_ocorrencia) {
                const dateObj = new Date(data.ultima_ocorrencia);
                dateStr = dateObj.toLocaleDateString('pt-BR') + ' ' + dateObj.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
            }
            document.getElementById('modalLastClock').textContent = dateStr;
            
            document.getElementById('modalRecurrence').textContent = data.recorrencia + ' vezes';
            
            // Renderiza HTML da IA de forma segura ou quebra linhas
            document.getElementById('modalAiSuggestion').innerHTML = data.ai_sugestao.replace(/\n/g, '<br>');
            
            document.getElementById('modalComentario').value = data.comentario_usuario || '';
            document.getElementById('modalFalsoPositivo').checked = (data.falso_positivo == 1);

            modalInstance.show();
        }

        function openTimelineModal(event, element) {
            // Impede que o clique na badge abra o modal principal da linha
            event.stopPropagation();
            
            const dataStr = element.getAttribute('data-info');
            const data = JSON.parse(dataStr);
            const listContainer = document.getElementById('timelineList');
            
            listContainer.innerHTML = '';
            
            if (data.lista_ocorrencias) {
                try {
                    const timestamps = JSON.parse(data.lista_ocorrencias);
                    if (Array.isArray(timestamps) && timestamps.length > 0) {
                        timestamps.forEach(ts => {
                            const dateObj = new Date(ts);
                            const formatted = dateObj.toLocaleDateString('pt-BR') + ' às ' + dateObj.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
                            
                            const li = document.createElement('li');
                            li.className = 'list-group-item d-flex justify-content-between align-items-center';
                            li.style.background = 'rgba(255,255,255,0.05)';
                            li.style.color = '#e2e8f0';
                            li.style.borderColor = 'rgba(255,255,255,0.1)';
                            li.innerHTML = `<i class="fas fa-clock text-muted me-2"></i> ${formatted}`;
                            listContainer.appendChild(li);
                        });
                    } else {
                        listContainer.innerHTML = '<li class="list-group-item" style="background: transparent; color: #94a3b8; border: none;">Nenhum horário detalhado salvo.</li>';
                    }
                } catch (e) {
                    listContainer.innerHTML = '<li class="list-group-item text-danger" style="background: transparent; border: none;">Erro ao ler o histórico.</li>';
                }
            } else {
                listContainer.innerHTML = '<li class="list-group-item" style="background: transparent; color: #94a3b8; border: none;">As ocorrências antigas não gravaram os horários.</li>';
            }
            
            timelineModalInstance.show();
        }
    </script>
</body>
</html>
