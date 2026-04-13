<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<?php
/**
 * MENU LATERAL: sidebar_menu.php
 * Componente de navegação principal, contendo links para todos os módulos do sistema.
 */
?>
<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar"
    style="background: transparent !important; box-shadow: none !important;">
    <hr class="sidebar-divider my-0">
    <!-- Principal -->
    <div class="sidebar-heading"><?php echo __('Menu Principal'); ?></div>
    <li class="nav-item">
        <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
            <i class="fas fa-chart-pie"></i><span><?php echo __('Dashboard'); ?></span>
        </a>
    </li>
    <?php if ($_SESSION['nivelUsuario'] == 'Admin' || $_SESSION['nivelUsuario'] == 'Suporte'): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'inicio.php') ? 'active' : ''; ?>" href="inicio.php">
                <i class="fas fa-tachometer-alt"></i><span><?php echo __('Console Operacional'); ?></span>
            </a>
        </li>
    <?php endif; ?>

    <?php if ($_SESSION['nivelUsuario'] == 'Admin' || $_SESSION['nivelUsuario'] == 'Suporte'): ?>
        <hr class="sidebar-divider">

        <!-- Gestão -->
        <div class="sidebar-heading"><?php echo __('Gestão de Ativos'); ?></div>
        <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current_page, 'equipamento') !== false || strpos($current_page, 'ativo') !== false) && !isset($_GET['status']) ? 'active' : ''; ?>"
                href="equipamentos.php">
                <i class="fas fa-laptop-medical"></i><span><?php echo __('Inventário de Ativos'); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo isset($_GET['status']) && $_GET['status'] == 'Manutencao' ? 'active' : ''; ?>"
                href="equipamentos.php?status=Manutencao">
                <i class="fas fa-tools"></i><span><?php echo __('Ativos em Manutenção'); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current_page, 'leiloe') !== false) ? 'active' : ''; ?>"
                href="leiloes.php">
                <i class="fas fa-gavel"></i><span><?php echo __('Lotes de Leilão'); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current_page, 'doado') !== false) ? 'active' : ''; ?>"
                href="ativos_doados.php">
                <i class="fas fa-hand-holding-heart"></i><span><?php echo __('Histórico de Doações'); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current_page, 'leiloados') !== false) ? 'active' : ''; ?>"
                href="ativos_leiloados.php">
                <i class="fas fa-file-invoice-dollar"></i><span><?php echo __('Histórico de Leilões'); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current_page, 'licenca') !== false) ? 'active' : ''; ?>"
                href="licencas.php">
                <i class="fas fa-file-contract"></i><span><?php echo __('Licenças & Software'); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current_page, 'centro_de_custo') !== false) ? 'active' : ''; ?>"
                href="centro_de_custo.php">
                <i class="fas fa-wallet"></i><span><?php echo __('Centros de Custo'); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current_page, 'locais') !== false) ? 'active' : ''; ?>"
                href="locais.php">
                <i class="fas fa-map-marked-alt"></i><span><?php echo __('Locais & Infra'); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current_page, 'fornecedor') !== false) ? 'active' : ''; ?>"
                href="fornecedores.php">
                <i class="fas fa-handshake"></i><span><?php echo __('Fornecedores'); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current_page, 'usuario') !== false) ? 'active' : ''; ?>"
                href="usuarios.php">
                <i class="fas fa-user-shield"></i><span><?php echo __('Controle de Usuários'); ?></span>
            </a>
        </li>
    <?php endif; ?>

    <hr class="sidebar-divider">

    <!-- Operacional -->
    <div class="sidebar-heading"><?php echo __('Operacional'); ?></div>
    <li class="nav-item">
        <a class="nav-link <?php echo (strpos($current_page, 'chamado') !== false) ? 'active' : ''; ?>"
            href="chamados.php">
            <i class="fas fa-ticket-alt"></i><span><?php echo __('Central de Chamados'); ?></span>
        </a>
    </li>
    <?php if ($_SESSION['nivelUsuario'] == 'Admin' || $_SESSION['nivelUsuario'] == 'Suporte'): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo (strpos($current_page, 'relatorio') !== false) ? 'active' : ''; ?>"
                href="relatorios.php">
                <i class="fas fa-file-invoice"></i><span><?php echo __('Relatórios Internos'); ?></span>
            </a>
        </li>
    <?php endif; ?>

    <hr class="sidebar-divider">

    <!-- Outros -->
    <div class="sidebar-heading"><?php echo __('Inteligência & Ajuda'); ?></div>
    <li class="nav-item">
        <a class="nav-link <?php echo (strpos($current_page, 'kb') !== false) ? 'active' : ''; ?>" href="gerenciar_kb.php">
            <i class="fas fa-book-open"></i><span><?php echo __('Base de Conhecimento'); ?></span>
        </a>
    </li>
    <?php if ($_SESSION['nivelUsuario'] == 'Admin' || $_SESSION['nivelUsuario'] == 'Suporte'): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'insights.php') ? 'active' : ''; ?>" href="insights.php">
                <i class="fas fa-lightbulb"></i><span><?php echo __('Previsão & Prevenção'); ?></span>
            </a>
        </li>
    <?php endif; ?>
    <li class="nav-item">
        <a class="nav-link <?php echo ($current_page == 'chat_interno.php') ? 'active' : ''; ?>" href="chat_interno.php">
            <i class="fas fa-comments"></i><span><?php echo __('Chat Interno'); ?></span>
            <span class="badge badge-danger badge-counter ml-1" id="global-chat-badge" style="display:none;font-size: 0.6rem; vertical-align: top;">0</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo ($current_page == 'agent.php') ? 'active' : ''; ?>" href="agent.php">
            <i class="fas fa-brain"></i><span><?php echo __('Assistente IA'); ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo ($current_page == 'suporte.php') ? 'active' : ''; ?>" href="suporte.php">
            <i class="fas fa-headset"></i><span><?php echo __('Suporte Técnico'); ?></span>
        </a>
    </li>
</ul>
<div class="text-center d-none d-md-inline">
    <button class="btn rounded-circle border-0" id="sidebarToggle" type="button"></button>
</div>
<?php include_once 'idle_timeout_modal.php'; ?>

<script>
(function () {
    const ul = document.getElementById('accordionSidebar');
    if (!ul) return;

    document.querySelectorAll('#accordionSidebar .nav-link').forEach(function (link) {
        link.addEventListener('mouseenter', function () {
            ul.style.overflowY = 'visible';
            ul.style.overflowX = 'visible';
        });
        link.addEventListener('mouseleave', function () {
            ul.style.overflowY = 'auto';
            ul.style.overflowX = 'visible';
        });
    });

    // Heartbeat e Notificações do Chat (Vanilla JS for Global Compatibility)
    function pollGlobalChat() {
        fetch('ajax_chat.php?action=poll')
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    const sBadge = document.getElementById('global-chat-badge');
                    const tBadge = document.getElementById('topbar-chat-badge');
                    
                    if (res.total > 0) {
                        if (sBadge) {
                            sBadge.textContent = res.total;
                            sBadge.style.display = 'inline-block';
                        }
                        if (tBadge) tBadge.style.display = 'block';
                    } else {
                        if (sBadge) sBadge.style.display = 'none';
                        if (tBadge) tBadge.style.display = 'none';
                    }
                }
            })
            .catch(err => console.error('Chat Poll Error:', err));
    }

    // Inicialização segura
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        pollGlobalChat();
    } else {
        document.addEventListener('DOMContentLoaded', pollGlobalChat);
    }
    
    // Intervalo de atualização (Heartbeat + Notificações)
    setInterval(pollGlobalChat, 30000); // A cada 30 segundos
})();
</script>