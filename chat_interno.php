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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/theme.js"></script>

    <script>
        let currentDestId = null;
        let lastMessageCount = 0;

        $(document).ready(function() {
            loadUserList();
            fetchMyStatus();

            setInterval(function() {
                if (currentDestId) fetchMessages(currentDestId, true);
                pollUnread();
                loadUserList();
            }, 5000);

            $('#user-search').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $("#user-list .chat-user-item").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            $('#chat-form').on('submit', function(e) {
                e.preventDefault();
                sendMessage();
            });

            // Upload de imagem ao selecionar
            $('#chat-attach').on('change', function() {
                if (this.files && this.files[0]) {
                    sendMessage(this.files[0]);
                }
            });

            // Delegar clique para abrir perfil
            $(document).on('click', '.view-profile', function() {
                const id = currentDestId;
                if (id) showUserProfile(id);
            });
        });

        function linkify(text) {
            const urlRegex = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
            return text.replace(urlRegex, function(url) {
                return '<a href="' + url + '" target="_blank" class="chat-link">' + url + '</a>';
            });
        }

        function sendMessage(file = null) {
            const msg = $('#chat-msg').val().trim();
            if (!msg && !file) return;

            const formData = new FormData();
            formData.append('destinatario_id', currentDestId);
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
                        fetchMessages(currentDestId);
                    } else if (res.message) {
                        alert(res.message);
                    }
                }
            });
        }

        function loadUserList() {
            $.get('ajax_chat.php?action=list_users', function(res) {
                if (res.success) {
                    let html = '';
                    res.users.forEach(user => {
                        const statusClass = 'status-' + user.chat_status.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                        const dotClass = 'bg-' + user.chat_status.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                        html += `
                            <div class="chat-user-item ${currentDestId == user.id_usuarios ? 'active' : ''}" data-id="${user.id_usuarios}" onclick="selectUser(${user.id_usuarios}, '${user.nome_completo}', '${user.foto}')">
                                <img src="${user.foto}" class="chat-avatar" onclick="event.stopPropagation(); showUserProfile(${user.id_usuarios})">
                                <div class="chat-user-info">
                                    <div class="chat-user-name">${user.nome_completo}</div>
                                    <div class="chat-user-status ${statusClass}">
                                        <span class="status-dot ${dotClass}"></span> ${user.chat_status}
                                    </div>
                                </div>
                                <span class="badge badge-danger badge-counter unread-count" id="unread-${user.id_usuarios}" style="display:none;">0</span>
                            </div>
                        `;
                    });
                    $('#user-list').html(html);
                    pollUnread();
                }
            });
        }

        function selectUser(id, name, photo) {
            currentDestId = id;
            $('.chat-user-item').removeClass('active');
            $(`.chat-user-item[data-id="${id}"]`).addClass('active');
            $('#active-user-name').text(name);
            $('#active-user-avatar').attr('src', photo);
            $('#chat-header').fadeIn();
            $('#chat-input-area').fadeIn();
            $(`#unread-${id}`).hide().text('0');
            fetchMessages(id);
        }

        function fetchMessages(otherId, isPolling = false) {
            $.get('ajax_chat.php?action=fetch', { with: otherId }, function(res) {
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
                        html += `
                            <div class="msg-container ${msg.is_me ? 'me' : 'other'}">
                                <div class="msg-bubble">${content}</div>
                                <div class="msg-time">${msg.time_formatted}</div>
                            </div>
                        `;
                    });
                    $('#chat-history').html(html || '<div class="text-center text-muted p-5 small"><?php echo __('Nenhuma mensagem ainda. Comece a conversar!'); ?></div>');
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
                    for (const [userId, count] of Object.entries(res.unread)) {
                        if (userId != currentDestId && count > 0) $(`#unread-${userId}`).text(count).show();
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
