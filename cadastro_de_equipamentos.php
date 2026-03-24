<?php
/**
 * CADASTRO DE EQUIPAMENTOS: cadastro_de_equipamentos.php
 * Interface para inclusão de novos itens ao inventário de hardware.
 */
include_once 'auth.php'; // Proteção de sessão
?>
<!DOCTYPE html>
<html lang="<?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'pt-BR') ? 'pt-br' : 'en'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Cadastro de Equipamentos'); ?> - Asset MGT</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=3265483e434712d72c41db9eebc4c8bb">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=d6a29779d310462e7fcdde7b9a80e0db">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=5f41e73f827c7b56616237a1da13b6e2">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=19488c1c6619bc9bd5c02de5f7ffbfd4">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=193916adb9d7af47fe74d9a2270caac3">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <?php include_once 'sidebar_style.php'; ?>
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
                    <h3 class="text-dark mb-1"><?php echo __('Cadastro de Ativo'); ?></h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <form id="assetForm" action="inserir_equipamento.php" method="post" enctype="multipart/form-data">
                                <!-- Row 1: Identificação Básica -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="categoria"><?php echo __('Categoria'); ?></label>
                                            <select class="form-control" name="categoria" id="categoria" required="">
                                                <?php
                                                include_once 'conexao.php';
                                                $sql = "SELECT categoria FROM categoria";
                                                $result = $conn->query($sql);
                                                if ($result && $result->num_rows > 0) {
                                                    while ($row = $result->fetch_assoc()) {
                                                        echo '<option value="' . $row['categoria'] . '">' . __($row['categoria']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="fabricante"><?php echo __('Fabricante'); ?></label>
                                            <input class="form-control" name="fabricante" id="fabricante" type="text" placeholder="<?php echo __('Ex: Dell, HP, Apple'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="modelo"><?php echo __('Modelo'); ?></label>
                                            <input class="form-control" name="modelo" id="modelo" type="text" placeholder="<?php echo __('Ex: Latitude 3420'); ?>" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 2: Rastreabilidade -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="tag"><?php echo __('Tag / Service Tag'); ?></label>
                                            <?php
                                            $res_next = mysqli_query($conn, "SHOW TABLE STATUS LIKE 'ativos'");
                                            $row_next = mysqli_fetch_assoc($res_next);
                                            $next_id = $row_next['Auto_increment'];
                                            $preview_tag = 'TAG-' . str_pad($next_id, 6, '0', STR_PAD_LEFT);
                                            ?>
                                            <input class="form-control" name="tag" id="tag" type="text" value="<?php echo $preview_tag; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="hostnameContainer">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="hostName"><?php echo __('Host Name'); ?></label>
                                            <input class="form-control" name="hostName" id="hostName" type="text" placeholder="<?php echo __('Ex: NOTE-001'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="macAdress"><?php echo __('Endereço MAC'); ?></label>
                                            <input class="form-control" name="macAdress" id="macAdress" type="text" placeholder="<?php echo __('Ex: 00:00:00:00:00:00'); ?>" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 3: Financeiro e Organização -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="valor"><?php echo __('Valor do Ativo (R$)'); ?></label>
                                            <input class="form-control" name="valor" id="valor" type="number" step="0.01" placeholder="<?php echo __('Ex: 4500.00'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="centroDeCusto"><?php echo __('Centro de Custo'); ?></label>
                                            <select class="form-control" name="centroDeCusto" id="centroDeCusto">
                                                <option value="Nenhum"><?php echo __('Nenhum'); ?></option>
                                                <?php
                                                $sql_cc = "SELECT nomeSetor FROM centro_de_custo ORDER BY nomeSetor ASC";
                                                $res_cc = $conn->query($sql_cc);
                                                if ($res_cc && $res_cc->num_rows > 0) {
                                                    while ($row_cc = $res_cc->fetch_assoc()) {
                                                        echo '<option value="' . $row_cc['nomeSetor'] . '">' . __($row_cc['nomeSetor']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="setor"><?php echo __('Setor Destinado'); ?></label>
                                            <input class="form-control" name="setor" id="setor" type="text" placeholder="<?php echo __('Ex: Marketing, RH, TI'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="fornecedor"><?php echo __('Fornecedor'); ?></label>
                                            <select class="form-control" name="fornecedor" id="fornecedor">
                                                <option value="Nenhum"><?php echo __('Nenhum'); ?></option>
                                                <?php
                                                $sql_for = "SELECT nomeEmpresa FROM fornecedor ORDER BY nomeEmpresa ASC";
                                                $res_for = $conn->query($sql_for);
                                                if ($res_for && $res_for->num_rows > 0) {
                                                    while ($row_for = $res_for->fetch_assoc()) {
                                                        echo '<option value="' . htmlspecialchars($row_for['nomeEmpresa']) . '">' . htmlspecialchars($row_for['nomeEmpresa']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 4: Dados Compra -->
                                <h5 class="text-primary font-weight-bold mt-4 mb-3"><?php echo __('Dados de Compra / Nota Fiscal'); ?></h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="numero_nota_fiscal"><?php echo __('Nota Fiscal/Chave de Acesso'); ?></label>
                                            <input class="form-control" name="numero_nota_fiscal" id="numero_nota_fiscal" type="text" placeholder="Ex: 35260312345678000190550010000458921876543210" required pattern="\d{44}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="anexo_nota_fiscal"><?php echo __('Anexar Nota Fiscal (PDF/Imagem)'); ?></label>
                                            <input class="form-control-file" name="anexo_nota_fiscal" id="anexo_nota_fiscal" type="file" accept=".pdf,image/*">
                                        </div>
                                    </div>
                                </div>

                                <!-- Hardware Section -->
                                <div id="hardwareSection" style="display: none; border-left: 4px solid #2c404a; padding-left: 15px; margin-bottom: 25px; background: #f8f9fc; border-radius: 5px;">
                                    <h5 class="text-primary font-weight-bold mb-3"><?php echo __('Especificações de Hardware'); ?></h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="text-gray-600 small font-weight-bold" for="memoria"><?php echo __('Memória RAM'); ?></label>
                                                <input class="form-control" name="memoria" id="memoria" type="text" placeholder="Ex: 16GB DDR4">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="text-gray-600 small font-weight-bold" for="processador"><?php echo __('Processador'); ?></label>
                                                <input class="form-control" name="processador" id="processador" type="text" placeholder="Ex: Intel i7">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="text-gray-600 small font-weight-bold" for="armazenamento"><?php echo __('Capacidade Armazenamento'); ?></label>
                                                <input class="form-control" name="armazenamento" id="armazenamento" type="text" placeholder="Ex: 512GB">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="text-gray-600 small font-weight-bold" for="tipo_armazenamento"><?php echo __('Tipo de Armazenamento'); ?></label>
                                                <select class="form-control" name="tipo_armazenamento" id="tipo_armazenamento">
                                                    <option value="SSD">SSD</option>
                                                    <option value="HD">HD</option>
                                                    <option value="NVMe">NVMe</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Outros Dados -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="dataAtivacao"><?php echo __('Data de Cadastro'); ?></label>
                                            <input class="form-control" name="dataAtivacao" id="dataAtivacao" type="date" value="<?php echo date('Y-m-d'); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="imagem"><?php echo __('Imagem do Ativo'); ?></label>
                                            <input class="form-control-file" name="imagem" id="imagem" type="file" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="custom-control custom-switch" style="margin-top: 32px;">
                                            <input type="hidden" name="status" value="Inativo">
                                            <input type="checkbox" class="custom-control-input" id="statusSwitch" name="status" value="Ativo" checked>
                                            <label class="custom-control-label" for="statusSwitch"><?php echo __('Ativo'); ?></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="descricao"><?php echo __('Observações Adicionais'); ?></label>
                                            <textarea class="form-control" name="descricao" id="descricao" style="height: 80px;"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4 mb-3">
                                    <div class="col-12 d-flex justify-content-end align-items-center" style="gap: 15px;">
                                        <a class="btn btn-secondary" href="equipamentos.php" style="border-radius: 10px; padding: 10px 30px; font-weight: 600;"><?php echo __('Voltar'); ?></a>
                                        <button class="btn btn-primary" type="submit" style="background: #2c404a; border: none; border-radius: 10px; padding: 10px 30px; font-weight: 600;">
                                            <i class="fas fa-save mr-2"></i><?php echo __('Cadastrar Equipamento'); ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>

    <!-- Modal Sucesso Cadastro com Etiqueta -->
    <div class="modal fade" id="successTagModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 bg-light" style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <h5 class="modal-title font-weight-bold text-success"><i class="fas fa-check-circle mr-2"></i><?php echo __('Equipamento Cadastrado!'); ?></h5>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="mb-4 text-muted"><?php echo __('A etiqueta patrimonial já está pronta para identificação física.'); ?></p>
                    <div class="p-3 bg-white d-inline-block rounded shadow-sm mb-3" style="border: 1px dashed #ddd;"><div id="qrcode_reg"></div></div>
                    <div class="mt-2"><span class="badge badge-dark p-2" id="tag_badge_reg" style="font-size: 1.2rem; border-radius: 10px; letter-spacing: 1px;"></span></div>
                </div>
                <div class="modal-footer border-0 p-4 flex-column">
                    <button type="button" class="btn btn-primary btn-block p-3 font-weight-bold mb-2" id="btn-print-reg" style="border-radius: 12px;"><i class="fas fa-print mr-2"></i><?php echo __('Imprimir Etiqueta Agora'); ?></button>
                    <div class="d-flex w-100" style="gap: 10px;">
                        <button type="button" class="btn btn-outline-secondary flex-fill" onclick="location.reload();" style="border-radius: 10px;"><?php echo __('Novo Cadastro'); ?></button>
                        <a href="equipamentos.php" class="btn btn-outline-primary flex-fill" style="border-radius: 10px;"><?php echo __('Ver Inventário'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentAssetData = null;
        $(document).ready(function() {
            function toggleFields() {
                var cat = $('#categoria').val();
                if (cat === 'Notebook' || cat === 'Desktop' || cat === 'Servidores') { $('#hardwareSection').slideDown(); } else { $('#hardwareSection').slideUp(); }
                if (cat === 'Monitor' || cat === 'Periféricos') { $('#hostnameContainer').slideUp(); $('#hostName').removeAttr('required'); } else { $('#hostnameContainer').slideDown(); $('#hostName').attr('required', 'required'); }
            }
            $('#categoria').change(toggleFields);
            toggleFields();

            $('#assetForm').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('ajax', '1');
                Swal.fire({ title: 'Salvando...', didOpen: () => { Swal.showLoading(); } });
                $.ajax({
                    url: 'inserir_equipamento.php?ts=' + new Date().getTime(),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        try {
                            const res = typeof response === 'object' ? response : JSON.parse(response);
                            if (res.success) {
                                Swal.close();
                                currentAssetData = res;
                                $('#tag_badge_reg').text(res.tag);
                                $('#qrcode_reg').empty();
                                new QRCode(document.getElementById("qrcode_reg"), { text: `ID: ${res.id_asset}\nTag: ${res.tag}\nModelo: ${res.modelo}`, width: 180, height: 180 });
                                $('#successTagModal').modal('show');
                            } else { 
                                Swal.fire('Erro de Negócio', res.message, 'error'); 
                            }
                        } catch (e) {
                            console.error("DEBUG - Resposta bruta do servidor:", response);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro de Análise JSON',
                                html: `O servidor retornou um formato inválido.<br><br><b>Conteúdo:</b><br><pre style="text-align:left; font-size:10px;">${response.substring(0, 200)}...</pre>`,
                                footer: 'Dica: Verifique o console do navegador (F12) para o log completo.'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error("DEBUG - Erro de Rede:", xhr.status, xhr.responseText);
                        Swal.fire('Erro de Rede', `Falha no servidor (Status ${xhr.status}).<br>${xhr.responseText.substring(0, 100)}`, 'error');
                    }
                });
            });

            $('#btn-print-reg').on('click', function() {
                const qrImg = document.querySelector('#qrcode_reg img').src;
                const printWindow = window.open('', '_blank', 'width=400,height=500');
                printWindow.document.write(`
                    <html><head><style>
                        body { margin: 0; padding: 20px; text-align: center; font-family: sans-serif; }
                        .label-container { width: 220px; padding: 10px; }
                        img { width: 180px; height: 180px; }
                        .tag { font-size: 1.4rem; font-weight: bold; border-top: 2px solid #000; margin-top: 10px; padding-top: 5px; }
                        .model { font-size: 0.8rem; color: #666; margin-bottom: 5px; text-transform: uppercase; }
                    </style></head>
                    <body onload="window.print(); window.close();">
                        <div class="model">${currentAssetData.modelo}</div>
                        <img src="${qrImg}">
                        <div class="tag">${currentAssetData.tag}</div>
                    </body></html>
                `);
                printWindow.document.close();
            });
        });
    </script>
</body>
</html>