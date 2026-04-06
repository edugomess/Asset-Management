<?php
/**
 * CADASTRO DE EQUIPAMENTOS: cadastro_de_equipamentos.php
 * Interface para inclusão de novos itens ao inventário de hardware.
 */
include_once 'auth.php'; // Proteção de sessão
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">

<head>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css">
    <title><?php echo __('Cadastro de Ativo'); ?> - Asset MGT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <?php include_once 'sidebar_style.php'; ?>
    <script>
        // --- FUNÇÃO DE IMPRESSÃO (DEFINIDA NO TOPO PARA SEGURANÇA) ---
        function printCreatedAsset() {
            if (typeof currentAssetData !== 'undefined' && currentAssetData && currentAssetData.id_asset) {
                const printUrl = `imprimir_etiqueta.php?id=${currentAssetData.id_asset}`;
                let printFrame = document.getElementById('print_iframe');
                if (!printFrame) {
                    printFrame = document.createElement('iframe');
                    printFrame.id = 'print_iframe';
                    printFrame.style.display = 'none';
                    document.body.appendChild(printFrame);
                }
                printFrame.src = printUrl;
            }
        }
    </script>
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
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="numero_serie"><?php echo __('Número de Série'); ?></label>
                                            <input class="form-control" name="numero_serie" id="numero_serie" type="text" placeholder="<?php echo __('Ex: 123456789'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="tag"><?php echo __('Tag de Serviço'); ?></label>
                                            <?php
                                            $res_next = mysqli_query($conn, "SHOW TABLE STATUS LIKE 'ativos'");
                                            $row_next = mysqli_fetch_assoc($res_next);
                                            $next_id = $row_next['Auto_increment'];
                                            $preview_tag = 'TAG-' . str_pad($next_id, 6, '0', STR_PAD_LEFT);
                                            ?>
                                            <input class="form-control" name="tag" id="tag" type="text" value="<?php echo $preview_tag; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3" id="hostnameContainer">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="hostName"><?php echo __('Host Name'); ?></label>
                                            <input class="form-control" name="hostName" id="hostName" type="text" placeholder="<?php echo __('Ex: NOTE-001'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="macAdress"><?php echo __('Endereço MAC'); ?></label>
                                            <input class="form-control" name="macAdress" id="macAdress" type="text" placeholder="<?php echo __('Ex: 00:00:00:00:00:00'); ?>" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 3: Atribuição e Localização -->
                                <h5 class="text-primary font-weight-bold mt-4 mb-3"><?php echo __('Atribuição e Localização'); ?></h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="assigned_type"><?php echo __('Tipo de Atribuição'); ?></label>
                                            <select class="form-control" name="assigned_type" id="assigned_type">
                                                <option value="Usuario"><?php echo __('Responsabilidade Individual (Usuário)'); ?></option>
                                                <option value="Local"><?php echo __('Responsabilidade Coletiva (Infraestrutura)'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3" id="user_assignment">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="assigned_to"><?php echo __('Atribuído a'); ?></label>
                                            <select class="form-control" name="assigned_to" id="assigned_to">
                                                <option value=""><?php echo __('Ninguém (Disponível)'); ?></option>
                                                <?php
                                                $sql_users = "SELECT id_usuarios, nome, sobrenome FROM usuarios WHERE status = 'Ativo' ORDER BY nome ASC";
                                                $res_users = $conn->query($sql_users);
                                                while($u = $res_users->fetch_assoc()) {
                                                    echo "<option value='{$u['id_usuarios']}'>{$u['nome']} {$u['sobrenome']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3" id="location_assignment" style="display: none;">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="id_local"><?php echo __('Local de Instalação'); ?></label>
                                            <select class="form-control" name="id_local" id="id_local">
                                                <option value=""><?php echo __('Selecione o Local...'); ?></option>
                                                <?php
                                                function listLocais($conn, $parentId = null, $prefix = '') {
                                                    $sql = $parentId === null ? "SELECT * FROM locais WHERE id_parent_local IS NULL" : "SELECT * FROM locais WHERE id_parent_local = $parentId";
                                                    $res = $conn->query($sql);
                                                    while($l = $res->fetch_assoc()) {
                                                        echo "<option value='{$l['id_local']}'>$prefix {$l['nome_local']} ({$l['tipo_local']})</option>";
                                                        listLocais($conn, $l['id_local'], $prefix . '— ');
                                                    }
                                                }
                                                listLocais($conn);
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="parent_asset_id"><?php echo __('Vincular a Ativo Pai'); ?></label>
                                            <select class="form-control" name="parent_asset_id" id="parent_asset_id">
                                                <option value=""><?php echo __('Nenhum'); ?></option>
                                                <?php
                                                $sql_assets = "SELECT id_asset, tag, modelo FROM ativos WHERE categoria IN ('Notebook', 'Desktop', 'Servidores', 'Workstation', 'Firewalls', 'Switches') ORDER BY tag ASC";
                                                $res_assets = $conn->query($sql_assets);
                                                while($a = $res_assets->fetch_assoc()) {
                                                    echo "<option value='{$a['id_asset']}'>{$a['tag']} - {$a['modelo']}</option>";
                                                }
                                                ?>
                                            </select>
                                            <small class="text-muted"><?php echo __('Ex: Monitor vinculado a um Desktop.'); ?></small>
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
                                </div>

                                <!-- Row 3.5: Organização (Tier/Setor) -->
                                <div class="row">
                                    <div class="col-md-6" id="tier_container">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="tier"><?php echo __('Tier / Nível de Atribuição'); ?></label>
                                            <select class="form-control" name="tier" id="tier">
                                                <option value=""><?php echo __('Nenhum'); ?></option>
                                                <option value="Tier 1"><?php echo __('Tier 1'); ?></option>
                                                <option value="Tier 2"><?php echo __('Tier 2'); ?></option>
                                                <option value="Tier 3"><?php echo __('Tier 3'); ?></option>
                                                <option value="Tier 4"><?php echo __('Tier 4'); ?></option>
                                                <option value="Infraestrutura"><?php echo __('Infraestrutura'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="setor_container">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="setor"><?php echo __('Setor / Departamento'); ?></label>
                                            <input class="form-control" name="setor" id="setor" type="text" placeholder="<?php echo __('Ex: Financeiro'); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 4: Financeiro e Organização -->
                                <h5 class="text-primary font-weight-bold mt-4 mb-3"><?php echo __('Dados Financeiros e Status'); ?></h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="valor"><?php echo __('Valor do Ativo (R$)'); ?></label>
                                            <input class="form-control" name="valor" id="valor" type="number" step="0.01" placeholder="<?php echo __('Ex: 4500.00'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="status_new"><?php echo __('Status do Ativo'); ?></label>
                                            <select class="form-control" name="status" id="status_new">
                                                <option value="Disponível"><?php echo __('Disponível'); ?></option>
                                                <option value="Em uso" selected><?php echo __('Em uso'); ?></option>
                                                <option value="Em manutenção"><?php echo __('Em manutenção'); ?></option>
                                                <option value="Descartado/Leilão"><?php echo __('Descartado/Leilão'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
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
                                            <input class="form-control" name="numero_nota_fiscal" id="numero_nota_fiscal" type="text" placeholder="<?php echo __('Ex: 352603...'); ?>" required pattern="\d{44}">
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
                                                <input class="form-control" name="memoria" id="memoria" type="text" placeholder="<?php echo __('Ex: 16GB DDR4'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="text-gray-600 small font-weight-bold" for="processador"><?php echo __('Processador'); ?></label>
                                                <input class="form-control" name="processador" id="processador" type="text" placeholder="<?php echo __('Ex: Intel i7'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="text-gray-600 small font-weight-bold" for="armazenamento"><?php echo __('Capacidade Armazenamento'); ?></label>
                                                <input class="form-control" name="armazenamento" id="armazenamento" type="text" placeholder="<?php echo __('Ex: 512GB'); ?>">
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
                                    <div class="row">
                                        <div class="col-md-12 mt-3" id="gpu_container" style="display: none;">
                                            <div class="form-group mb-0">
                                                <label class="text-gray-600 small font-weight-bold" for="gpu"><?php echo __('Placa Gráfica (GPU)'); ?></label>
                                                <input class="form-control" name="gpu" id="gpu" type="text" placeholder="<?php echo __('Ex: NVIDIA RTX 4090 24GB'); ?>">
                                            </div>
                                        </div>
                                        <!-- Polegadas (Monitor) -->
                                        <div class="col-md-12 mt-3" id="inches_container" style="display: none;">
                                            <div class="form-group mb-0">
                                                <label class="text-gray-600 small font-weight-bold" for="polegadas"><?php echo __('Polegadas (Monitor)'); ?></label>
                                                <select class="form-control" name="polegadas" id="polegadas">
                                                    <option value=""><?php echo __('Selecione...'); ?></option>
                                                    <option value="15\"">15"</option>
                                                    <option value="17\"">17"</option>
                                                    <option value="18.5\"">18.5"</option>
                                                    <option value="19\"">19"</option>
                                                    <option value="20\"">20"</option>
                                                    <option value="21\"">21"</option>
                                                    <option value="21.5\"">21.5"</option>
                                                    <option value="22\"">22"</option>
                                                    <option value="23\"">23"</option>
                                                    <option value="23.8\"">23.8"</option>
                                                    <option value="24\"">24"</option>
                                                    <option value="27\"">27"</option>
                                                    <option value="29\"">29" (UltraWide)</option>
                                                    <option value="32\"">32"</option>
                                                    <option value="34\"">34" (UltraWide)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- Scanner (Impressora) -->
                                        <div class="col-md-12 mt-3" id="scanner_container" style="display: none;">
                                            <div class="form-group mb-0">
                                                <label class="text-gray-600 small font-weight-bold" for="is_scanner"><?php echo __('Possui Scanner? (Multifuncional)'); ?></label>
                                                <select class="form-control" name="is_scanner" id="is_scanner">
                                                    <option value="Não"><?php echo __('Não (Impressora Simples)'); ?></option>
                                                    <option value="Sim"><?php echo __('Sim (Multifuncional)'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Smartphone Section -->
                                <div id="smartphoneSection" style="display: none; border-left: 4px solid #007bff; padding-left: 15px; margin-bottom: 25px; background: #f0f7ff; border-radius: 5px;">
                                    <h5 class="text-primary font-weight-bold mb-3"><?php echo __('Dados do Smartphone'); ?></h5>
                                    <div class="row py-2">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="text-gray-600 small font-weight-bold" for="imei"><?php echo __('IMEI'); ?></label>
                                                <input class="form-control" name="imei" id="imei" type="text" placeholder="<?php echo __('Ex: 351234567890123'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="text-gray-600 small font-weight-bold" for="sim_card"><?php echo __('SIM Card (Número/ICCID)'); ?></label>
                                                <input class="form-control" name="sim_card" id="sim_card" type="text" placeholder="<?php echo __('Ex: 89551234567890123456'); ?>">
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
                                        <!-- Campo oculto para compatibilidade com lógica antiga -->
                                        <input type="hidden" name="status_old" value="Ativo">
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
    <script src="/assets/js/theme.js"></script>
    <script>
        let currentAssetData = null;
        $(document).ready(function() {
            // Atribuição Toggle
            $('#assigned_type').change(function() {
                if ($(this).val() === 'Usuario') {
                    $('#user_assignment').show();
                    $('#location_assignment').hide();
                    $('#id_local').val('');
                } else {
                    $('#user_assignment').hide();
                    $('#location_assignment').show();
                    $('#assigned_to').val('');
                }
            });

            function toggleFields() {
                var cat = $('#categoria').val();
                
                // Hardware standard (Notebook, Desktop, Servidor, Workstation)
                if (cat === 'Notebook' || cat === 'Desktop' || cat === 'Servidores' || cat === 'Workstation') {
                    $('#hardwareSection').slideDown();
                    $('#smartphoneSection').slideUp();
                    $('#setor_container').hide();
                    $('#tier_container').show();

                    if (cat === 'Workstation') {
                        $('#gpu_container').show();
                    } else {
                        $('#gpu_container').hide();
                        $('#gpu').val('');
                    }

                    $('#inches_container').hide();
                    $('#scanner_container').hide();
                    $('#memoria, #processador, #armazenamento, #tipo_armazenamento').closest('.col-md-3').show();
                } 
                else if (cat === 'Monitores') {
                    $('#hardwareSection').slideDown();
                    $('#smartphoneSection').slideUp();
                    $('#memoria, #processador, #armazenamento, #tipo_armazenamento').closest('.col-md-3').hide();
                    $('#inches_container').show();
                    $('#gpu_container').hide();
                    $('#scanner_container').hide();
                    $('#tier_container').show();
                    $('#setor_container').show();
                }
                else if (cat === 'Impressoras') {
                    $('#hardwareSection').slideDown();
                    $('#smartphoneSection').slideUp();
                    $('#memoria, #processador, #armazenamento, #tipo_armazenamento').closest('.col-md-3').hide();
                    $('#scanner_container').show();
                    $('#inches_container').hide();
                    $('#gpu_container').hide();
                    $('#tier_container').show();
                    $('#setor_container').show();
                }
                else if (cat === 'Smartphone') {
                    $('#smartphoneSection').slideDown();
                    $('#hardwareSection').slideUp();
                    $('#hostnameContainer').hide();
                    $('#hostName').prop('required', false);
                    $('#tier_container').show();
                    $('#setor_container').show();
                }
                else {
                    $('#smartphoneSection').slideUp();
                    $('#hardwareSection').slideUp();
                    $('#tier_container').show();
                    $('#setor_container').show();
                    $('#inches_container').hide();
                    $('#scanner_container').hide();
                    $('#gpu_container').hide();
                }

                // Host Name visibility logic refined
                if (cat === 'Monitores' || cat === 'Periféricos' || cat === 'Impressoras' || cat === 'Smartphone') {
                    $('#hostnameContainer').slideUp();
                    $('#hostName').removeAttr('required');
                } else {
                    $('#hostnameContainer').slideDown();
                    $('#hostName').attr('required', 'required');
                }
            }

            $('#categoria').change(toggleFields);
            toggleFields();

            // AJAX Submission with SweetAlert2
            $('#assetForm').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('ajax', '1');
                
                Swal.fire({ 
                    title: '<?php echo __("Salvando..."); ?>', 
                    didOpen: () => { Swal.showLoading(); },
                    allowOutsideClick: false
                });

                $.ajax({
                    url: 'inserir_equipamento.php',
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
                                new QRCode(document.getElementById("qrcode_reg"), {
                                    text: `ID: ${res.id_asset}\nTag: ${res.tag}\nModelo: ${res.modelo}`,
                                    width: 180,
                                    height: 180
                                });
                                $('#successTagModal').modal('show');
                            } else {
                                Swal.fire('<?php echo __("Erro de Negócio"); ?>', res.message, 'error');
                            }
                        } catch (err) {
                            Swal.fire('<?php echo __("Erro de Resposta"); ?>', '<?php echo __("O servidor retornou um formato inválido."); ?>', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('<?php echo __("Erro de Rede"); ?>', `<?php echo __("Falha no servidor"); ?> (Status ${xhr.status})`, 'error');
                    }
                });
            });

            $('#btn-print-reg').on('click', function() {
                printCreatedAsset();
            });
        });
    </script>
</body>
</html>