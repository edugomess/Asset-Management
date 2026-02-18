<?php
include 'auth.php';
include 'conexao.php';
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>IA Agent - Asset MGT</title>
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
    <style>
        .chat-container {
            height: 60vh;
            overflow-y: auto;
            border: 1px solid #e3e6f0;
            border-radius: 5px;
            padding: 15px;
            background-color: #f8f9fc;
        }
        .chat-message {
            margin-bottom: 10px;
            display: flex;
            flex-direction: column;
        }
        .message-user {
            align-items: flex-end;
        }
        .message-bot {
            align-items: flex-start;
        }
        .message-content {
            padding: 10px 15px;
            border-radius: 20px;
            max-width: 80%;
            word-wrap: break-word;
        }
        .message-user .message-content {
            background-color: #4e73df;
            color: white;
            border-bottom-right-radius: 5px;
        }
        .message-bot .message-content {
            background-color: #e3e6f0;
            color: #5a5c69;
            border-bottom-left-radius: 5px;
        }
        .message-time {
            font-size: 0.7rem;
            color: #858796;
            margin-top: 2px;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0" style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0"><a class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#">
                    <div class="sidebar-brand-icon rotate-n-15"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-layout-distribute-horizontal" style="width: 30px;height: 30px;">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <line x1="4" y1="4" x2="20" y2="4"></line>
                            <line x1="4" y1="20" x2="20" y2="20"></line>
                            <rect x="6" y="9" width="12" height="6" rx="2"></rect>
                        </svg></div>
                    <div class="sidebar-brand-text mx-3"><span>ASSET MGT</span></div>
                </a>
                <hr class="sidebar-divider my-0">
                <ul class="navbar-nav text-light" id="accordionSidebar">
                    <li class="nav-item"><a class="nav-link" href="/index.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/inicio.php"><i class="fas fa-home"></i><span> Início</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/usuarios.php"><i class="fas fa-user-alt"></i><span> Usuários</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/centro_de_custo.php"><i class="fas fa-file-invoice-dollar"></i><span> Centro de Custo</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/fornecedores.php"><i class="fas fa-hands-helping"></i><span> Fornecedores</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/equipamentos.php"><i class="fas fa-boxes"></i><span> Ativos</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/relatorios.php"><i class="fas fa-scroll"></i><span> Relatórios</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/chamados.php"><i class="fas fa-headset"></i><span> Chamados</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/suporte.php"><i class="fas fa-user-cog"></i><span> Suporte</span></a></li>
                    <li class="nav-item"><a class="nav-link active" href="/agent.php"><i class="fas fa-robot"></i><span> IA Agent</span></a></li>
                </ul>
                <div class="text-center d-none d-md-inline"><button class="btn rounded-circle border-0" id="sidebarToggle" type="button"></button></div>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top" style="margin: 23px;">
                    <button class="btn btn-link d-md-none rounded-circle mr-3" id="sidebarToggleTop" type="button"><i class="fas fa-bars"></i></button>
                    <ul class="navbar-nav flex-nowrap ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#"><span class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span><img class="border rounded-circle img-profile" src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/avatar1.jpeg'; ?>"></a>
                                <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in"><a class="dropdown-item" href="profile.php"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a><a class="dropdown-item" href="#"><i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a>
                                    <div class="dropdown-divider"></div><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </nav>
                <div class="container-fluid">
                    <h3 class="text-dark mb-4">Agente Inteligente - Assistente Virtual</h3>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Chat com IA</h6>
                        </div>
                        <div class="card-body">
                            <div id="chat-history" class="chat-container mb-3">
                                <div class="chat-message message-bot">
                                    <div class="message-content">
                                        Olá, <strong><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></strong>! Eu sou o assistente virtual do sistema. Posso ajudar com informações sobre ativos, chamados ou tirar dúvidas. Como posso ajudar hoje?
                                    </div>
                                    <div class="message-time"><?php echo date('H:i'); ?></div>
                                </div>
                            </div>
                            <div class="input-group">
                                <input type="text" id="user-input" class="form-control" placeholder="Digite sua pergunta aqui (ex: 'quantos ativos temos?', 'listar chamados abertos')..." onkeypress="handleKeyPress(event)">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" onclick="sendMessage()">Enviar <i class="fas fa-paper-plane"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="bg-white sticky-footer" style="padding: 0;">
                <div class="container my-auto">
                    <div class="text-center my-auto copyright"><span>DEGB Copyright © 2015-2024</span></div>
                </div>
            </footer>
        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
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

        function sendMessage() {
            const input = document.getElementById('user-input');
            const message = input.value.trim();
            if (!message) return;

            // Add user message to chat
            addMessage(message, 'user');
            input.value = '';

            // Loading state
            const loadingId = addMessage('Digitando...', 'bot', true);

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
                // Remove loading message
                removeMessage(loadingId);
                // Add bot response
                addMessage(data.reply, 'bot');
            })
            .catch(error => {
                removeMessage(loadingId);
                addMessage('Desculpe, ocorreu um erro ao processar sua solicitação.', 'bot');
                console.error('Error:', error);
            });
        }

        function addMessage(text, sender, isLoading = false) {
            const history = document.getElementById('chat-history');
            const msgDiv = document.createElement('div');
            msgDiv.className = `chat-message message-${sender}`;
            if (isLoading) msgDiv.id = 'loading-msg-' + Date.now();
            
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            // Format newlines
            const formattedText = text.replace(/\n/g, '<br>');

            msgDiv.innerHTML = `
                <div class="message-content">${formattedText}</div>
                <div class="message-time">${time}</div>
            `;
            
            history.appendChild(msgDiv);
            history.scrollTop = history.scrollHeight;
            return msgDiv.id;
        }

        function removeMessage(id) {
            const msg = document.getElementById(id);
            if (msg) msg.remove();
        }
    </script>
</body>

</html>
