<!-- 
    MODAIS COMPARTILHADOS: Atribuição e Manutenção
    Este arquivo é incluído em equipamentos.php e perfil_ativo.php 
-->

<!-- Modal para Atribuir Ativo -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center">
                    <div class="bg-success-soft rounded-circle p-3 mr-3">
                        <i class="fas fa-link text-success fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold text-dark"><?php echo __('Atribuir Ativo'); ?></h5>
                        <p class="text-muted small mb-0"><?php echo __('Vincular este equipamento a um colaborador ou local.'); ?></p>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="assign_asset_id">
                
                <div class="form-group mb-4">
                    <label class="text-gray-600 small font-weight-bold uppercase tracking-wider"><?php echo __('Tipo de Atribuição'); ?></label>
                    <select id="assignTypeSelect" class="form-control custom-select-lg border-0 bg-light font-weight-bold" style="border-radius: 12px; height: 50px;">
                        <option value="user" selected>👤 <?php echo __('Colaborador'); ?></option>
                        <option value="location">🏢 <?php echo __('Local / Infraestrutura'); ?></option>
                    </select>
                </div>

                <div id="assignSearchSection">
                    <label id="assignSearchLabel" class="text-gray-600 small font-weight-bold uppercase tracking-wider"><?php echo __('Buscar Colaborador'); ?></label>
                    <div class="input-group mb-3 bg-light rounded-pill px-3 py-1">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-transparent border-0 text-muted">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <input type="text" id="assignSearchInput" class="form-control bg-transparent border-0" 
                               placeholder="<?php echo __('Nome, e-mail ou CPF...'); ?>" style="box-shadow: none;">
                    </div>

                    <!-- Estado Inicial / Resultados -->
                    <div id="startTyping" class="text-center py-4">
                        <i class="fas fa-search text-gray-200 fa-3x mb-3"></i>
                        <p class="text-muted small"><?php echo __('Comece a digitar para buscar...'); ?></p>
                    </div>

                    <div id="assignSearchResults" class="list-group list-group-flush rounded-xl overflow-hidden shadow-sm border" style="display: none; max-height: 250px; overflow-y: auto;">
                        <!-- Resultados via AJAX -->
                    </div>

                    <div id="noResults" class="text-center py-4" style="display: none;">
                        <i class="fas fa-search text-gray-300 fa-3x mb-3"></i>
                        <p class="text-muted"><?php echo __('Nenhum resultado encontrado.'); ?></p>
                    </div>
                </div>

                <!-- Item Selecionado -->
                <div id="selectedAssignInfo" class="mt-3 p-3 rounded-lg border-left-success bg-light" style="display: none;">
                    <input type="hidden" id="selectedAssignId">
                    <div class="d-flex align-items-center">
                        <div class="bg-success rounded-circle p-2 mr-3">
                            <i class="fas fa-check text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-success font-weight-bold uppercase"><?php echo __('Selecionado'); ?></small>
                            <h6 id="selectedAssignName" class="mb-0 font-weight-bold text-dark"></h6>
                        </div>
                        <button class="btn btn-sm btn-link text-danger" onclick="clearAssignmentSelection()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-link text-muted font-weight-bold" data-dismiss="modal"><?php echo __('Cancelar'); ?></button>
                <button type="button" id="btnConfirmAssign" class="btn btn-success px-4 py-2 font-weight-bold shadow-sm" disabled style="border-radius: 12px;">
                    <?php echo __('Confirmar Atribuição'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Manutenção -->
<div class="modal fade" id="maintenanceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center">
                    <div class="bg-warning-soft rounded-circle p-3 mr-3">
                        <i class="fas fa-tools text-warning fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold text-dark"><?php echo __('Solicitar Manutenção'); ?></h5>
                        <p class="text-muted small mb-0"><?php echo __('Selecione o fluxo técnico para este ativo.'); ?></p>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="maintenance_asset_id">
                
                <!-- Seleção de Tipo -->
                <div class="row no-gutters mb-4 p-1 bg-light rounded-xl">
                    <?php 
                    $types = [
                        ['id' => 'Insumo', 'icon' => 'fa-fill-drip', 'color' => 'text-success', 'label' => __('Suprimento')],
                        ['id' => 'Reparo', 'icon' => 'fa-wrench', 'color' => 'text-warning', 'label' => __('Reparo')],
                        ['id' => 'Upgrade', 'icon' => 'fa-arrow-up', 'color' => 'text-primary', 'label' => __('Upgrade')]
                    ];
                    foreach ($types as $t): ?>
                    <div class="col-4 p-1">
                        <input type="radio" name="maintenanceType" id="type<?php echo $t['id']; ?>" value="<?php echo $t['id']; ?>" class="d-none" <?php echo $t['id'] == 'Insumo' ? 'checked' : ''; ?>>
                        <label for="type<?php echo $t['id']; ?>" class="m-0 p-3 text-center d-block rounded-lg cursor-pointer maintenance-type-card h-100 transition-all">
                            <i class="fas <?php echo $t['icon']; ?> d-block fa-lg mb-2 <?php echo $t['color']; ?>"></i>
                            <span class="small font-weight-bold"><?php echo $t['label']; ?></span>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Campos Dinâmicos: Reparo -->
                <div id="repairFields" style="display: none;">
                    <div class="form-group mb-4">
                        <label class="text-gray-600 small font-weight-bold uppercase tracking-wider"><?php echo __('Motivo / Detalhes'); ?></label>
                        <textarea id="maintenanceReason" class="form-control border-0 bg-light p-3" rows="3" style="border-radius: 12px;" placeholder="<?php echo __('Descreva o problema ou necessidade...'); ?>"></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-gray-600 small font-weight-bold uppercase tracking-wider"><?php echo __('Peças Trocadas (opcional)'); ?></label>
                        <input type="text" id="itemTrocado" class="form-control border-0 bg-light" style="border-radius: 12px; height: 50px;" placeholder="<?php echo __('Ex: Teclado, Bateria...'); ?>">
                    </div>
                </div>

                <!-- Campos Dinâmicos: Upgrade -->
                <div id="upgradeFields" style="display: none;">
                    <div class="form-group mb-3">
                        <label class="text-gray-600 small font-weight-bold uppercase tracking-wider"><?php echo __('Categoria'); ?></label>
                        <select id="upgradeCategory" class="form-control border-0 bg-light custom-select" style="border-radius: 12px; height: 50px;">
                            <option value=""><?php echo __('Selecione...'); ?></option>
                            <option value="Memória"><?php echo __('Memória RAM'); ?></option>
                            <option value="Armazenamento"><?php echo __('Armazenamento'); ?></option>
                            <option value="Outro"><?php echo __('Outros'); ?></option>
                        </select>
                    </div>
                    <div id="ramFields" style="display: none;">
                        <div class="form-group mb-3">
                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Módulo Selecionado'); ?></label>
                            <input type="text" id="ramModule" class="form-control border-0 bg-light" style="border-radius: 12px; height: 50px;" placeholder="Ex: 8GB DDR4 3200MHz">
                        </div>
                    </div>
                    <div id="storageFields" style="display: none;">
                        <div class="row">
                            <div class="col-8 pr-1">
                                <label class="text-gray-600 small font-weight-bold"><?php echo __('Tecnologia'); ?></label>
                                <select id="diskType" class="form-control border-0 bg-light custom-select" style="border-radius: 12px; height: 50px;">
                                    <option value="SSD NVMe" selected>SSD NVMe</option>
                                    <option value="SSD SATA">SSD SATA</option>
                                    <option value="HDD">HDD</option>
                                </select>
                            </div>
                            <div class="col-4 pl-1">
                                <label class="text-gray-600 small font-weight-bold"><?php echo __('Tam.'); ?></label>
                                <input type="text" id="upgradeValue" class="form-control border-0 bg-light" style="border-radius: 12px; height: 50px;" placeholder="Ex: 512GB">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3 mb-0">
                        <label class="text-gray-600 small font-weight-bold"><?php echo __('Obs / Descrição'); ?></label>
                        <textarea id="upgradeDescription" class="form-control border-0 bg-light p-3" rows="2" style="border-radius: 12px;" placeholder="<?php echo __('Detalhes do upgrade...'); ?>"></textarea>
                    </div>
                    <div class="form-group mt-3 mb-0">
                        <label class="text-gray-600 small font-weight-bold"><?php echo __('Custo estimado (opcional)'); ?></label>
                        <input type="text" id="upgradeCost" class="form-control border-0 bg-light" style="border-radius: 12px; height: 50px;" placeholder="R$ 0,00">
                    </div>
                </div>

                <!-- Campos Dinâmicos: Insumo -->
                <div id="supplyFields">
                    <div class="form-group mb-3">
                        <label class="text-gray-600 small font-weight-bold uppercase tracking-wider"><?php echo __('Tipo de Insumo'); ?></label>
                        <select id="supplyType" class="form-control border-0 bg-light custom-select" style="border-radius: 12px; height: 50px;">
                            <option value=""><?php echo __('Selecione...'); ?></option>
                            <?php foreach (['Toner', 'Difusor', 'Cartucho de Tinta', 'Cilindro / Drum', 'Kit Fusor', 'Outro'] as $ins): ?>
                            <option value="<?php echo $ins; ?>"><?php echo __($ins); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-gray-600 small font-weight-bold uppercase tracking-wider"><?php echo __('Observações'); ?></label>
                        <textarea id="supplyDescription" class="form-control border-0 bg-light p-3" rows="2" style="border-radius: 12px;" placeholder="<?php echo __('Ex: Troca do Toner Preto...'); ?>"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-link text-muted font-weight-bold" data-dismiss="modal"><?php echo __('Cancelar'); ?></button>
                <button type="button" id="confirmMaintenance" class="btn btn-warning px-4 py-2 font-weight-bold shadow-sm" style="border-radius: 12px;">
                    <?php echo __('Confirmar'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-success-soft { background-color: rgba(40, 167, 69, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .bg-primary-soft { background-color: rgba(0, 123, 255, 0.1); }
    .maintenance-type-card { border: 2px solid transparent; background: white; transition: all 0.2s; }
    input[name="maintenanceType"]:checked + .maintenance-type-card { border-color: #ffc107; background: #fffdf5; transform: scale(1.02); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .rounded-xl { border-radius: 16px !important; }
    .rounded-lg { border-radius: 12px !important; }
    .list-group-item:hover { background-color: #f8f9fa; }
    .cursor-pointer { cursor: pointer; }
</style>

<script>
$(document).ready(function() {
    // --- Lógica de Atribuição ---
    $('#assignTypeSelect').on('change', function() {
        updateAssignUI();
        clearAssignmentSelection();
    });

    $('#assignSearchInput').on('keyup', function() {
        const query = $(this).val();
        const type = $('#assignTypeSelect').val();
        if (query.length < 2) {
            $('#assignSearchResults').hide().empty();
            $('#startTyping').show();
            $('#noResults').hide();
            return;
        }

        const endpoint = (type === 'user') ? 'ajax_buscar_usuario.php' : 'ajax_buscar_local.php';
        
        $('#assignSearchResults').show().html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i><p class="text-muted small mb-0"><?php echo __('Buscando...'); ?></p></div>');
        $('#startTyping, #noResults').hide();

        $.getJSON(endpoint, { query: query }, function(data) {
            $('#startTyping').hide();
            $('#assignSearchResults').empty();
            if (data.length > 0) {
                data.forEach(item => {
                    let id, name, sub;
                    if (type === 'user') {
                        id = item.id;
                        name = item.nome_completo;
                        sub = `${item.funcao || 'Funcional'} | ${item.email}`;
                    } else {
                        id = item.id_local;
                        name = item.nome_local;
                        sub = item.tipo_local;
                    }

                    const row = $(`
                        <button class="list-group-item list-group-item-action border-0 d-flex align-items-center p-3" 
                                style="transition: all 0.2s;" onclick="selectAssignItem(${id}, '${name}', '${type}')">
                            <div class="bg-light rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas ${type === 'user' ? 'fa-user' : 'fa-building'} text-primary"></i>
                            </div>
                            <div class="flex-grow-1 text-left">
                                <h6 class="mb-0 font-weight-bold text-dark">${name}</h6>
                                <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>${sub}</small>
                            </div>
                            <i class="fas fa-chevron-right text-gray-300"></i>
                        </button>
                    `);
                    $('#assignSearchResults').append(row);
                });
                $('#assignSearchResults').show();
                $('#noResults').hide();
            } else {
                $('#assignSearchResults').hide();
                $('#noResults').show();
            }
        });
    });

    $(document).on('click', '#btnConfirmAssign', function() {
        const assetId = $('#assign_asset_id').val();
        const idTarget = $('#selectedAssignId').val();
        const type = $('#assignTypeSelect').val();
        if (!idTarget) return;

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i><?php echo __('Processando...'); ?>');
        const action = (type === 'user') ? 'assign' : 'assign_local';
        const payload = { action: action, id_asset: assetId };
        if (type === 'user') payload.id_usuario = idTarget;
        else payload.id_local = idTarget;

        $.post('ajax_ativos.php', payload, function(res) {
            if (res.success) {
                Swal.fire({
                    title: '<?php echo __('Sucesso!'); ?>',
                    text: '<?php echo __('Ativo atribuído corretamente.'); ?>',
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('<?php echo __('Erro'); ?>', res.message || '<?php echo __('Erro ao atribuir'); ?>', 'error');
                $('#btnConfirmAssign').prop('disabled', false).text('<?php echo __('Confirmar Atribuição'); ?>');
            }
        }, 'json');
    });

    // --- Lógica de Manutenção ---
    $(document).on('change', 'input[name="maintenanceType"]', function() {
        toggleMaintenanceUI($(this).val());
    });

    $(document).on('change', '#upgradeCategory', function() {
        const cat = $(this).val();
        if (cat === 'Memória') {
            $('#ramFields').slideDown(200);
            $('#storageFields').slideUp(200);
        } else if (cat === 'Armazenamento') {
            $('#ramFields').slideUp(200);
            $('#storageFields').slideDown(200);
        } else {
            $('#ramFields, #storageFields').slideUp(200);
        }
    });

    $(document).on('click', '#confirmMaintenance', function() {
        const type = $('input[name="maintenanceType"]:checked').val();
        const assetId = $('#maintenance_asset_id').val();
        let data = { action: 'send_to_maintenance', id_asset: assetId, tipo_manutencao: type };

        if (type === 'Reparo') {
            if (!$('#maintenanceReason').val()) { Swal.fire('<?php echo __('Atenção'); ?>', '<?php echo __('Relate o motivo do reparo.'); ?>', 'warning'); return; }
            data.observacoes = $('#maintenanceReason').val();
            data.item_trocado = $('#itemTrocado').val();
        } else if (type === 'Upgrade') {
            const cat = $('#upgradeCategory').val();
            if (!cat) { Swal.fire('<?php echo __('Atenção'); ?>', '<?php echo __('Selecione a categoria do upgrade.'); ?>', 'warning'); return; }
            data.categoria_upgrade = cat;
            data.observacoes = $('#upgradeDescription').val();
            data.valor_upgrade = $('#upgradeCost').val();
            if (cat === 'Memória') data.item_trocado = $('#ramModule').val();
            if (cat === 'Armazenamento') {
                data.item_trocado = $('#diskType').val();
                data.detalhes_update = $('#upgradeValue').val();
            }
        } else if (type === 'Insumo') {
            if (!$('#supplyType').val()) { Swal.fire('<?php echo __('Atenção'); ?>', '<?php echo __('Selecione o tipo de insumo.'); ?>', 'warning'); return; }
            data.item_trocado = $('#supplyType').val();
            data.observacoes = $('#supplyDescription').val();
        }

        $.post('ajax_ativos.php', data, function(res) {
            if (res.success) location.reload();
            else Swal.fire('<?php echo __('Erro'); ?>', res.message || '<?php echo __('Erro ao processar manutenção'); ?>', 'error');
        }, 'json');
    });
});

// Funções Globais (podem ser chamadas de fora do ready)
window.openAssignModal = function(id) {
    $('#assign_asset_id').val(id);
    $('#assignTypeSelect').val('user');
    updateAssignUI();
    clearAssignmentSelection();
    $('#assignModal').modal('show');
};

window.updateAssignUI = function() {
    const type = $('#assignTypeSelect').val();
    const label = type === 'user' ? '<?php echo __('Buscar Colaborador'); ?>' : '<?php echo __('Buscar Local / Infraestrutura'); ?>';
    const placeholder = type === 'user' ? '<?php echo __('Nome, e-mail ou CPF...'); ?>' : '<?php echo __('Nome do local ou tipo...'); ?>';
    $('#assignSearchLabel').text(label);
    $('#assignSearchInput').attr('placeholder', placeholder).val('').focus();
    $('#assignSearchResults').hide().empty();
    $('#startTyping').show();
    $('#noResults').hide();
};

window.clearAssignmentSelection = function() {
    $('#selectedAssignId').val('');
    $('#selectedAssignName').text('');
    $('#selectedAssignInfo').hide();
    $('#btnConfirmAssign').prop('disabled', true);
    $('#assignSearchSection').fadeIn(200);
    $('#assignSearchInput').val('').focus();
};

window.selectAssignItem = function(id, name, type) {
    $('#selectedAssignId').val(id);
    $('#selectedAssignName').text(name);
    $('#selectedAssignInfo').fadeIn(200);
    $('#btnConfirmAssign').prop('disabled', false);
    $('#assignSearchSection').hide();
};

window.unassignUser = function(id) {
    Swal.fire({
        title: '<?php echo __('Confirmar Liberação?'); ?>',
        text: "<?php echo __('O ativo voltará a ficar disponível no estoque.'); ?>",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<?php echo __('Sim, liberar'); ?>',
        cancelButtonText: '<?php echo __('Cancelar'); ?>'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('ajax_ativos.php', { action: 'unassign', id_asset: id }, function (res) {
                if (res.success) {
                    Swal.fire({
                        title: '<?php echo __('Liberado!'); ?>',
                        text: '<?php echo __('O ativo está disponível para nova atribuição.'); ?>',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('<?php echo __('Erro'); ?>', res.message || '<?php echo __('Erro ao liberar'); ?>', 'error');
                }
            }, 'json');
        }
    });
};

window.sendToMaintenance = function(id) {
    $('#maintenance_asset_id').val(id);
    $('#maintenanceReason, #itemTrocado, #upgradeDescription, #supplyDescription, #upgradeValue, #upgradeCost').val('');
    $('#upgradeCategory, #ramModule, #diskType, #supplyType').val('');
    $('#ramFields, #storageFields').hide();
    $('#typeInsumo').prop('checked', true);
    toggleMaintenanceUI('Insumo');
    $('#maintenanceModal').modal('show');
};

window.toggleMaintenanceUI = function(type) {
    $('#repairFields, #upgradeFields, #supplyFields').slideUp(200);
    if (type === 'Reparo') $('#repairFields').slideDown(200);
    else if (type === 'Upgrade') $('#upgradeFields').slideDown(200);
    else if (type === 'Insumo') $('#supplyFields').slideDown(200);
};

window.releaseFromMaintenance = function(id) {
    Swal.fire({
        title: '<?php echo __('Finalizar Manutenção?'); ?>',
        text: "<?php echo __('O ativo voltará a ficar disponível no estoque.'); ?>",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: '<?php echo __('Sim, finalizar'); ?>',
        cancelButtonText: '<?php echo __('Cancelar'); ?>'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('ajax_ativos.php', { action: 'release_maintenance', id_asset: id }, function (res) {
                if (res.success) location.reload();
                else Swal.fire('<?php echo __('Erro'); ?>', res.message || '<?php echo __('Erro ao liberar manutenção'); ?>', 'error');
            }, 'json');
        }
    });
};
</script>
