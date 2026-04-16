<?php
$userName = htmlspecialchars($_SESSION['nome_usuario']);
$fotoPerfil = !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/avatar5.jpeg';
$isAdminOrSuporte = $_SESSION['nivelUsuario'] !== 'Usuário';
?>
<style>
/* Estilo Premium para Ícones da Topbar */
.nav-link-utility {
    background: none;
    border-radius: 50% !important;
    width: 38px;
    height: 38px;
    display: flex !important;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: none;
    color: rgb(44, 64, 74) !important;
    padding: 0 !important;
}
.nav-link-utility:hover {
    background: rgba(44, 64, 74, 0.05);
    transform: translateY(-2px);
    color: #4f46e5 !important;
}
.btn-help-manual {
    color: #4f46e5 !important;
}
.btn-help-manual:hover {
    background: rgba(79, 70, 229, 0.15) !important;
}


/* Customização Premium Intro.js */
.introjs-tooltip {
    border-radius: 16px !important;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15) !important;
    border: 1px solid rgba(0,0,0,0.05) !important;
    padding: 20px !important;
    font-family: 'Inter', sans-serif !important;
}
.introjs-tooltiptext {
    font-size: 0.95rem !important;
    color: #475569 !important;
    line-height: 1.5 !important;
}
.introjs-tooltip-title {
    font-weight: 700 !important;
    color: #1e293b !important;
    font-size: 1.1rem !important;
    margin-bottom: 8px !important;
}
.introjs-nextbutton, .introjs-prevbutton, .introjs-donebutton {
    border-radius: 8px !important;
    font-weight: 600 !important;
    text-shadow: none !important;
    transition: all 0.2s !important;
    border: none !important;
    padding: 8px 16px !important;
}
.introjs-nextbutton { background-image: linear-gradient(to right, #4f46e5, #4338ca) !important; color: white !important; }
.introjs-prevbutton { background: #f1f5f9 !important; color: #475569 !important; }
.introjs-donebutton { background: #10b981 !important; color: white !important; }
.introjs-progress { background-color: #e2e8f0 !important; border-radius: 10px !important; }
.introjs-progressbar { background-color: #4f46e5 !important; border-radius: 10px !important; }
</style>
<nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top" style="margin: 5px 23px;">
    <div class="container-fluid">
        <button class="btn btn-link d-md-none rounded-circle mr-3" id="sidebarToggleTop-1" type="button">
            <i class="fas fa-bars"></i>
        </button>
        <form class="form-inline d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search position-relative">
            <div class="input-group">
                <input class="bg-light form-control border-0 small" type="text"
                    placeholder="<?php echo __('Pesquisar por equipamentos, usuários...'); ?>" id="globalSearchInput" autocomplete="off">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="button" style="background: rgb(44,64,74); border: none;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div id="globalSearchResults" class="dropdown-menu shadow animated--grow-in" style="width: 100%; display: none;"></div>
        </form>

        <ul class="navbar-nav flex-nowrap ml-auto">
            <!-- Nav Item - Alerts -->
            <?php if ($isAdminOrSuporte): ?>
            <li class="nav-item dropdown no-arrow mx-1 align-self-center">
                <a class="nav-link dropdown-toggle nav-link-utility" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-bell fa-fw"></i>
                    <span class="badge badge-danger badge-counter" id="topbar-alerts-badge" style="display:none; top: 0; right: 0;"></span>
                </a>
                <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                    <h6 class="dropdown-header" style="background: #2c404a; border: none; padding: 12px;">
                        <?php echo __('Alertas do Sistema'); ?>
                    </h6>
                    <div id="topbar-alerts-list">
                        <div class="dropdown-item d-flex align-items-center py-3">
                            <span class="text-gray-500 small"><?php echo __('Nenhum alerta crítico no momento.'); ?></span>
                        </div>
                    </div>
                    <a class="dropdown-item text-center small text-gray-500 font-weight-bold" href="insights.php" style="border-top: 1px solid #f2f2f2;"><?php echo __('Ver no Insights'); ?></a>
                </div>
            </li>
            <?php endif; ?>

            <!-- Nav Item - Chat Messages -->
            <li class="nav-item dropdown no-arrow mx-1 align-self-center">
                <a class="nav-link dropdown-toggle nav-link-utility" href="chat_interno.php" id="messagesDropdown" role="button">
                    <i class="fas fa-envelope fa-fw"></i>
                    <span class="badge badge-danger" id="topbar-chat-badge" style="display:none; top: 0; right: 0;"></span>
                </a>
            </li>

            <!-- Botão de Ajuda Interativo -->
            <li class="nav-item mx-1 align-self-center">
                <a class="nav-link nav-link-utility btn-help-manual" href="javascript:void(0);" id="start-manual" title="<?php echo __('Ajuda Interativa'); ?>">
                    <i class="fas fa-question-circle fa-fw"></i>
                </a>
            </li>

            <div class="topbar-divider d-none d-sm-block"></div>

            <li class="nav-item dropdown no-arrow">
                <a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#">
                    <span class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo $userName; ?></span>
                    <img class="border rounded-circle img-profile" src="<?php echo $fotoPerfil; ?>">
                </a>
                <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in">
                    <a class="dropdown-item" href="perfil_usuario.php"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i><?php echo __('Perfil'); ?></a>
                    <a class="dropdown-item" href="configuracoes.php"><i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i><?php echo __('Configurações'); ?></a>
                    <?php if ($isAdminOrSuporte): ?>
                        <a class="dropdown-item" href="equipamentos.php?status=Manutencao"><i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i><?php echo __('Ativos em Manutenção'); ?></a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;<?php echo __('Sair'); ?></a>
                </div>
            </li>
        </ul>
    </div>
</nav>

<script>
/**
 * Polling de Notificações de Estoque e Sistema
 */
function pollSystemNotifications() {
    fetch('ajax_notificacoes.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('topbar-alerts-badge');
            const list = document.getElementById('topbar-alerts-list');
            if(!badge || !list) return;

            if (data.count > 0) {
                badge.textContent = data.count > 9 ? '9+' : data.count;
                badge.style.display = 'block';
                
                let html = '';
                data.alerts.forEach(alert => {
                    html += `
                        <a class="dropdown-item d-flex align-items-center" href="${alert.link}">
                            <div class="mr-3">
                                <div class="icon-circle ${alert.bg_class}">
                                    <i class="${alert.icon} text-white"></i>
                                </div>
                            </div>
                            <div>
                                <div class="small text-gray-500">${alert.title}</div>
                                <span class="font-weight-bold" style="font-size: 0.8rem;">${alert.subtitle}</span>
                            </div>
                        </a>`;
                });
                list.innerHTML = html;
            } else {
                badge.style.display = 'none';
                list.innerHTML = `<div class="dropdown-item d-flex align-items-center py-3">
                    <span class="text-gray-500 small"><?php echo __('Nenhum alerta crítico no momento.'); ?></span>
                </div>`;
            }
        })
        .catch(err => console.error('Notification Error:', err));
}

document.addEventListener('DOMContentLoaded', () => {
    pollSystemNotifications();
    setInterval(pollSystemNotifications, 60000); // Verifica a cada minuto
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js"></script>
<script src="/assets/js/system_manual.js"></script>
