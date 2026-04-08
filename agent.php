<?php
/**
 * INTERFACE DO CHATBOT: agent.php
 * Página frontal do Agente Inteligente, fornecendo a interface de chat
 * para interação com a IA Gemini e comandos rápidos do sistema.
 */
include_once 'auth.php';
include_once 'conexao.php';

// Buscar configuração de ativação do chat
$chat_ativo = true;
$sql_config = "SELECT ia_chat_ativo, ia_agente_ativo FROM configuracoes_alertas LIMIT 1";
$res_config = mysqli_query($conn, $sql_config);
if ($res_config && mysqli_num_rows($res_config) > 0) {
    $row_config = mysqli_fetch_assoc($res_config);
    if (($row_config['ia_chat_ativo'] ?? 1) == 0 || ($row_config['ia_agente_ativo'] ?? 1) == 0) {
        $chat_ativo = false;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('IA Agent'); ?> - Asset MGT</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <?php include_once 'sidebar_style.php'; ?>
    <style>
        .card-shadow { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-shadow:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important; 
        }
        .chat-container {
            height: 60vh;
            overflow-y: auto;
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            padding: 20px;
            background-color: #f8f9fc;
            scroll-behavior: smooth;
        }

        .chat-message {
            margin-bottom: 12px;
            display: flex;
            flex-direction: column;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message-user {
            align-items: flex-end;
        }

        .message-bot {
            align-items: flex-start;
        }

        .message-content {
            padding: 12px 16px;
            border-radius: 18px;
            max-width: 80%;
            word-wrap: break-word;
            line-height: 1.5;
            font-size: 14px;
        }

        .message-user .message-content {
            background-color: rgb(44, 64, 74);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-bot .message-content {
            background-color: #e8ecf1;
            color: #2d3748;
            border-bottom-left-radius: 4px;
        }

        .message-time {
            font-size: 0.7rem;
            color: #858796;
            margin-top: 3px;
        }

        .message-content strong,
        .message-content b {
            font-weight: 700;
        }

        .typing-dots span {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #858796;
            margin: 0 2px;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .typing-dots span:nth-child(1) {
            animation-delay: -0.32s;
        }

        .typing-dots span:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes bounce {

            0%,
            80%,
            100% {
                transform: scale(0);
            }

            40% {
                transform: scale(1);
            }
        }

        .gemini-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #4285f4, #34a853, #fbbc05, #ea4335);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
            font-size: 13px;
        }

        .btn-clear-chat {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 6px;
        }
    </style>
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
                    <h3 class="text-dark mb-4 animate__animated animate__fadeInDown"><i class="fas fa-robot mr-2 text-primary"></i><?php echo __('Agente Inteligente - Assistente Virtual'); ?></h3>
                    <div class="card shadow card-shadow mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold" style="color: rgb(44,64,74);">
                                <i class="fas fa-robot mr-1"></i> <?php echo __('Chat com IA'); ?>
                                <span class="gemini-badge ml-2">✦ Gemini</span>
                            </h6>
                            <button class="btn btn-outline-secondary btn-clear-chat" onclick="clearChat()"
                                title="<?php echo __('Limpar conversa'); ?>">
                                <i class="fas fa-trash-alt mr-1"></i> <?php echo __('Limpar'); ?>
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (!$chat_ativo): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-comment-slash fa-4x text-gray-300 mb-4"></i>
                                    <h2 class="font-weight-bold" style="font-size: 2.5rem;"><i class="fas fa-headset mr-2 text-primary"></i><?php echo __('Como podemos ajudar você hoje?'); ?></h2>
                                    <p class="text-muted"><?php echo __('Esta funcionalidade foi desabilitada nas configurações do sistema pelo administrador.'); ?></p>
                                    <a href="configuracoes.php" class="btn btn-primary btn-sm mt-3" style="background: rgb(44,64,74); border: none;">
                                        <i class="fas fa-cogs mr-1"></i> <?php echo __('Ir para Configurações'); ?>
                                    </a>
                                </div>
                            <?php else: ?>
                                <div id="chat-history" class="chat-container mb-3">
                                    <div class="chat-message message-bot">
                                        <div class="message-content">
                                            <?php echo __('Olá,'); ?>
                                            <strong><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></strong>! <?php echo __('Eu sou o assistente virtual do sistema. Posso ajudar com informações sobre ativos, chamados ou tirar dúvidas. Como posso ajudar hoje?'); ?>
                                        </div>
                                        <div class="message-time"><?php echo date('H:i'); ?></div>
                                    </div>
                                </div>
                                <div id="quick-suggestions" class="mb-3 d-flex flex-wrap gap-2">
                                    <button class="btn btn-sm btn-outline-info mr-2 mb-2"
                                        onclick="useSuggestion('Resumo do sistema')">📊 <?php echo __('Resumo do sistema'); ?></button>
                                    <button class="btn btn-sm btn-outline-info mr-2 mb-2"
                                        onclick="useSuggestion('Quais licenças temos?')">🔑 <?php echo __('Licenças atuais'); ?></button>
                                    <button class="btn btn-sm btn-outline-info mr-2 mb-2"
                                        onclick="useSuggestion('Ativos em manutenção')">🔧 <?php echo __('Em manutenção'); ?></button>
                                    <button class="btn btn-sm btn-outline-info mr-2 mb-2"
                                        onclick="useSuggestion('Meus ativos')">📦 <?php echo __('Meus ativos'); ?></button>
                                    <button class="btn btn-sm btn-outline-info mr-2 mb-2"
                                        onclick="useSuggestion('Sugira melhorias')">💡 <?php echo __('Sugerir melhorias'); ?></button>
                                </div>
                                <div class="input-group">
                                    <input type="text" id="user-input" class="form-control"
                                        placeholder="<?php echo __('Pergunte qualquer coisa... (ex: \'resumo do sistema\', \'sugira melhorias\')'); ?>"
                                        onkeypress="handleKeyPress(event)"
                                        style="border-radius: 8px 0 0 8px; border-color: rgba(44,64,74,0.3);">
                                    <div class="input-group-append">
                                        <button class="btn text-white" type="button" onclick="sendMessage()"
                                            style="background: rgb(44,64,74); border-radius: 0 8px 8px 0;"><?php echo __('Enviar'); ?> <i
                                                class="fas fa-paper-plane"></i></button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">

                                </div>
                            <?php endif; ?>
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
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script>
        function handleKeyPress(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        }

        function useSuggestion(text) {
            document.getElementById('user-input').value = text;
            sendMessage();
        }

        function sendMessage() {
            const input = document.getElementById('user-input');
            const message = input.value.trim();
            if (!message) return;

            // Add user message to chat
            addMessage(message, 'user');
            input.value = '';
            input.focus();

            // Loading state with animated dots
            const loadingId = addMessage('', 'bot', true);

            // Send to backend
            fetch('agent_backend.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'message=' + encodeURIComponent(message)
            })
                .then(response => response.json())
                .then(data => {
                    removeMessage(loadingId);
                    addMessage(data.reply, 'bot');
                })
                .catch(error => {
                    removeMessage(loadingId);
                    addMessage('⚠️ ' + "<?php echo __('Desculpe, ocorreu um erro ao processar sua solicitação. Tente novamente.'); ?>", 'bot');
                    console.error('Error:', error);
                });
        }

        function formatMarkdown(text) {
            // Bold: **text**
            text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            // Newlines
            text = text.replace(/\n/g, '<br>');
            return text;
        }

        function addMessage(text, sender, isLoading = false) {
            const history = document.getElementById('chat-history');
            const msgDiv = document.createElement('div');
            msgDiv.className = `chat-message message-${sender}`;
            const msgId = 'msg-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);
            if (isLoading) msgDiv.id = msgId;

            const time = new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });

            if (isLoading) {
                msgDiv.innerHTML = `
                    <div class="message-content">
                        <div class="typing-dots"><span></span><span></span><span></span></div>
                    </div>
                `;
            } else {
                const formattedText = formatMarkdown(text);
                msgDiv.innerHTML = `
                    <div class="message-content">${formattedText}</div>
                    <div class="message-time">${time}</div>
                `;
            }

            history.appendChild(msgDiv);
            history.scrollTop = history.scrollHeight;
            return msgId;
        }

        function removeMessage(id) {
            const msg = document.getElementById(id);
            if (msg) msg.remove();
        }

        function clearChat() {
            const history = document.getElementById('chat-history');
            history.innerHTML = `
                <div class="chat-message message-bot">
                    <div class="message-content">
                        <?php echo __('Olá,'); ?> <strong><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></strong>! 👋 <?php echo __('Eu sou o assistente virtual do sistema, integrado com Google Gemini AI. Posso ajudar com informações sobre ativos, chamados, fornecedores e muito mais. Como posso ajudar hoje?'); ?>
                    </div>
                    <div class="message-time">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
                </div>
            `;
            // Clear server-side history
            fetch('agent_backend.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'clear_history=1'
            });
        }
    </script>
</body>

</html>