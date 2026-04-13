<!-- 
  MODAL DE IMPORTAÇÃO CSV: modal_importar.php
  Componente compartilhado para upload e processamento de dados em massa.
-->
<div class="modal fade" id="modalImportarCSV" tabindex="-1" role="dialog" aria-labelledby="modalImportarCSVLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
            <!-- Header Premium -->
            <div class="modal-header p-4" style="background: #2c404a; color: white; border: none;">
                <div class="d-flex align-items-center">
                    <div class="bg-white rounded-circle p-2 mr-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-file-import text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0" id="modalImportarCSVLabel"><?php echo __('Importação de Dados'); ?></h5>
                        <small class="text-white-50"><?php echo __('Carga em massa via arquivo CSV'); ?></small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4 bg-light">
                <form id="formImportarCSV" enctype="multipart/form-data">
                    <input type="hidden" name="type" id="import_type" value="ativos">
                    
                    <!-- Alerta de Orientação -->
                    <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 12px; background: #e3f2fd;">
                        <div class="d-flex">
                            <i class="fas fa-info-circle mr-2 mt-1"></i>
                            <div>
                                <h6 class="font-weight-bold mb-1" style="font-size: 0.9rem;"><?php echo __('Importante:'); ?></h6>
                                <p class="small mb-2"><?php echo __('Utilize o modelo padrão para garantir que as colunas sejam processadas corretamente.'); ?></p>
                                <a href="gerar_template_csv.php?type=ativos" id="linkTemplate" class="btn btn-sm btn-info font-weight-bold" style="border-radius: 8px;">
                                    <i class="fas fa-download mr-1"></i><?php echo __('Baixar Modelo CSV'); ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Central -->
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-gray-700 small text-uppercase mb-2"><?php echo __('Selecione o arquivo (.csv):'); ?></label>
                        <div class="custom-file mb-3">
                            <input type="file" class="custom-file-input" name="csv_file" id="csv_file" accept=".csv" required onchange="updateCSVFileName(this)">
                            <label class="custom-file-label" for="csv_file" data-browse="<?php echo __('Procurar'); ?>"><?php echo __('Escolher arquivo...'); ?></label>
                        </div>
                    </div>

                    <!-- Progress/Results (Hidden by default) -->
                    <div id="importResults" style="display: none;">
                        <hr>
                        <div id="importSpinner" class="text-center py-3">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted small"><?php echo __('Processando dados, aguarde...'); ?></p>
                        </div>
                        <div id="importStats" style="display: none;">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h4 class="font-weight-bold text-success mb-0" id="successCount">0</h4>
                                    <small class="text-muted text-uppercase" style="font-size: 0.6rem;"><?php echo __('Sucesso'); ?></small>
                                </div>
                                <div class="col-4">
                                    <h4 class="font-weight-bold text-warning mb-0" id="skipCount">0</h4>
                                    <small class="text-muted text-uppercase" style="font-size: 0.6rem;"><?php echo __('Duplicados'); ?></small>
                                </div>
                                <div class="col-4">
                                    <h4 class="font-weight-bold text-danger mb-0" id="errorCount">0</h4>
                                    <small class="text-muted text-uppercase" style="font-size: 0.6rem;"><?php echo __('Erros'); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0 p-4 bg-white">
                <button type="button" class="btn btn-secondary font-weight-bold px-4" data-dismiss="modal" style="border-radius: 12px;"><?php echo __('Cancelar'); ?></button>
                <button type="button" id="btnConfirmarImport" onclick="window.iniciarImportacao()" class="btn btn-primary font-weight-bold px-4 shadow" style="border-radius: 12px; background: #2c404a; border: none;">
                    <?php echo __('Iniciar Importação'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Início do Script com Garantia de Escopo Global -->
<script>
/**
 * Função global para abrir o modal de importação.
 * Definida fora de qualquer bloco de carregamento para estar disponível imediatamente.
 */
window.abrirModalImportar = function(type) {
    console.log("abrirModalImportar called with type:", type);
    
    if (typeof jQuery === "undefined") {
        console.error("ERRO: jQuery não está definido! Verifique a ordem dos scripts.");
        alert("Erro técnico: jQuery não carregado.");
        return;
    }
    
    // Captura o modal via jQuery
    var $modal = jQuery('#modalImportarCSV');
    
    if (!$modal.length) {
        console.error("Modal de importação não encontrado no DOM.");
        return;
    }

    // Configuração inicial do Modal
    jQuery('#import_type').val(type);
    jQuery('#linkTemplate').attr('href', 'gerar_template_csv.php?type=' + type);
    jQuery('#importResults').hide();
    jQuery('#btnConfirmarImport').show();
    jQuery('#csv_file').val('');
    jQuery('.custom-file-label').html('<?php echo __('Escolher arquivo...'); ?>');
    
    // Abre o Modal usando a API do Bootstrap
    console.log("Tentando abrir o modal #modalImportarCSV via Bootstrap API...");
    try {
        $modal.modal('show');
    } catch (e) {
        console.error("Erro ao abrir modal via Bootstrap:", e);
        // Fallback para toggle manual se a API do Bootstrap falhar
        $modal.addClass('show').css('display', 'block');
    }
};

/**
 * Função global ativada via onchange inline para garantir que o nome seja exibido
 * independentemente de conflitos com jQuery, bs-custom-file-input ou outros plugins.
 */
window.updateCSVFileName = function(input) {
    var fileName = input.files && input.files.length > 0 ? input.files[0].name : '';
    var label = document.querySelector('label[for="csv_file"]');
    if (label) {
        if (fileName) {
            label.classList.add('selected');
            label.innerHTML = fileName;
        } else {
            label.classList.remove('selected');
            label.innerHTML = '<?php echo __('Escolher arquivo...'); ?>';
        }
    }
};

window.iniciarImportacao = function() {
    const fileInput = document.getElementById('csv_file');
    if (!fileInput || !fileInput.files || !fileInput.files.length) {
        if (typeof Swal !== "undefined") {
            Swal.fire({
                icon: 'warning',
                title: '<?php echo __('Atenção'); ?>',
                text: '<?php echo __('Selecione um arquivo CSV primeiro.'); ?>'
            });
        } else {
            alert('<?php echo __('Selecione um arquivo CSV primeiro.'); ?>');
        }
        return;
    }

    const formElement = document.getElementById('formImportarCSV');
    const formData = new FormData(formElement);
    
    // Reset e mostrar progresso
    document.getElementById('btnConfirmarImport').style.display = 'none';
    const importResults = document.getElementById('importResults');
    const importSpinner = document.getElementById('importSpinner');
    const importStats = document.getElementById('importStats');
    
    if (importResults) importResults.style.display = 'block';
    if (importSpinner) importSpinner.style.display = 'block';
    if (importStats) importStats.style.display = 'none';

    fetch('ajax_importar_csv.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (importSpinner) importSpinner.style.display = 'none';
        if (importStats) importStats.style.display = 'block';
        
        if (data.success) {
            document.getElementById('successCount').innerText = data.results.imported || 0;
            document.getElementById('skipCount').innerText = data.results.skipped || 0;
            document.getElementById('errorCount').innerText = data.results.errors || 0;
            
            // Se houver sucessos, recarregar a página após um breve delay
            if (data.results.imported > 0) {
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            }
        } else {
            if (typeof Swal !== "undefined") {
                Swal.fire({ icon: 'error', title: '<?php echo __('Erro'); ?>', text: data.message });
            } else {
                alert('<?php echo __('Erro: '); ?>' + data.message);
            }
            document.getElementById('btnConfirmarImport').style.display = 'inline-block';
            if (importResults) importResults.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof Swal !== "undefined") {
            Swal.fire({ icon: 'error', title: '<?php echo __('Erro de Requisição'); ?>', text: '<?php echo __('Ocorreu um erro no processamento do arquivo.'); ?>' });
        } else {
            alert('<?php echo __('Ocorreu um erro no processamento do arquivo.'); ?>');
        }
        document.getElementById('btnConfirmarImport').style.display = 'inline-block';
        if (importResults) importResults.style.display = 'none';
    });
};

jQuery(document).ready(function($) {
    // Listener robusto delegado para o botão de importação
    $(document).on('click', '.btn-premium-import', function(e) {
        e.preventDefault();
        const type = $(this).data('import-type');
        console.log("Btn-premium-import clicked. Type: ", type);
        if (type) {
            window.abrirModalImportar(type);
        } else {
            console.error("No data-import-type found on clicked element.");
        }
    });
});
</script>
