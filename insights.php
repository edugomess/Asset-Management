<?php
// Certifique-se de que a sessão foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui autenticação e conexão com o banco de dados
include 'auth.php';
include 'conexao.php';

// Buscar configurações de IA (Geral e Prevenção)
$sql_ia = "SELECT ia_agente_ativo, ia_preve_ativo FROM configuracoes_alertas LIMIT 1";
$res_ia = mysqli_query($conn, $sql_ia);
$ia_geral_ativo = true;
$ia_preve_ativo = true;
if ($res_ia && mysqli_num_rows($res_ia) > 0) {
    $row_ia = mysqli_fetch_assoc($res_ia);
    $ia_geral_ativo = (bool) ($row_ia['ia_agente_ativo'] ?? 1);
    $ia_preve_ativo = (bool) ($row_ia['ia_preve_ativo'] ?? 1);
}

// 1. ANÁLISE DE RECORRÊNCIA: Identifica incidentes com títulos similares para detectar problemas sistêmicos
$sql_recorrencia = "SELECT titulo, COUNT(*) as total 
                    FROM chamados 
                    GROUP BY titulo 
                    HAVING total > 1 
                    ORDER BY total DESC LIMIT 5";
$res_recorrencia = $conn->query($sql_recorrencia);

// 2. ATIVOS PROBLEMÁTICOS: Identifica equipamentos que passaram por múltiplas manutenções (Ativos Críticos)
$sql_ativos_críticos = "SELECT a.id_asset, a.hostName, a.modelo, COUNT(m.id_manutencao) as total_manut
                        FROM ativos a
                        JOIN manutencao m ON a.id_asset = m.id_asset
                        GROUP BY a.id_asset
                        HAVING total_manut > 1
                        ORDER BY total_manut DESC LIMIT 5";
$res_ativos = $conn->query($sql_ativos_críticos);

// 3. MOTOR DE SUGESTÕES: Cruza palavras-chave dos chamados recentes com a base de conhecimento preventiva
$sugestoes = [];
$sql_chamados_recentes = "SELECT DISTINCT titulo FROM chamados ORDER BY data_abertura DESC LIMIT 20";
$res_recentes = $conn->query($sql_chamados_recentes);

if ($res_recentes && $res_recentes->num_rows > 0) {
    // Carrega todas as regras de prevenção em memória para otimizar o processamento em loop
    $sql_all_sugestoes = "SELECT palavra_chave, sugestao FROM sugestoes_prevencao";
    $res_all = $conn->query($sql_all_sugestoes);
    $lista_sugestoes = [];
    if ($res_all) {
        while ($s = $res_all->fetch_assoc()) {
            $lista_sugestoes[] = $s;
        }
    }

    // Faz o de-para entre títulos de chamados e sugestões baseadas em palavras-chave
    while ($row_chamado = $res_recentes->fetch_assoc()) {
        $titulo = $row_chamado['titulo'];
        foreach ($lista_sugestoes as $item) {
            if (stripos($titulo, $item['palavra_chave']) !== false) {
                $sugestoes[$titulo] = $item['sugestao'];
                break; // Pega a primeira recomendação relevante por chamado
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= (isset($_SESSION['language']) && $_SESSION['language'] == 'en-US') ? 'en' : 'pt-br'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Inteligência e Prevenção - Asset Management'); ?></title>
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/css/Montserrat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <?php include_once 'sidebar_style.php'; ?>
    <style>
        .card-shadow { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-shadow:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important; 
        }
        /* Estilos visuais para os cards de insights da IA */
        .card-insight {
            border-left: 4px solid #f6c23e;
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

        /* Melhorias na interação das linhas da tabela */
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

        /* Efeito de digitação para a consultoria da IA */
        .typing-dots span {
            width: 5px;
            height: 5px;
            background: #2c404a;
            border-radius: 50%;
            display: inline-block;
            margin: 0 2px;
            animation: blink 1.4s infinite both;
        }

        @keyframes blink {

            0%,
            80%,
            100% {
                opacity: 0;
            }

            40% {
                opacity: 1;
            }
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Menu Lateral -->
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);" aria-label="Navegação Lateral">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include_once 'sidebar_brand.php'; ?>
                <?php include_once 'sidebar_menu.php'; ?>
            </div>
        </nav>

        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <!-- Topbar Principal -->
                <?php include_once 'topbar.php'; ?>

                <div class="container-fluid">
                    <div class="row animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                        <!-- Card: Incidentes Recorrentes -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow card-shadow border-bottom-warning h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <?php echo __('🔄 Top Incidentes Recorrentes'); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($res_recorrencia->num_rows > 0): ?>
                                        <?php while ($row = $res_recorrencia->fetch_assoc()): ?>
                                            <div class="mb-3 p-2 insight-item"
                                                aria-label="<?php echo __('Visualizar chamados sobre ') . htmlspecialchars($row['titulo']); ?>"
                                                onclick="window.location='chamados.php?busca=<?php echo urlencode($row['titulo']); ?>'">
                                                <div class="small font-weight-bold">
                                                    <?php echo htmlspecialchars($row['titulo']); ?>
                                                    <span class="float-right badge badge-warning">
                                                        <?php echo $row['total']; ?>         <?php echo __('ocorrências'); ?>
                                                    </span>
                                                </div>
                                                <div class="progress progress-sm mt-2">
                                                    <div class="progress-bar bg-warning" role="progressbar"
                                                        title="<?php echo $row['total']; ?> <?php echo __('ocorrências'); ?>"
                                                        style="width: <?php echo min(100, $row['total'] * 10); ?>%"></div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <p class="text-muted text-center py-4">
                                            <?php echo __('Nenhuma recorrência crítica detectada ainda.'); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Ativos Críticos -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow card-shadow border-bottom-danger h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-danger">
                                        <?php echo __('⚠️ Ativos Críticos (Alto Índice de Manutenção)'); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover"
                                            aria-label="<?php echo __('Tabela de Ativos Críticos'); ?>">
                                            <thead>
                                                <tr>
                                                    <th scope="col"><?php echo __('HostName'); ?></th>
                                                    <th scope="col"><?php echo __('Modelo'); ?></th>
                                                    <th scope="col" class="text-center"><?php echo __('Manutenções'); ?>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($res_ativos->num_rows > 0): ?>
                                                    <?php while ($row = $res_ativos->fetch_assoc()): ?>
                                                        <tr class="clickable-row"
                                                            title="<?php echo __('Clique para detalhes do ativo'); ?>"
                                                            onclick="window.location='perfil_ativo.php?id=<?php echo $row['id_asset']; ?>'">
                                                            <td><strong><?php echo htmlspecialchars($row['hostName']); ?></strong>
                                                            </td>
                                                            <td><small><?php echo htmlspecialchars($row['modelo']); ?></small>
                                                            </td>
                                                            <td class="text-center"><span
                                                                    class="badge badge-danger"><?php echo $row['total_manut']; ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-4">
                                                            <?php echo __('Todos os ativos estão operando dentro da normalidade.'); ?>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seção: Guia de Melhores Práticas e Consultoria por IA -->
                    <div class="row animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                        <div class="col-12 mb-4">
                            <div class="card shadow card-shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <?php echo __('💡 Sugestões de Prevenção e Melhores Práticas'); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <!-- CONSULTORIA ESTRATÉGICA POR IA -->
                                    <?php if ($ia_geral_ativo && $ia_preve_ativo): ?>
                                        <div class="row mb-5">
                                            <div class="col-12">
                                                <div class="card shadow-sm border-left-primary bg-light">
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
                                                                    <?php echo __('Consultoria Estratégica Automática'); ?>
                                                                    <span class="badge badge-primary">Gemini 2.0 AI</span>
                                                                </div>
                                                                <div id="ai-analysis-text"
                                                                    class="text-dark mb-0 font-italic">
                                                                    <div class="typing-dots">
                                                                        <span></span><span></span><span></span>
                                                                        <?php echo __('Analisando dados da infraestrutura em tempo real...'); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info border-left-info shadow-sm mb-5">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-info-circle fa-2x mr-3"></i>
                                                <div>
                                                    <strong><?php echo __('Consultoria de IA Desativada:'); ?></strong>
                                                    <?php echo __('A análise preditiva estratégica foi desabilitada nas configurações do sistema.'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Sugestões baseadas na base de conhecimento local -->
                                    <div class="row">
                                        <?php if (count($sugestoes) > 0): ?>
                                            <?php foreach ($sugestoes as $problema => $dica): ?>
                                                <div class="col-md-6 mb-3">
                                                    <div class="p-3 card-insight shadow-sm bg-light rounded">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <div class="icon-box"><i class="fas fa-lightbulb text-warning"></i>
                                                            </div>
                                                            <div class="font-weight-bold text-dark">
                                                                <?php echo __('Checklist para:'); ?>
                                                                "<?php echo htmlspecialchars($problema); ?>"
                                                            </div>
                                                        </div>
                                                        <p class="mb-0 small text-secondary">
                                                            <?php echo htmlspecialchars($dica); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="col-12 text-center py-5">
                                                <i class="fas fa-magic fa-3x text-gray-300 mb-3"></i>
                                                <p class="text-muted">
                                                    <?php echo __('O sistema ainda está minerando padrões em seus dados.'); ?>
                                                    <br>
                                                    <?php echo __('Continue registrando chamados para ativar os insights automáticos.'); ?>
                                                </p>
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
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>

    <!-- Scripts de Funcionalidade -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="/assets/js/theme.js"></script>
    <script src="/assets/js/global_search.js"></script>
    <script>
        $(document).ready(function () {
            /**
             * Busca a análise preditiva gerada pela IA a partir do endpoint agent_insights.php
             * e realiza a formatação básica de markdown para HTML antes de exibir.
             */
            fetch('agent_insights.php')
                .then(response => response.json())
                .then(data => {
                    const textContainer = document.getElementById('ai-analysis-text');
                    if (data && data.reply) {
                        // Formatação simples de bold (**texto**) e quebras de linha
                        let reply = data.reply.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                        reply = reply.replace(/\n/g, '<br>');
                        textContainer.innerHTML = reply;
                        textContainer.classList.remove('font-italic');
                    } else {
                        textContainer.innerHTML = "<?php echo __('Nenhum insight estratégico encontrado no momento.'); ?>";
                    }
                })
                .catch(error => {
                    document.getElementById('ai-analysis-text').innerHTML = "⚠️ <?php echo __('Não foi possível conectar ao motor de consultoria por IA.'); ?>";
                    console.error('Error:', error);
                });
        });
    </script>
</body>

</html>