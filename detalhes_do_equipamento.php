<?php
// Inclui o arquivo de autenticação para garantir que o usuário esteja logado
include_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="pt-br"> <!-- Idioma configurado para português do Brasil -->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Detalhes do Ativo - Asset Management</title>
    <?php
    include 'conexao.php';
    // Obtém o ID do ativo via parâmetro GET
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (mysqli_num_rows($result_ativo) > 0) {
        $ativo = mysqli_fetch_assoc($result_ativo);

        // Configurações padrão de depreciação (caso não existam no banco)
        $dep_config = [
            'taxa_depreciacao' => 10.00,
            'periodo_anos' => 1,
            'periodo_meses' => 0,
            'elegivel_doacao' => 0,
            'tempo_doacao_anos' => 5,
            'tempo_doacao_meses' => 0
        ];

        // Busca as configurações reais de depreciação cadastradas no sistema
        $result_dep = mysqli_query($conn, "SELECT * FROM configuracoes_depreciacao LIMIT 1");
        if ($result_dep && mysqli_num_rows($result_dep) > 0) {
            $dep_config = mysqli_fetch_assoc($result_dep);
        }

        // Calcula o tempo total desde a ativação do ativo
        $data_ativacao = new DateTime($ativo['dataAtivacao']);
        $data_atual = new DateTime();
        $diff = $data_ativacao->diff($data_atual);
        $dias_ativos = $diff->days;

        // Lógica de cálculo da Depreciação Financeira
        $valor_original = floatval($ativo['valor']);
        $taxa_pct = floatval($dep_config['taxa_depreciacao']); // Taxa percentual (ex: 10%)
        $periodo_total_meses = (intval($dep_config['periodo_anos']) * 12) + intval($dep_config['periodo_meses']);

        if ($periodo_total_meses > 0 && $valor_original > 0) {
            // Calcula quantos meses o ativo está em operação desde sua data de ativação
            $meses_ativos = ($diff->y * 12) + $diff->m;
            // Determina quantos ciclos de depreciação completos ocorreram
            $periodos_completos = floor($meses_ativos / $periodo_total_meses);
            // O valor depreciado acumulado é limitado ao valor total do ativo (não pode depreciar abaixo de zero)
            $depreciacao_total = min($valor_original, $valor_original * ($taxa_pct / 100) * $periodos_completos);
            // Calcula o valor contábil atual do ativo
            $valor_atual = max(0, $valor_original - $depreciacao_total);
            // Calcula o percentual de vida util/depreciação para indicadores visuais
            $percentual_depreciado = min(100, round(($depreciacao_total / $valor_original) * 100, 1));
        } else {
            $depreciacao_total = 0;
            $valor_atual = $valor_original;
            $percentual_depreciado = 0;
        }

        // Regras de Elegibilidade para Doação
        $doacao_habilitada = intval($dep_config['elegivel_doacao']);
        $tempo_min_doacao_meses = (intval($dep_config['tempo_doacao_anos']) * 12) + intval($dep_config['tempo_doacao_meses']);
        $meses_desde_cadastro = ($diff->y * 12) + $diff->m;

        // Verifica se a categoria específica do ativo permite doação
        $categoria_ativo = $ativo['categoria'];
        $cat_elegivel = 1; // Assume elegível por padrão
        $result_cat_eleg = mysqli_query($conn, "SELECT elegivel_doacao FROM categoria_doacao WHERE categoria = '" . mysqli_real_escape_string($conn, $categoria_ativo) . "' LIMIT 1");
        if ($result_cat_eleg && mysqli_num_rows($result_cat_eleg) > 0) {
            $row_cat_eleg = mysqli_fetch_assoc($result_cat_eleg);
            $cat_elegivel = intval($row_cat_eleg['elegivel_doacao']);
        }

        // Determina o status textual e a cor do indicador de doação
        if (!$doacao_habilitada) {
            $status_doacao = "Doação Desativada Globalmente";
            $cor_doacao = "text-secondary";
        } elseif (!$cat_elegivel) {
            $status_doacao = "Categoria não elegível para doação";
            $cor_doacao = "text-warning";
        } elseif ($meses_desde_cadastro >= $tempo_min_doacao_meses) {
            $status_doacao = "Elegível para Doação ✅";
            $cor_doacao = "text-success";
        } else {
            // Se ainda não for elegível, calcula o tempo restante de carência
            $restante_meses = $tempo_min_doacao_meses - $meses_desde_cadastro;
            $anos_rest = floor($restante_meses / 12);
            $meses_rest = $restante_meses % 12;
            $tempo_str = '';
            if ($anos_rest > 0)
                $tempo_str .= $anos_rest . ' ano(s)';
            if ($anos_rest > 0 && $meses_rest > 0)
                $tempo_str .= ' e ';
            if ($meses_rest > 0)
                $tempo_str .= $meses_rest . ' mês(es)';
            if (empty($tempo_str))
                $tempo_str = 'menos de 1 mês';
            $status_doacao = "Bloqueado (Carência: " . $tempo_str . ")";
            $cor_doacao = "text-danger";
        }
    } else {
        // Encerra a execução se o ID do ativo for inválido ou não encontrado
        echo "<script>alert('Ativo não encontrado!'); window.location.href='equipamentos.php';</script>";
        exit;
    }

    // Define a imagem do ativo: usa a cadastrada ou uma padrão de placeholder
    $imagem = !empty($ativo['imagem']) ? $ativo['imagem'] : '/assets/img/dogs/image2.jpeg';
    ?>
    <!-- Links para Fontes e Bibliotecas CSS do Tema -->
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=ab31356e4f631a0a7556d48e827f1a2e">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=2fbfaadd1b3a8788aae69992363f994b">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=acb564fee2af37275d90d22a53249489">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=78d767d2c02420f5f2582c9908b00d3d">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=b38528c99809ba193f587f296d40fc27">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Animated-numbers-section.css?h=f70eceb0d9266e15c95f7e63479d6265">
    <link rel="stylesheet" href="/assets/css/Bootstrap-Image-Uploader.css?h=406ba72429389f6080fdb666c60fb216">
    <link rel="stylesheet" href="/assets/css/card-image-zoom-on-hover.css?h=82e6162bc70edfde8bfd14b57fdcb3f7">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet"
        href="/assets/css/Form-Select---Full-Date---Month-Day-Year.css?h=7b6a3c2cb7894fdb77bae43c70b92224">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightpick@1.3.4/css/lightpick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="/assets/css/Map-Clean.css?h=bdd15207233b27ebc7c6fc928c71b34c">
    <link rel="stylesheet" href="/assets/css/Modern-Contact-Form.css?h=af67b929d317df499a992472a9bb8fcc">
    <link rel="stylesheet"
        href="/assets/css/Multi-Select-Dropdown-by-Jigar-Mistry.css?h=28bd9d636c700fbf60086e2bcb002efb">
    <link rel="stylesheet"
        href="/assets/css/Password-Strenght-Checker---Ambrodu-1.css?h=1af6ac373aa34a3b40f3d87a4f494eaf">
    <link rel="stylesheet"
        href="/assets/css/Password-Strenght-Checker---Ambrodu.css?h=5818638767f362b9d58a96550bd9a9a3">
    <link rel="stylesheet" href="/assets/css/Simple-footer-by-krissy.css?h=73316da5ae5ad6b51632cd2e5413f263">
    <link rel="stylesheet" href="/assets/css/TR-Form.css?h=ce0bc58b5b8027e2406229d460f4d895">
    <?php include 'sidebar_style.php'; ?>
    <style>
        /* Estilização customizada dos botões do sistema para um visual premium */
        .btn-system {
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: none;
        }

        .btn-system:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            filter: brightness(1.1);
        }

        .btn-warning-system {
            background-color: #f6c23e;
            color: white;
        }

        .btn-danger-system {
            background-color: #e74a3b;
            color: white;
        }

        .btn-info-system {
            background-color: #36b9cc;
            color: white;
        }

        .btn-success-system {
            background-color: #1cc88a;
            color: white;
        }

        /* Estilo específico para processos de manutenção (Cor Laranja vibrante) */
        .btn-maintenance-system {
            background-color: #ff8c00 !important;
            border-color: #ff8c00 !important;
            color: white !important;
            transition: all 0.3s ease;
        }

        .btn-maintenance-system:hover {
            background-color: #e67e00 !important;
            border-color: #e67e00 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 140, 0, 0.4);
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Barra Lateral de Navegação -->
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);" aria-label="Menu Lateral">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include 'sidebar_brand.php'; ?>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <!-- Barra Superior (Topbar) -->
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top"
                    style="margin: 5px 23px;" aria-label="Menu Superior">
                    <div class="container-fluid">
                        <button class="btn btn-link d-md-none rounded-circle mr-3" id="sidebarToggleTop-1"
                            type="button"><i class="fas fa-bars"></i></button>
                    </div>
                </nav>
                <div class="container-fluid">
                    <h3 class="text-dark mb-4">Detalhes do Ativo</h3>
                    <div class="row mb-3">
                        <div class="col-lg-4">
                            <!-- Card com a imagem do ativo -->
                            <div class="card mb-3">
                                <div class="card-body text-center shadow">
                                    <img class="rounded-circle mb-3 mt-4" src="<?php echo $imagem; ?>" width="160"
                                        height="160" style="object-fit: cover; border: 3px solid #f8f9fc;"
                                        alt="Imagem do Equipamento">
                                    <div class="mb-3">
                                        <input type="file" id="foto-input" accept="image/*" style="display: none;"
                                            onchange="uploadFoto(this)">
                                        <button class="btn btn-primary btn-sm btn-system mx-auto" type="button"
                                            style="background: rgb(44,64,74); padding: 8px 15px; font-size: 0.85rem;"
                                            onclick="document.getElementById('foto-input').click();">
                                            <i class="fas fa-camera"></i> Alterar Foto
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Card de Ações Rápidas -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary font-weight-bold m-0">Ações Rápidas</h6>
                                </div>
                                <div class="card-body d-grid gap-3">
                                    <!-- Botão de edição do ativo -->
                                    <a href="editar_ativo.php?id=<?php echo $id; ?>"
                                        class="btn btn-warning-system btn-system btn-block text-white mb-3">
                                        <i class="fas fa-edit"></i> Editar Ativo
                                    </a>

                                    <!-- Alterna entre Ativar/Desativar dependendo do status atual -->
                                    <?php if ($ativo['status'] == 'Ativo'): ?>
                                        <button class="btn btn-danger-system btn-system btn-block mb-3"
                                            onclick="toggleStatus(<?php echo $id; ?>, 'Inativo')">
                                            <i class="fas fa-power-off"></i> Desativar
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-success-system btn-system btn-block mb-3"
                                            onclick="toggleStatus(<?php echo $id; ?>, 'Ativo')">
                                            <i class="fas fa-power-off"></i> Ativar
                                        </button>
                                    <?php endif; ?>


                                    <button class="btn btn-info-system btn-system btn-block" onclick="gerarPDF()">
                                        <i class="fas fa-file-pdf"></i> Gerar Relatório PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <!-- Seção de Detalhes Técnicos do Ativo -->
                                <div class="card shadow mb-3">
                                    <div class="card-header py-3">
                                        <p class="text-primary m-0 font-weight-bold">Informações do Ativo</p>
                                    </div>
                                    <div class="card-body">
                                        <form>
                                            <div class="form-row">
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label for="modelo"><strong>Modelo</strong></label>
                                                        <input id="modelo" class="form-control" type="text"
                                                            value="<?php echo htmlspecialchars($ativo['modelo']); ?>"
                                                            readonly>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label for="tag"><strong>Tag</strong></label>
                                                        <input id="tag" class="form-control" type="text"
                                                            value="<?php echo htmlspecialchars($ativo['tag']); ?>"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label for="fabricante"><strong>Fabricante</strong></label>
                                                        <input id="fabricante" class="form-control" type="text"
                                                            value="<?php echo htmlspecialchars($ativo['fabricante']); ?>"
                                                            readonly>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label for="categoria"><strong>Categoria</strong></label>
                                                        <input id="categoria" class="form-control" type="text"
                                                            value="<?php echo htmlspecialchars($ativo['categoria']); ?>"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="hostname"><strong>Hostname</strong></label>
                                                <input id="hostname" class="form-control" type="text"
                                                    value="<?php echo htmlspecialchars($ativo['hostName']); ?>"
                                                    readonly>
                                            </div>
                                            <div class="form-group">
                                                <label for="mac"><strong>MAC Address</strong></label>
                                                <input id="mac" class="form-control" type="text"
                                                    value="<?php echo htmlspecialchars($ativo['macAdress']); ?>"
                                                    readonly>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- Seção de Valores Financeiros e Status de Depreciação -->
                                <div class="card shadow">
                                    <div class="card-header py-3">
                                        <p class="text-primary m-0 font-weight-bold">Valores e Status</p>
                                    </div>
                                    <div class="card-body">
                                        <form>
                                            <div class="form-row">
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label for="data_cadastro"><strong>Data de
                                                                Cadastro</strong></label>
                                                        <input id="data_cadastro" class="form-control" type="text"
                                                            value="<?php echo date('d/m/Y', strtotime($ativo['dataAtivacao'])); ?>"
                                                            readonly>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label for="centro_custo"><strong>Centro de
                                                                Custo</strong></label>
                                                        <input id="centro_custo" class="form-control" type="text"
                                                            value="<?php echo htmlspecialchars($ativo['centroDeCusto']); ?>"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label for="valor_original"><strong>Valor
                                                                Original</strong></label>
                                                        <input id="valor_original" class="form-control" type="text"
                                                            value="R$ <?php echo number_format($valor_original, 2, ',', '.'); ?>"
                                                            readonly>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label for="valor_atual"><strong>Valor Atual</strong></label>
                                                        <input id="valor_atual" class="form-control" type="text"
                                                            value="R$ <?php echo number_format($valor_atual, 2, ',', '.'); ?>"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label for="taxa_depreciacao"><strong>Taxa de
                                                                Depreciação</strong></label>
                                                        <input id="taxa_depreciacao" class="form-control" type="text"
                                                            value="<?php echo number_format($taxa_pct, 2, ',', '.'); ?>% a cada <?php echo intval($dep_config['periodo_anos']); ?> ano(s) e <?php echo intval($dep_config['periodo_meses']); ?> mês(es)"
                                                            readonly>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label for="depreciacao_acumulada"><strong>Depreciação
                                                                Acumulada</strong></label>
                                                        <input id="depreciacao_acumulada"
                                                            class="form-control text-danger font-weight-bold"
                                                            type="text"
                                                            value="R$ <?php echo number_format($depreciacao_total, 2, ',', '.'); ?> (<?php echo number_format($percentual_depreciado, 1, ',', '.'); ?>%)"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="status_doacao"><strong>Status de Doação</strong></label>
                                                <input id="status_doacao"
                                                    class="form-control <?php echo $cor_doacao; ?> font-weight-bold"
                                                    type="text" value="<?php echo $status_doacao; ?>" readonly>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exibição de Alerta de Manutenção Ativa -->
                <?php if ($ativo['status'] == 'Manutencao' && !empty($ativo['manutencao_desc'])): ?>
                    <div class="card shadow mb-4 border-left-warning mx-4">
                        <div class="card-header py-3">
                            <p class="text-warning m-0 font-weight-bold"><i class="fas fa-tools"></i> Detalhes da Manutenção
                            </p>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <p><strong>Data de Início:</strong>
                                        <?php echo date('d/m/Y H:i', strtotime($ativo['manutencao_data'])); ?></p>
                                    <p><strong>Observações:</strong></p>
                                    <div class="p-3 bg-light border rounded">
                                        <?php echo nl2br(htmlspecialchars($ativo['manutencao_desc'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Descrição Textual Longa do Ativo -->
                <div class="card shadow mb-5 mx-4">
                    <div class="card-header py-3">
                        <p class="text-primary m-0 font-weight-bold">Descrição</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <form>
                                    <div class="form-group">
                                        <label for="descricao_detalhada"><strong>Detalhes Adicionais</strong></label>
                                        <textarea id="descricao_detalhada" class="form-control" rows="4"
                                            readonly><?php echo htmlspecialchars($ativo['descricao']); ?></textarea>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Histórico de Auditoria do Ativo -->
                <div class="card shadow mb-5 mx-4">
                    <div class="card-header py-3">
                        <p class="text-primary m-0 font-weight-bold">Histórico do Ativo</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="historyDataTable" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th scope="col">Data/Hora</th>
                                        <th scope="col">Ação</th>
                                        <th scope="col">Responsável</th>
                                        <th scope="col">Detalhes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql_historico = "SELECT h.*, u.nome, u.sobrenome FROM historico_ativos h LEFT JOIN usuarios u ON h.usuario_id = u.id_usuarios WHERE h.ativo_id = '$id' ORDER BY h.data_evento DESC";
                                    $result_historico = mysqli_query($conn, $sql_historico);

                                    if (mysqli_num_rows($result_historico) > 0) {
                                        while ($row = mysqli_fetch_assoc($result_historico)) {
                                            $usuario_nome = $row['nome'] ? $row['nome'] . ' ' . $row['sobrenome'] : 'Sistema';
                                            echo "<tr>";
                                            echo "<td>" . date('d/m/Y H:i', strtotime($row['data_evento'])) . "</td>";
                                            echo "<td>" . $row['acao'] . "</td>";
                                            echo "<td>" . $usuario_nome . "</td>";
                                            echo "<td>" . $row['detalhes'] . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center'>Nenhum histórico encontrado.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botão de Voltar ao Topo -->
    <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <!-- Scripts de Terceiros -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.js"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        /**
         * Realiza o upload assíncrono da foto do ativo através do endpoint upload_foto_ativo.php
         */
        function uploadFoto(input) {
            if (input.files && input.files[0]) {
                var formData = new FormData();
                formData.append('foto', input.files[0]);
                formData.append('id_asset', <?php echo $id; ?>);

                fetch('upload_foto_ativo.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Foto atualizada com sucesso!');
                            location.reload();
                        } else {
                            alert('Erro ao atualizar foto: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erro na requisição');
                    });
            }
        }

        /**
         * Altera o status do ativo (ex: Ativo para Inativo) após confirmação do usuário
         */
        function toggleStatus(id, novoStatus) {
            if (!confirm('Tem certeza que deseja alterar o status para ' + novoStatus + '?')) {
                return;
            }

            fetch('toggle_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_asset: id,
                    novo_status: novoStatus
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erro: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro na requisição');
                });
        }

        /**
         * Gera um relatório PDF formatado da página de detalhes do ativo,
         * ocultando elementos de interface desnecessários como menus e botões.
         */
        function gerarPDF() {
            var sidebar = document.querySelector('nav.sidebar');
            var topbar = document.querySelector('.topbar');
            var btnAcoes = document.querySelector('.card-body.d-grid').closest('.card');
            var cardFoto = document.querySelector('.col-lg-4 .card');

            // Esconde elementos que não devem aparecer no impresso
            if (sidebar) sidebar.style.display = 'none';
            if (topbar) topbar.style.display = 'none';
            if (btnAcoes) btnAcoes.style.display = 'none';
            if (cardFoto) cardFoto.style.display = 'none';

            var element = document.getElementById('content');
            var tag = '<?php echo addslashes($ativo["tag"]); ?>';

            var opt = {
                margin: [10, 10, 10, 10],
                filename: 'Relatorio_Ativo_' + tag + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, scrollY: 0 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(function () {
                // Restaura os elementos na tela após gerar o arquivo
                if (sidebar) sidebar.style.display = '';
                if (topbar) topbar.style.display = '';
                if (btnAcoes) btnAcoes.style.display = '';
                if (cardFoto) cardFoto.style.display = '';
            });
        }
    </script>
</body>

</html>