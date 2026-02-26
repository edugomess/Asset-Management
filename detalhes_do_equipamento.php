<?php include_once 'auth.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Detalhes do Equipamento</title>
    <?php
    include 'conexao.php';
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Buscar detalhes do ativo
    $sql_ativo = "SELECT a.*, m.observacoes AS manutencao_desc, m.data_inicio AS manutencao_data 
                 FROM ativos a 
                 LEFT JOIN manutencao m ON a.id_asset = m.id_asset AND m.status_manutencao = 'Em Manutenção'
                 WHERE a.id_asset = '$id'";
    $result_ativo = mysqli_query($conn, $sql_ativo);

    if (mysqli_num_rows($result_ativo) > 0) {
        $ativo = mysqli_fetch_assoc($result_ativo);

        // Buscar configurações de depreciação do banco
        $dep_config = [
            'taxa_depreciacao' => 10.00,
            'periodo_anos' => 1,
            'periodo_meses' => 0,
            'elegivel_doacao' => 0,
            'tempo_doacao_anos' => 5,
            'tempo_doacao_meses' => 0
        ];
        $result_dep = mysqli_query($conn, "SELECT * FROM configuracoes_depreciacao LIMIT 1");
        if ($result_dep && mysqli_num_rows($result_dep) > 0) {
            $dep_config = mysqli_fetch_assoc($result_dep);
        }

        // Calcular tempo desde cadastro
        $data_ativacao = new DateTime($ativo['dataAtivacao']);
        $data_atual = new DateTime();
        $diff = $data_ativacao->diff($data_atual);
        $dias_ativos = $diff->days;

        // Depreciação baseada nas configurações
        $valor_original = floatval($ativo['valor']);
        $taxa_pct = floatval($dep_config['taxa_depreciacao']); // ex: 10 = 10%
        $periodo_total_meses = (intval($dep_config['periodo_anos']) * 12) + intval($dep_config['periodo_meses']);

        if ($periodo_total_meses > 0 && $valor_original > 0) {
            // Quantos períodos completos já se passaram
            $meses_ativos = ($diff->y * 12) + $diff->m;
            $periodos_completos = floor($meses_ativos / $periodo_total_meses);
            // Depreciação acumulada (nunca ultrapassa o valor original)
            $depreciacao_total = min($valor_original, $valor_original * ($taxa_pct / 100) * $periodos_completos);
            $valor_atual = max(0, $valor_original - $depreciacao_total);
            $percentual_depreciado = min(100, round(($depreciacao_total / $valor_original) * 100, 1));
        } else {
            $depreciacao_total = 0;
            $valor_atual = $valor_original;
            $percentual_depreciado = 0;
        }

        // Elegibilidade para doação baseada nas configurações
        $doacao_habilitada = intval($dep_config['elegivel_doacao']);
        $tempo_min_doacao_meses = (intval($dep_config['tempo_doacao_anos']) * 12) + intval($dep_config['tempo_doacao_meses']);
        $meses_desde_cadastro = ($diff->y * 12) + $diff->m;

        // Verificar elegibilidade por categoria
        $categoria_ativo = $ativo['categoria'];
        $cat_elegivel = 1; // Default: elegível
        $result_cat_eleg = mysqli_query($conn, "SELECT elegivel_doacao FROM categoria_doacao WHERE categoria = '" . mysqli_real_escape_string($conn, $categoria_ativo) . "' LIMIT 1");
        if ($result_cat_eleg && mysqli_num_rows($result_cat_eleg) > 0) {
            $row_cat_eleg = mysqli_fetch_assoc($result_cat_eleg);
            $cat_elegivel = intval($row_cat_eleg['elegivel_doacao']);
        }

        if (!$doacao_habilitada) {
            $status_doacao = "Doação Desativada";
            $cor_doacao = "text-secondary";
        } elseif (!$cat_elegivel) {
            $status_doacao = "Categoria não elegível para doação";
            $cor_doacao = "text-warning";
        } elseif ($meses_desde_cadastro >= $tempo_min_doacao_meses) {
            $status_doacao = "Elegível para Doação";
            $cor_doacao = "text-success";
        } else {
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
        echo "<script>alert('Ativo não encontrado!'); window.location.href='equipamentos.php';</script>";
        exit;
    }

    // Determinar imagem
    $imagem = !empty($ativo['imagem']) ? $ativo['imagem'] : '/assets/img/dogs/image2.jpeg';
    ?>
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
            background-color: #e74a3b;
            color: white;
        }

        .btn-success-system {
            background-color: #1cc88a;
            color: white;
        }

        .btn-maintenance-system {
            background-color: #f6953e;
            color: white;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0"><a
                    class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#">
                    <div class="sidebar-brand-icon rotate-n-15"><svg xmlns="http://www.w3.org/2000/svg" width="1em"
                            height="1em" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icon-tabler-layout-distribute-horizontal"
                            style="width: 30px;height: 30px;">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <line x1="4" y1="4" x2="20" y2="4"></line>
                            <line x1="4" y1="20" x2="20" y2="20"></line>
                            <rect x="6" y="9" width="12" height="6" rx="2"></rect>
                        </svg></div>
                    <div class="sidebar-brand-text mx-3"><span>ASSET MGT</span></div>
                </a>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top"
                    style="margin: 5px 23px;">
                    <div class="container-fluid">
                        <button class="btn btn-link d-md-none rounded-circle mr-3" id="sidebarToggleTop-1"
                            type="button"><i class="fas fa-bars"></i></button>
                    </div>
                </nav>
                <div class="container-fluid">
                    <h3 class="text-dark mb-4">Detalhes do Ativo</h3>
                    <div class="row mb-3">
                        <div class="col-lg-4">
                            <div class="card mb-3">
                                <div class="card-body text-center shadow">
                                    <img class="rounded-circle mb-3 mt-4" src="<?php echo $imagem; ?>" width="160"
                                        height="160" style="object-fit: cover; border: 3px solid #f8f9fc;">
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
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary font-weight-bold m-0">Ações Rápidas</h6>
                                </div>
                                <div class="card-body d-grid gap-3">
                                    <a href="editar_ativo.php?id=<?php echo $id; ?>"
                                        class="btn btn-warning-system btn-system btn-block text-white mb-3">
                                        <i class="fas fa-edit"></i> Editar Ativo
                                    </a>
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
                                <div class="card shadow mb-3">
                                    <div class="card-header py-3">
                                        <p class="text-primary m-0 font-weight-bold">Informações do Ativo</p>
                                    </div>
                                    <div class="card-body">
                                        <form>
                                            <div class="form-row">
                                                <div class="col">
                                                    <div class="form-group"><label
                                                            for="modelo"><strong>Modelo</strong></label><input
                                                            class="form-control" type="text"
                                                            value="<?php echo htmlspecialchars($ativo['modelo']); ?>"
                                                            readonly></div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group"><label
                                                            for="tag"><strong>Tag</strong></label><input
                                                            class="form-control" type="text"
                                                            value="<?php echo htmlspecialchars($ativo['tag']); ?>"
                                                            readonly></div>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="col">
                                                    <div class="form-group"><label
                                                            for="fabricante"><strong>Fabricante</strong></label><input
                                                            class="form-control" type="text"
                                                            value="<?php echo htmlspecialchars($ativo['fabricante']); ?>"
                                                            readonly></div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group"><label
                                                            for="categoria"><strong>Categoria</strong></label><input
                                                            class="form-control" type="text"
                                                            value="<?php echo htmlspecialchars($ativo['categoria']); ?>"
                                                            readonly></div>
                                                </div>
                                            </div>
                                            <div class="form-group"><label
                                                    for="hostname"><strong>Hostname</strong></label><input
                                                    class="form-control" type="text"
                                                    value="<?php echo htmlspecialchars($ativo['hostName']); ?>"
                                                    readonly></div>
                                            <div class="form-group"><label for="mac"><strong>MAC
                                                        Address</strong></label><input class="form-control" type="text"
                                                    value="<?php echo htmlspecialchars($ativo['macAdress']); ?>"
                                                    readonly></div>
                                        </form>
                                    </div>
                                </div>
                                <div class="card shadow">
                                    <div class="card-header py-3">
                                        <p class="text-primary m-0 font-weight-bold">Valores e Status</p>
                                    </div>
                                    <div class="card-body">
                                        <form>
                                            <div class="form-row">
                                                <div class="col">
                                                    <div class="form-group"><label><strong>Data de
                                                                Cadastro</strong></label><input class="form-control"
                                                            type="text"
                                                            value="<?php echo date('d/m/Y', strtotime($ativo['dataAtivacao'])); ?>"
                                                            readonly></div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group"><label><strong>Centro de
                                                                Custo</strong></label><input class="form-control"
                                                            type="text"
                                                            value="<?php echo htmlspecialchars($ativo['centroDeCusto']); ?>"
                                                            readonly></div>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="col">
                                                    <div class="form-group"><label><strong>Valor
                                                                Original</strong></label><input class="form-control"
                                                            type="text"
                                                            value="R$ <?php echo number_format($valor_original, 2, ',', '.'); ?>"
                                                            readonly></div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group"><label><strong>Valor
                                                                Atual</strong></label><input class="form-control"
                                                            type="text"
                                                            value="R$ <?php echo number_format($valor_atual, 2, ',', '.'); ?>"
                                                            readonly></div>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="col">
                                                    <div class="form-group"><label><strong>Taxa de
                                                                Depreciação</strong></label><input class="form-control"
                                                            type="text"
                                                            value="<?php echo number_format($taxa_pct, 2, ',', '.'); ?>% a cada <?php echo intval($dep_config['periodo_anos']); ?> ano(s) e <?php echo intval($dep_config['periodo_meses']); ?> mês(es)"
                                                            readonly></div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group"><label><strong>Depreciação
                                                                Acumulada</strong></label><input
                                                            class="form-control text-danger" type="text"
                                                            value="R$ <?php echo number_format($depreciacao_total, 2, ',', '.'); ?> (<?php echo number_format($percentual_depreciado, 1, ',', '.'); ?>%)"
                                                            readonly style="font-weight: bold;"></div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label><strong>Status de Doação</strong></label>
                                                <input class="form-control <?php echo $cor_doacao; ?>" type="text"
                                                    value="<?php echo $status_doacao; ?>" readonly
                                                    style="font-weight: bold;">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($ativo['status'] == 'Manutencao' && !empty($ativo['manutencao_desc'])): ?>
                    <div class="card shadow mb-4 border-left-warning">
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
                <div class="card shadow mb-5">
                    <div class="card-header py-3">
                        <p class="text-primary m-0 font-weight-bold">Descrição</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <form>
                                    <div class="form-group">
                                        <label for="signature"><strong>Detalhes Adicionais</strong></label>
                                        <textarea class="form-control" rows="4"
                                            readonly><?php echo htmlspecialchars($ativo['descricao']); ?></textarea>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow mb-5">
                    <div class="card-header py-3">
                        <p class="text-primary m-0 font-weight-bold">Histórico do Ativo</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Data/Hora</th>
                                        <th>Ação</th>
                                        <th>Responsável pela ação</th>
                                        <th>Detalhes</th>
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

    </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightpick@1.3.4/lightpick.min.js"></script>
    <script src="/assets/js/Date-Range-Picker.js?h=1d598b35ada76eb401b3897ae4b61ccb"></script>
    <script src="/assets/js/Animated-numbers-section.js?h=a0ec092b1194013aa3c8e220b0938a52"></script>
    <script src="/assets/js/Bootstrap-Image-Uploader.js?h=2218f85124ce4687cddacceb8e123cc9"></script>
    <script src="/assets/js/DateRangePicker.js?h=e84100887465fbb69726c415c180211a"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.2.0/zxcvbn.js"></script>
    <script src="/assets/js/Multi-Select-Dropdown-by-Jigar-Mistry.js?h=45421b0ed6bd109b4f00e752ae5bf3e5"></script>
    <script src="/assets/js/Password-Strenght-Checker---Ambrodu.js?h=f40a32e3d989fd0e00bf2f0567e52e27"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
    <script>
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

        function toggleStatus(id, novoStatus) {
            if (!confirm('Tem certeza que deseja alterar o status para ' + novoStatus + '?')) {
                return;
            }

            fetch('toggle_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
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
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function gerarPDF() {
            // Esconder botões antes de gerar
            var btns = document.querySelectorAll('.btn');
            var sidebar = document.getElementById('wrapper').querySelector('nav.sidebar');
            var topbar = document.querySelector('.topbar');
            var btnPdf = event.target.closest('button');
            var acoesCard = btnPdf.closest('.card');
            var fotoCard = acoesCard.previousElementSibling;

            // Guardar estado original
            var sidebarDisplay = sidebar ? sidebar.style.display : '';
            var topbarDisplay = topbar ? topbar.style.display : '';
            var acoesDisplay = acoesCard.style.display;
            var fotoDisplay = fotoCard ? fotoCard.style.display : '';

            // Esconder elementos desnecessários no PDF
            if (sidebar) sidebar.style.display = 'none';
            if (topbar) topbar.style.display = 'none';
            acoesCard.style.display = 'none';
            if (fotoCard) fotoCard.style.display = 'none';

            var element = document.getElementById('content');
            var tag = '<?php echo addslashes($ativo["tag"]); ?>';
            var modelo = '<?php echo addslashes($ativo["modelo"]); ?>';

            var opt = {
                margin: [10, 10, 10, 10],
                filename: 'Relatorio_Ativo_' + tag + '.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    scrollY: 0
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                },
                pagebreak: {
                    mode: ['avoid-all', 'css', 'legacy']
                }
            };

            html2pdf().set(opt).from(element).save().then(function () {
                // Restaurar elementos
                if (sidebar) sidebar.style.display = sidebarDisplay;
                if (topbar) topbar.style.display = topbarDisplay;
                acoesCard.style.display = acoesDisplay;
                if (fotoCard) fotoCard.style.display = fotoDisplay;
            });
        }
    </script>
</body>

</html>