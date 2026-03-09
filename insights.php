<?php
include 'auth.php';
include 'conexao.php';

// 1. Recorrência de Incidentes (Títulos similares)
$sql_recorrencia = "SELECT titulo, COUNT(*) as total 
                    FROM chamados 
                    GROUP BY titulo 
                    HAVING total > 1 
                    ORDER BY total DESC LIMIT 5";
$res_recorrencia = $conn->query($sql_recorrencia);

// 2. Ativos Problemáticos (Múltiplas manutenções)
$sql_ativos_críticos = "SELECT a.id_asset, a.hostName, a.modelo, COUNT(m.id_manutencao) as total_manut
                        FROM ativos a
                        JOIN manutencao m ON a.id_asset = m.id_asset
                        GROUP BY a.id_asset
                        HAVING total_manut > 1
                        ORDER BY total_manut DESC LIMIT 5";
$res_ativos = $conn->query($sql_ativos_críticos);

// 3. Cruzamento de Sugestões baseadas nos chamados recentes (Otimizado)
$sugestoes = [];
$sql_chamados_recentes = "SELECT DISTINCT titulo FROM chamados ORDER BY data_abertura DESC LIMIT 20";
$res_recentes = $conn->query($sql_chamados_recentes);

if ($res_recentes && $res_recentes->num_rows > 0) {
    // Buscar todas as sugestões possíveis uma única vez
    $sql_all_sugestoes = "SELECT palavra_chave, sugestao FROM sugestoes_prevencao";
    $res_all = $conn->query($sql_all_sugestoes);
    $lista_sugestoes = [];
    if ($res_all) {
        while ($s = $res_all->fetch_assoc()) {
            $lista_sugestoes[] = $s;
        }
    }

    while ($row_chamado = $res_recentes->fetch_assoc()) {
        $titulo = $row_chamado['titulo'];
        foreach ($lista_sugestoes as $item) {
            if (stripos($titulo, $item['palavra_chave']) !== false) {
                $sugestoes[$titulo] = $item['sugestao'];
                break; // Pega a primeira correspondência e vai para o próximo título
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Previsão & Prevenção - Asset Mgt</title>
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/css/Montserrat.css">
    <?php include 'sidebar_style.php'; ?>
    <style>
        .card-insight {
            border-left: 4px solid #f6c23e;
            transition: transform 0.2s;
        }

        .card-insight:hover {
            transform: scale(1.02);
        }

        .icon-box {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eee;
            margin-right: 15px;
        }

        .clickable-row:hover {
            background-color: rgba(0, 0, 0, 0.05);
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .insight-item:hover {
            background: #f1f3f9;
            cursor: pointer;
            border-radius: 5px;
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
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top">
                    <div class="container-fluid">
                        <h3 class="text-dark mb-0">Inteligência de Gestão: Previsão & Prevenção</h3>
                    </div>
                </nav>
                <div class="container-fluid">

                    <div class="row">
                        <!-- Top Incidentes Recorrentes -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">🔄 Top Incidentes Recorrentes</h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($res_recorrencia->num_rows > 0): ?>
                                        <?php while ($row = $res_recorrencia->fetch_assoc()): ?>
                                            <div class="mb-3 p-2 insight-item"
                                                onclick="window.location='chamados.php?busca=<?php echo urlencode($row['titulo']); ?>'">
                                                <div class="small font-weight-bold">
                                                    <?php echo htmlspecialchars($row['titulo']); ?> <span class="float-right">
                                                        <?php echo $row['total']; ?> ocorrências
                                                    </span>
                                                </div>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-warning" role="progressbar"
                                                        style="width: <?php echo ($row['total'] * 10); ?>%"></div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <p class="text-muted">Nenhuma recorrência crítica detectada ainda.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Ativos Problemáticos -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-danger">⚠️ Ativos Críticos (Múltiplas
                                        Manutenções)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>HostName</th>
                                                    <th>Modelo</th>
                                                    <th class="text-center">Manutenções</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($res_ativos->num_rows > 0): ?>
                                                    <?php while ($row = $res_ativos->fetch_assoc()): ?>
                                                        <tr class="clickable-row"
                                                            onclick="window.location='detalhes_do_equipamento.php?id=<?php echo $row['id_asset']; ?>'">
                                                            <td>
                                                                <strong><?php echo htmlspecialchars($row['hostName']); ?></strong>
                                                            </td>
                                                            <td><small>
                                                                    <?php echo htmlspecialchars($row['modelo']); ?>
                                                                </small></td>
                                                            <td class="text-center"><span class="badge badge-danger">
                                                                    <?php echo $row['total_manut']; ?>
                                                                </span></td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">Todos os ativos estão
                                                            operando dentro da normalidade.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guia de Melhores Práticas -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-success">💡 Sugestões de Prevenção e Melhores
                                        Práticas</h6>
                                </div>
                                <div class="container-fluid">

                                    <!-- CONSULTORIA POR IA -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="card shadow border-left-primary bg-light">
                                                <div class="card-body">
                                                    <div class="row align-items-center no-gutters">
                                                        <div class="col-auto mr-3">
                                                            <div
                                                                style="width: 60px; height: 60px; background: rgb(44,64,74); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                                <i class="fas fa-robot fa-2x text-white"></i>
                                                            </div>
                                                        </div>
                                                        <div class="col">
                                                            <div
                                                                class="text-uppercase text-primary font-weight-bold text-xs mb-1">
                                                                Consultoria Estratégica por IA <span
                                                                    class="badge badge-primary">Gemini 2.0</span>
                                                            </div>
                                                            <div id="ai-analysis-text" class="text-dark mb-0 italic">
                                                                <div class="typing-dots">
                                                                    <span></span><span></span><span></span> Analisando
                                                                    dados em tempo real...
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <?php if (count($sugestoes) > 0): ?>
                                            <?php foreach ($sugestoes as $problema => $dica): ?>
                                                <div class="col-md-6 mb-3">
                                                    <div class="p-3 card-insight shadow-sm bg-light rounded">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <div class="icon-box"><i class="fas fa-lightbulb text-warning"></i>
                                                            </div>
                                                            <div class="font-weight-bold text-dark">Baseado em: "
                                                                <?php echo htmlspecialchars($problema); ?>"
                                                            </div>
                                                        </div>
                                                        <p class="mb-0 small text-secondary">
                                                            <?php echo htmlspecialchars($dica); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="col-12 text-center py-4">
                                                <i class="fas fa-magic fa-3x text-gray-300 mb-3"></i>
                                                <p>O sistema ainda está aprendendo com seus dados. Continue registrando
                                                    chamados para receber insights!</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/theme.js"></script>
    <script>
        $(document).ready(function () {
            // Carregar análise da IA
            fetch('agent_insights.php')
                .then(response => response.json())
                .then(data => {
                    const textContainer = document.getElementById('ai-analysis-text');
                    // Formatação simples de markdown
                    let reply = data.reply.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                    reply = reply.replace(/\n/g, '<br>');
                    textContainer.innerHTML = reply;
                })
                .catch(error => {
                    document.getElementById('ai-analysis-text').innerHTML = '⚠️ Erro ao carregar análise estratégica.';
                    console.error('Error:', error);
                });
        });
    </script>
</body>

</html>