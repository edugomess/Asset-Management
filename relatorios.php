<?php
include_once 'auth.php';
include_once 'conexao.php';

// Bloqueio de acesso para usuários comuns
if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Relatórios'); ?> - Asset Mgt</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=f3d9abe8d5aa7831c01bfaa2a1563712">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=41e93b37bc495fd67938799bb3a6adaf">
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
                <?php include 'topbar.php'; ?>
                <div class="container-fluid" style="padding-left: 23px; padding-right: 23px;">
                    <h3 class="text-dark mb-4"><?php echo __('Relatórios'); ?></h3>
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <p class="text-primary m-0 font-weight-bold"></p>
                        </div>
                        <div class="card-body">
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <form id="reportForm" class="d-flex align-items-center">
                                        <select id="reportType" class="form-control mr-3" style="height: 50px;">
                                            <option value=""><?php echo __('Selecione um relatório...'); ?></option>

                                            <?php if ($_SESSION['nivelUsuario'] !== 'Usuário'): ?>
                                                <optgroup label="<?php echo __('Ativos'); ?>">
                                                    <option value="relatorio_ativo.php" data-periodo="true"><?php echo __('Geral de Ativos'); ?></option>
                                                    <option value="relatorio_ativos_status.php" data-periodo="true"><?php echo __('Por Status'); ?></option>
                                                    <option value="relatorio_ativos_cc.php" data-periodo="true"><?php echo __('Por Centro de Custo'); ?></option>
                                                    <option value="relatorio_financeiro.php" data-periodo="true"><?php echo __('Resumo Financeiro'); ?></option>
                                                    <option value="relatorio_ativos_fabricante.php" data-periodo="true"><?php echo __('Por Fabricante'); ?></option>
                                                    <option value="relatorio_ativos_modelo.php"><?php echo __('Por Modelo'); ?></option>
                                                    <option value="relatorio_ativos_categoria.php"><?php echo __('Por Categoria'); ?></option>
                                                    <option value="relatorio_ativos_antigos.php"><?php echo __('Ativos Antigos (> 3 anos)'); ?>
                                                    </option>
                                                    <option value="relatorio_ativos_recentes.php"><?php echo __('Ativos Recentes (< 1 ano)'); ?></option>
                                                    <option value="relatorio_ativos_valor_alto.php"><?php echo __('Alto Valor (> R$ 5k)'); ?>
                                                    </option>
                                                    <option value="relatorio_ativos_valor_baixo.php"><?php echo __('Baixo Valor (< R$ 1k)'); ?></option>
                                                    <option value="relatorio_ativos_disponiveis.php"><?php echo __('Disponíveis'); ?></option>
                                                    <option value="relatorio_ativos_em_uso.php"><?php echo __('Em Uso'); ?></option>
                                                    <option value="relatorio_ativos_manutencao.php"><?php echo __('Em Manutenção'); ?></option>
                                                    <option value="relatorio_ativos_sem_cc.php"><?php echo __('Sem Centro de Custo'); ?></option>
                                                    <option value="relatorio_ativos_por_usuario.php"><?php echo __('Por Usuário'); ?></option>
                                                    <option value="relatorio_ativos_unidade.php"><?php echo __('Por Unidade'); ?></option>
                                                    <option value="relatorio_ativos_historico.php"><?php echo __('Trilha de Auditoria (Histórico)'); ?></option>
                                                     <option value="relatorio_ativos_incidentes.php"><?php echo __('Incidentes por Ativo'); ?></option>
                                                     <option value="ativos_doados.php"><?php echo __('Doações'); ?></option>
                                                </optgroup>
                                            <?php else: ?>
                                                <optgroup label="<?php echo __('Meus Ativos'); ?>">
                                                    <option value="relatorio_ativos_por_usuario.php"><?php echo __('Meus Ativos Atribuídos'); ?>
                                                    </option>
                                                </optgroup>
                                            <?php endif; ?>

                                            <optgroup label="<?php echo __('Chamados'); ?>">
                                                <?php if ($_SESSION['nivelUsuario'] !== 'Usuário'): ?>
                                                    <option value="relatorio_chamados_mensal.php"><?php echo __('Resumo Mensal'); ?></option>
                                                <?php endif; ?>
                                                <option value="relatorio_chamados_abertos.php" data-periodo="true"><?php echo __('Meus Chamados Abertos'); ?>
                                                </option>
                                                <option value="relatorio_chamados_fechados.php" data-periodo="true"><?php echo __('Meus Chamados Fechados'); ?>
                                                </option>
                                                <option value="relatorio_chamados_recentes.php" data-periodo="true"><?php echo __('Meus Chamados Recentes (30 dias)'); ?></option>
                                                <?php if ($_SESSION['nivelUsuario'] !== 'Usuário'): ?>
                                                    <option value="relatorio_chamados_categoria.php" data-periodo="true"><?php echo __('Por Categoria'); ?></option>
                                                    <option value="relatorio_chamados_tecnico.php" data-periodo="true"><?php echo __('Por Técnico'); ?></option>
                                                    <option value="relatorio_chamados_solicitante.php" data-periodo="true"><?php echo __('Por Solicitante'); ?>
                                                    </option>
                                                    <option value="relatorio_chamados_sla_vencido.php"><?php echo __('SLA Vencido'); ?></option>
                                                    <option value="relatorio_chamados_prioridade.php"><?php echo __('Por Prioridade'); ?></option>
                                                     <option value="relatorio_chamados_sem_atribuicao.php"><?php echo __('Sem Atribuição'); ?>
                                                     </option>
                                                     <option value="relatorio_chamados_aprovacoes.php"><?php echo __('Aprovações de Gestores'); ?></option>
                                                <?php endif; ?>
                                            </optgroup>

                                            <?php if ($_SESSION['nivelUsuario'] !== 'Usuário'): ?>
                                                <optgroup label="<?php echo __('Usuários'); ?>">
                                                    <option value="relatorio_usuario.php"><?php echo __('Lista Geral'); ?></option>
                                                    <option value="relatorio_usuarios_cc.php"><?php echo __('Por Centro de Custo'); ?></option>
                                                    <option value="relatorio_usuarios_inativos.php"><?php echo __('Inativos'); ?></option>
                                                    <option value="relatorio_usuarios_sem_ativos.php"><?php echo __('Sem Ativos'); ?></option>
                                                    <option value="relatorio_usuarios_com_ativos.php"><?php echo __('Com Ativos'); ?></option>
                                                    <option value="relatorio_usuarios_funcao.php"><?php echo __('Por Função'); ?></option>
                                                    <option value="relatorio_usuarios_vips.php"><?php echo __('VIPs'); ?></option>
                                                </optgroup>

                                                <optgroup label="<?php echo __('Licenças'); ?>">
                                                    <option value="relatorio_licencas_geral.php" data-periodo="true"><?php echo __('Geral de Licenças'); ?></option>
                                                    <option value="relatorio_licencas_expiradas.php"><?php echo __('Expiradas / Próximas ao Vencimento'); ?></option>
                                                    <option value="relatorio_licencas_cc.php"><?php echo __('Por Centro de Custo'); ?></option>
                                                    <option value="relatorio_licencas_em_uso.php"><?php echo __('Uso de Seats (Ocupação)'); ?>
                                                    </option>
                                                    <option value="relatorio_atribuicoes_geral.php"><?php echo __('Relatório Geral de Atribuições'); ?></option>
                                                </optgroup>

                                                <optgroup label="<?php echo __('Manutenção'); ?>">
                                                    <option value="relatorio_manutencao_atual.php"><?php echo __('Manutenções Ativas'); ?>
                                                    </option>
                                                    <option value="relatorio_manutencao_historico.php" data-periodo="true"><?php echo __('Histórico de Manutenções'); ?></option>
                                                    <option value="relatorio_manutencao_estatistico.php"><?php echo __('Resumo Estatístico'); ?>
                                                    </option>
                                                </optgroup>

                                                <optgroup label="<?php echo __('Fornecedores'); ?>">
                                                    <option value="relatorio_fornecedores_lista.php"><?php echo __('Lista de Fornecedores'); ?></option>
                                                    <option value="relatorio_fornecedores_servico.php"><?php echo __('Por Tipo de Serviço'); ?></option>
                                                    <option value="relatorio_ativos_fornecedor.php"><?php echo __('Ativos por Fornecedor'); ?></option>
                                                    <option value="relatorio_licencas_fornecedor.php"><?php echo __('Licenças por Fornecedor'); ?></option>
                                                    <option value="relatorio_compras_fornecedor.php" data-periodo="true"><?php echo __('Investimento por Fornecedor'); ?></option>
                                                </optgroup>

                                                <optgroup label="<?php echo __('Outros'); ?>">
                                                    <option value="relatorio_centro_de_custo.php"><?php echo __('Centros de Custo (Lista)'); ?>
                                                    </option>
                                                    <option value="relatorio_cc_detalhado.php"><?php echo __('Centros de Custo (Detalhado)'); ?>
                                                    </option>
                                                    <option value="relatorio_resumo_geral.php"><?php echo __('Resumo Geral do Sistema'); ?>
                                                    </option>
                                                </optgroup>
                                            <?php endif; ?>
                                        </select>

                                        <!-- Filtro de Período -->
                                        <div id="dateFilters" class="align-items-center mr-3" style="display: none;">
                                            <input type="date" id="startDate" class="form-control mr-2" style="height: 50px; width: 170px;" title="<?php echo __('Data Inicial'); ?>">
                                            <span class="mr-2"><?php echo __('até'); ?></span>
                                            <input type="date" id="endDate" class="form-control" style="height: 50px; width: 170px;" title="<?php echo __('Data Final'); ?>">
                                        </div>
                                        <select id="reportFormat" class="form-control mr-3"
                                            style="height: 50px; width: 150px;">
                                            <option value="pdf">PDF</option>
                                            <option value="xlsx">XLSX</option>
                                        </select>
                                        <button class="btn btn-success active text-white pulse animated btn-user"
                                            type="button" onclick="generateReport()"
                                            style="background: rgb(44,64,74);border-radius: 10px;height: 50px; white-space: nowrap;">
                                            <?php echo __('Gerar Relatório'); ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
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
    <script>
        /**
         * Abre o arquivo de relatório selecionado em uma nova aba.
         * Verifica se uma opção válida foi escolhida antes de tentar abrir.
         */
        function generateReport() {
            var url = document.getElementById('reportType').value;
            var format = document.getElementById('reportFormat').value;

            if (url) {
                var startDate = document.getElementById('startDate').value;
                var endDate = document.getElementById('endDate').value;

                // Adiciona o parâmetro de formato se não for PDF (padrão)
                var finalUrl = url;
                var params = [];

                if (format === 'xlsx') params.push('format=xlsx');
                if (startDate) params.push('start=' + startDate);
                if (endDate) params.push('end=' + endDate);

                if (params.length > 0) {
                    finalUrl += (url.includes('?') ? '&' : '?') + params.join('&');
                }

                window.open(finalUrl, '_blank');
            }
 else {
                alert('<?php echo __('Por favor, selecione um tipo de relatório.'); ?>');
            }
        }

        $(document).ready(function () {
            let debounceTimer;
            $('#globalSearchInput').on('input', function () {
                clearTimeout(debounceTimer);
                let query = $(this).val();
                let resultBox = $('#globalSearchResults');

                if (query.length < 2) {
                    resultBox.hide();
                    return;
                }

                debounceTimer = setTimeout(function () {
                    $.ajax({
                        url: 'search_backend.php',
                        method: 'GET',
                        data: {
                            q: query
                        },
                        dataType: 'json',
                        success: function (data) {
                            resultBox.empty();
                            if (data.length > 0) {
                                data.forEach(function (item) {
                                    let icon = 'fa-search';
                                    if (item.type === 'user') icon = 'fa-user';
                                    else if (item.type === 'asset') icon = 'fa-box';
                                    else if (item.type === 'ticket') icon = 'fa-headset';
                                    else if (item.type === 'cost_center') icon = 'fa-building';
                                    else if (item.type === 'supplier') icon = 'fa-truck';

                                    resultBox.append(`
                                        <a class="dropdown-item d-flex align-items-center" href="${item.url}">
                                            <div class="mr-3">
                                                <div class="icon-circle bg-primary">
                                                    <i class="fas ${icon} text-white"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="small text-gray-500">${item.category}</div>
                                                <span class="font-weight-bold">${item.label}</span>
                                            </div>
                                        </a>
                                    `);
                                });
                                resultBox.show();
                            } else {
                                resultBox.append('<a class="dropdown-item text-center small text-gray-500" href="#"><?php echo __('Nenhum resultado encontrado'); ?></a>');
                                resultBox.show();
                            }
                        },
                        error: function () {
                            console.error("Erro na busca");
                        }
                    });
                }, 300);
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('.navbar-search').length) {
                    $('#globalSearchResults').hide();
                }
            });

            $('#reportType').on('change', function() {
                let selectedOption = $(this).find('option:selected');
                let supportsPeriodo = selectedOption.data('periodo');
                if (supportsPeriodo) {
                    $('#dateFilters').css('display', 'flex');
                } else {
                    $('#dateFilters').css('display', 'none');
                }
            });
            // Trigger inicialmente para o relatório padrão
            $('#reportType').trigger('change');
        });
    </script>
    <script src="/assets/js/global_search.js"></script>
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
</body>

</html>