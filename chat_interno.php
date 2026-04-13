<?php
/**
 * INTERFACE DO CHAT INTERNO: chat_interno.php
 * Fornece a interface principal para comunicação entre usuários do sistema.
 * Suporta agora visualização de perfis, envio de imagens e links.
 */
include_once 'auth.php';
include_once 'conexao.php';
include_once 'language.php';

$my_id = $_SESSION['id_usuarios'];
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Chat Interno'); ?> - Asset MGT</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/css/Nunito.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="/assets/css/chat_premium.css">
    <style>
        .msg-sender-name {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 2px;
            margin-left: 12px;
        }
        .me .msg-sender-name {
            display: none;
        }
        .form-control-premium {
            border-radius: 10px;
            border: 1px solid #e3e6f0;
            padding: 10px 15px;
            transition: all 0.2s;
        }
        .form-control-premium:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1);
        }
    </style>
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
                <div class="container-fluid" style="padding-left: 23px; padding-right: 23px;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="text-dark mb-0 animate__animated animate__fadeInDown">
                            <i class="fas fa-comments mr-2 text-primary"></i><?php echo __('Comunicação Interna'); ?>
                        </h3>
                    </div>

                    <div class="chat-main-wrapper animate__animated animate__fadeInUp">
                        <!-- Sidebar de Usuários -->
                        <div class="chat-sidebar">
                            <div class="self-status-indicator">
                                <div class="small font-weight-bold text-muted"><?php echo __('Meu Status'); ?>:</div>
                                <div class="dropdown status-dropdown">
                                    <button class="btn btn-light dropdown-toggle" type="button" id="myStatusBtn" data-toggle="dropdown">
                                        <span class="status-dot bg-disponível"></span> <?php echo __('Disponível'); ?>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="#" onclick="updateMyStatus('Disponível')"><span class="status-dot bg-disponível"></span> <?php echo __('Disponível'); ?></a>
                                        <a class="dropdown-item" href="#" onclick="updateMyStatus('Ausente')"><span class="status-dot bg-ausente"></span> <?php echo __('Ausente'); ?></a>
                                        <a class="dropdown-item" href="#" onclick="updateMyStatus('Ocupado')"><span class="status-dot bg-ocupado"></span> <?php echo __('Ocupado'); ?></a>
                                        <a class="dropdown-item" href="#" onclick="updateMyStatus('Offline')"><span class="status-dot bg-offline"></span> <?php echo __('Offline'); ?></a>
                                    </div>
                                </div>
                            </div>
                            <div class="chat-sidebar-header">
                                <div class="d-flex justify-content-between mb-2">
                                    <button class="btn btn-primary btn-sm btn-block" data-toggle="modal" data-target="#createGroupModal" style="background: rgb(44,64,74); border: none;">
                                        <i class="fas fa-users mr-1"></i> <?php echo __('Novo Grupo'); ?>
                                    </button>
                                </div>
                                <div class="input-group sidebar-search">
                                    <input type="text" id="user-search" class="form-control bg-light border-0 small" placeholder="<?php echo __('Buscar colega...'); ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button" style="background: rgb(44,64,74);"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div id="user-list" class="chat-user-list">
                                <div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>
                            </div>
                        </div>

                        <!-- Área da Conversa -->
                        <div class="chat-content">
                            <div id="chat-header" class="chat-header" style="display:none;">
                                <div class="d-flex align-items-center">
                                    <img id="active-user-avatar" src="/assets/img/no-image.png" class="chat-avatar view-profile" style="width:40px;height:40px;cursor:pointer;">
                                    <div>
                                        <div id="active-user-name" class="chat-user-name view-profile" style="cursor:pointer;">...</div>
                                        <div id="active-user-status" class="chat-user-status"><?php echo __('Ativo agora'); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div id="chat-history" class="chat-history">
                                <div class="empty-chat-state">
                                    <i class="fas fa-comments"></i>
                                    <h4><?php echo __('Bem-vindo ao Chat Interno'); ?></h4>
                                    <p><?php echo __('Selecione um colega na lista ao lado para iniciar uma conversa segura e privada.'); ?></p>
                                </div>
                            </div>

                            <div id="chat-input-area" class="chat-input-area" style="display:none;">
                                <form id="chat-form" class="chat-input-container">
                                    <button type="button" class="btn btn-light rounded-circle mr-2" onclick="$('#chat-attach').click()">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                    <input type="file" id="chat-attach" style="display:none;">
                                    
                                    <input type="text" id="chat-msg" class="chat-input-field" placeholder="<?php echo __('Escreva sua mensagem...'); ?>" autocomplete="off">
                                    <button type="submit" class="btn-send-chat">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Perfil do Usuário -->
    <div class="modal fade" id="userProfileModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-body p-0">
                    <div style="height: 120px; background: var(--chat-primary);"></div>
                    <div class="text-center" style="margin-top: -60px;">
                        <img id="modal-user-photo" src="/assets/img/no-image.png" class="shadow-sm" style="width: 120px; height: 120px; border-radius: 30px; border: 5px solid #fff; object-fit: cover;">
                    </div>
                    <div class="p-4 text-center">
                        <h4 id="modal-user-name" class="font-weight-bold text-dark mb-1">...</h4>
                        <p id="modal-user-funcao" class="text-primary mb-3 font-weight-bold small text-uppercase">...</p>
                        
                        <div class="row text-left mt-4 px-3">
                            <div class="col-6 mb-3">
                                <label class="small text-muted mb-0"><?php echo __('Usuário'); ?></label>
                                <div id="modal-user-ad" class="font-weight-bold">...</div>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="small text-muted mb-0"><?php echo __('Setor'); ?></label>
                                <div id="modal-user-setor" class="font-weight-bold">...</div>
                            </div>
                            <div class="col-12 mt-2">
                                <label class="small text-muted mb-0"><?php echo __('E-mail'); ?></label>
                                <div id="modal-user-email" class="font-weight-bold">...</div>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 bg-light text-right">
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-dismiss="modal"><?php echo __('Fechar'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Criação de Grupo -->
    <div class="modal fade" id="createGroupModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-users-cog mr-2"></i><?php echo __('Criar Novo Grupo'); ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="small font-weight-bold"><?php echo __('Nome do Grupo'); ?></label>
                        <input type="text" id="group-name" class="form-control form-control-premium" placeholder="<?php echo __('Ex: Equipe de TI'); ?>">
                    </div>
                    <label class="small font-weight-bold"><?php echo __('Selecionar Membros'); ?></label>
                    <div id="group-members-list" class="list-group" style="max-height: 300px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px;">
                        <!-- Listagem dinâmica de usuários para seleção -->
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?php echo __('Cancelar'); ?></button>
                    <button type="button" class="btn btn-primary btn-sm px-4" onclick="createNewGroup()" style="background: rgb(44,64,74); border: none;"><?php echo __('Criar Grupo'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Membros do Grupo -->
    <div class="modal fade" id="groupMembersModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-users mr-2"></i><?php echo __('Membros do Grupo'); ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-0">
                    <div id="group-members-display-list" class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        <!-- Membros listados aqui -->
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between bg-light">
                    <button type="button" class="btn btn-danger btn-sm rounded-pill px-3" onclick="leaveGroup()"><i class="fas fa-sign-out-alt mr-1"></i><?php echo __('Sair do Grupo'); ?></button>
                    <div>
                        <button type="button" id="admin-add-btn" class="btn btn-primary btn-sm rounded-pill px-3 mr-2" style="display:none;" onclick="openAddMemberModal()"><i class="fas fa-plus mr-1"></i><?php echo __('Adicionar'); ?></button>
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-dismiss="modal"><?php echo __('Fechar'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Adicionar Membro -->
    <div class="modal fade" id="addMemberModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-dark text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title small font-weight-bold"><?php echo __('Convidar para o Grupo'); ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-0">
                    <div id="potential-members-list" class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                        <!-- Usuários não membros aqui -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/theme.js"></script>

    <script>
        let currentDestId = null;
        let isGroupChat = false;
        let lastMessageCount = 0;

        $(document).ready(function() {
            loadUserList();
            fetchMyStatus();

            setInterval(function() {
                if (currentDestId) fetchMessages(currentDestId, true, isGroupChat);
                pollUnread();
                loadUserList();
            }, 5000);

            $('#user-search').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $("#user-list .chat-user-item").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            $('#chat-form').on('submit', function(e) { e.preventDefault(); sendMessage(); });
            $('#chat-attach').on('change', function() { if (this.files && this.files[0]) sendMessage(this.files[0]); });

            $(document).on('click', '.view-profile', function() {
                if (isGroupChat) showGroupMembers(currentDestId);
                else showUserProfile(currentDestId);
            });

            $('#createGroupModal').on('show.bs.modal', function() {
                loadMembersSelection();
            });
        });

        function linkify(text) {
            if (!text) return '';
            const urlRegex = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
            return text.replace(urlRegex, function(url) {
                return '<a href="' + url + '" target="_blank" class="chat-link">' + url + '</a>';
            });
        }

        function createNewGroup() {
            const nome = $('#group-name').val().trim();
            const membros = [];
            $('.member-checkbox:checked').each(function() { membros.push($(this).val()); });

            if (!nome || membros.length === 0) {
                alert('<?php echo __("Preencha o nome e selecione ao menos um membro."); ?>');
                return;
            }

            $.post('ajax_chat.php?action=create_group', { nome: nome, membros: membros }, function(res) {
                if (res.success) {
                    $('#createGroupModal').modal('hide');
                    $('#group-name').val('');
                    loadUserList();
                    selectUser(res.grupo_id, nome, '/assets/img/group-no-image.png', true);
                }
            });
        }

        function loadMembersSelection() {
            $.get('ajax_chat.php?action=list_users', function(res) {
                if (res.success) {
                    let html = '';
                    res.users.forEach(user => {
                        if (!user.is_group) {
                            html += `
                                <label class="list-group-item d-flex align-items-center mb-0" style="cursor:pointer; border:none; border-bottom: 1px solid #f8f9fc;">
                                    <input type="checkbox" class="member-checkbox mr-3" value="${user.id_usuarios}">
                                    <img src="${user.foto}" style="width:30px; height:30px; border-radius:50%; margin-right:10px;">
                                    <span>${user.nome_completo}</span>
                                </label>
                            `;
                        }
                    });
                    $('#group-members-list').html(html);
                }
            });
        }

        function sendMessage(file = null) {
            const msg = $('#chat-msg').val().trim();
            if (!msg && !file) return;

            const formData = new FormData();
            if (isGroupChat) formData.append('grupo_id', currentDestId);
            else formData.append('destinatario_id', currentDestId);
            
            formData.append('mensagem', msg);
            if (file) formData.append('imagem', file);

            $.ajax({
                url: 'ajax_chat.php?action=send',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        $('#chat-msg').val('').focus();
                        $('#chat-attach').val('');
                        fetchMessages(currentDestId, false, isGroupChat);
                    }
                }
            });
        }

        const statusTranslations = {
            'Disponível': '<?php echo __('Disponível'); ?>',
            'Ausente': '<?php echo __('Ausente'); ?>',
            'Ocupado': '<?php echo __('Ocupado'); ?>',
            'Offline': '<?php echo __('Offline'); ?>',
            'Grupo': '<?php echo __('Grupo'); ?>'
        };

        function loadUserList() {
            $.get('ajax_chat.php?action=list_users', function(res) {
                if (res.success) {
                    let html = '';
                    res.users.forEach(user => {
                        const isGroup = !!user.is_group;
                        const actualId = isGroup ? user.id_grupo : user.id_usuarios;
                        const statusClass = isGroup ? '' : 'status-' + user.chat_status.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                        const dotClass = isGroup ? 'bg-primary' : 'bg-' + user.chat_status.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                        const listId = isGroup ? 'g' + user.id_grupo : user.id_usuarios;

                        html += `
                            <div class="chat-user-item ${currentDestId == actualId && isGroupChat == isGroup ? 'active' : ''}" data-id="${listId}" onclick="selectUser(${actualId}, '${user.nome_completo}', '${user.foto}', ${isGroup})">
                                <img src="${user.foto}" class="chat-avatar" ${!isGroup ? `onclick="event.stopPropagation(); showUserProfile(${user.id_usuarios})"` : ''}>
                                <div class="chat-user-info">
                                    <div class="chat-user-name">${user.nome_completo}</div>
                                    <div class="chat-user-status ${statusClass}">
                                        <span class="status-dot ${dotClass}"></span> ${isGroup ? statusTranslations['Grupo'] : (statusTranslations[user.chat_status] || user.chat_status)}
                                    </div>
                                </div>
                                <span class="badge badge-danger badge-counter unread-count" id="unread-${listId}" style="display:none;">0</span>
                            </div>
                        `;
                    });
                    $('#user-list').html(html);
                    pollUnread();
                }
            });
        }

        function selectUser(id, name, photo, isGroup = false) {
            currentDestId = id;
            isGroupChat = isGroup;
            const listId = isGroup ? 'g' + id : id;
            
            $('.chat-user-item').removeClass('active');
            $(`.chat-user-item[data-id="${listId}"]`).addClass('active');
            $('#active-user-name').text(name);
            $('#active-user-avatar').attr('src', photo);
            $('#chat-header').fadeIn();
            $('#chat-input-area').fadeIn();
            $(`#unread-${listId}`).hide().text('0');
            fetchMessages(id, false, isGroup);
        }

        function fetchMessages(otherId, isPolling = false, isGroup = false) {
            const params = isGroup ? { with_group: otherId } : { with: otherId };
            $.get('ajax_chat.php?action=fetch', params, function(res) {
                if (res.success) {
                    if (isPolling && res.messages.length === lastMessageCount) return;
                    let html = '';
                    res.messages.forEach(msg => {
                        let content = linkify(msg.mensagem);
                        if (msg.tipo === 'imagem') {
                            content += `<div class="chat-img-wrapper"><img src="${msg.arquivo_url}" class="chat-img" onclick="window.open(this.src)"></div>`;
                        } else if (msg.tipo === 'arquivo') {
                            content = `
                                <div class="chat-file-card" onclick="window.open('${msg.arquivo_url}')">
                                    <div class="chat-file-icon"><i class="fas fa-file-alt"></i></div>
                                    <div class="chat-file-info">
                                        <div class="chat-file-name text-truncate">${msg.mensagem}</div>
                                        <div class="chat-file-size"><?php echo __('Clique para baixar'); ?></div>
                                    </div>
                                    <i class="fas fa-download ml-2"></i>
                                </div>
                            `;
                        }
                        
                        let senderName = (isGroup && !msg.is_me) ? `<div class="msg-sender-name">${msg.sender_name}</div>` : '';
                        
                        html += `
                            <div class="msg-container ${msg.is_me ? 'me' : 'other'}">
                                ${senderName}
                                <div class="msg-bubble">${content}</div>
                                <div class="msg-time">${msg.time_formatted}</div>
                            </div>
                        `;
                    });
                    $('#chat-history').html(html || '<div class="text-center text-muted p-5 small"><?php echo __('Nenhuma mensagem ainda!'); ?></div>');
                    if (!isPolling || res.messages.length > lastMessageCount) scrollToBottom();
                    lastMessageCount = res.messages.length;
                }
            });
        }

        function showUserProfile(id) {
            $.get('ajax_chat.php?action=get_user_detail', { id: id }, function(res) {
                if (res.success) {
                    const u = res.user;
                    $('#modal-user-photo').attr('src', u.foto);
                    $('#modal-user-name').text(u.nome_completo);
                    $('#modal-user-ad').text(u.usuarioAD);
                    $('#modal-user-email').text(u.email);
                    $('#modal-user-setor').text(u.setor || '-');
                    $('#modal-user-funcao').text(u.funcao || '-');
                    $('#userProfileModal').modal('show');
                }
            });
        }

        function showGroupMembers(id) {
            $.get('ajax_chat.php?action=get_group_members', { id: id }, function(res) {
                if (res.success) {
                    let html = '';
                    const isAdmin = res.is_admin;
                    
                    if (isAdmin) $('#admin-add-btn').show();
                    else $('#admin-add-btn').hide();

                    res.members.forEach(m => {
                        let actions = `
                            <button class="btn btn-light btn-sm rounded-circle mr-1" onclick="selectUser(${m.id_usuarios}, '${m.nome_completo}', '${m.foto}', false)" title="<?php echo __('Conversa Privada'); ?>">
                                <i class="fas fa-comment text-primary"></i>
                            </button>
                        `;
                        
                        if (isAdmin && m.id_usuarios != <?php echo $my_id; ?>) {
                            actions += `
                                <button class="btn btn-light btn-sm rounded-circle" onclick="removeMember(${m.id_usuarios})" title="<?php echo __('Remover do Grupo'); ?>">
                                    <i class="fas fa-user-minus text-danger"></i>
                                </button>
                            `;
                        }

                        html += `
                            <div class="list-group-item d-flex align-items-center py-3">
                                <img src="${m.foto}" style="width:40px; height:40px; border-radius:12px; object-fit:cover; margin-right:15px;">
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold text-dark">${m.nome_completo}</div>
                                    <div class="small text-muted text-uppercase">${m.funcao || '-'}</div>
                                </div>
                                <div class="d-flex">
                                    ${actions}
                                </div>
                            </div>
                        `;
                    });
                    $('#group-members-display-list').html(html);
                    $('#groupMembersModal').modal('show');
                }
            });
        }

        function removeMember(uid) {
            if (confirm('<?php echo __("Tem certeza que deseja remover este membro?"); ?>')) {
                $.post('ajax_chat.php?action=remove_member', { id_grupo: currentDestId, usuario_id: uid }, function(res) {
                    if (res.success) showGroupMembers(currentDestId);
                    else alert(res.message);
                });
            }
        }

        function openAddMemberModal() {
            loadPotentialMembers();
            $('#addMemberModal').modal('show');
        }

        function loadPotentialMembers() {
            $.get('ajax_chat.php?action=list_potential_members', { id_grupo: currentDestId }, function(res) {
                if (res.success) {
                    let html = '';
                    res.users.forEach(u => {
                        html += `
                            <div class="list-group-item d-flex align-items-center py-2" style="cursor:pointer" onclick="addMember(${u.id_usuarios})">
                                <img src="${u.foto}" style="width:30px; height:30px; border-radius:50%; margin-right:10px;">
                                <div class="small font-weight-bold">${u.nome_completo}</div>
                                <i class="fas fa-plus-circle ml-auto text-success"></i>
                            </div>
                        `;
                    });
                    $('#potential-members-list').html(html || '<div class="p-3 text-center small text-muted"><?php echo __("Todos os usuários já estão no grupo."); ?></div>');
                }
            });
        }

        function addMember(uid) {
            $.post('ajax_chat.php?action=add_member', { id_grupo: currentDestId, usuario_id: uid }, function(res) {
                if (res.success) {
                    $('#addMemberModal').modal('hide');
                    showGroupMembers(currentDestId);
                }
            });
        }

        function leaveGroup() {
            if (confirm('<?php echo __("Tem certeza que deseja sair deste grupo? Se você for o administrador, outro membro será promovido."); ?>')) {
                $.post('ajax_chat.php?action=leave_group', { id_grupo: currentDestId }, function(res) {
                    if (res.success) {
                        $('#groupMembersModal').modal('hide');
                        $('#chat-header').hide();
                        $('#chat-input-area').hide();
                        $('#chat-history').html('<div class="empty-chat-state"><i class="fas fa-comments"></i><h4><?php echo __("Você saiu do grupo"); ?></h4></div>');
                        currentDestId = null;
                        loadUserList();
                    }
                });
            }
        }

        function updateMyStatus(status) {
            $.post('ajax_chat.php?action=update_status', { status: status }, function(res) {
                if (res.success) updateStatusUI(status);
            });
        }

        function updateStatusUI(status) {
            const dotClass = 'bg-' + status.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            $('#myStatusBtn').html(`<span class="status-dot ${dotClass}"></span> ${status}`);
        }

        function fetchMyStatus() {
            $.get('ajax_chat.php?action=get_my_status', function(res) {
                if (res.success) updateStatusUI(res.status);
            });
        }

        function pollUnread() {
            $.get('ajax_chat.php?action=poll', function(res) {
                if (res.success) {
                    const currentListId = isGroupChat ? 'g' + currentDestId : currentDestId;
                    for (const [listId, count] of Object.entries(res.unread)) {
                        if (listId != currentListId && count > 0) $(`#unread-${listId}`).text(count).show();
                        else if (listId == currentListId) $(`#unread-${listId}`).hide();
                    }
                    // Atualiza distintivo global no sidebar
                    const globalBadge = $('#global-chat-badge');
                    if (res.total > 0) {
                        globalBadge.text(res.total).show();
                    } else {
                        globalBadge.hide();
                    }
                }
            });
        }

        function scrollToBottom() {
            const h = document.getElementById('chat-history');
            h.scrollTop = h.scrollHeight;
        }
    </script>
</body>
</html>
