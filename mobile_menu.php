<?php
/**
 * NOVO SELETOR DE PÁGINAS MOBILE
 * Este componente substitui a sidebar tradicional em smartphones para uma navegação mais intuitiva.
 */
$current_page = basename($_SERVER['PHP_SELF']);
$isAdminOrSuporte = $_SESSION['nivelUsuario'] == 'Admin' || $_SESSION['nivelUsuario'] == 'Suporte';
?>

<div id="mobileQuickMenu" class="mobile-quick-menu">
    <div class="mobile-menu-header">
        <div class="menu-brand">
            <i class="fas fa-layer-group mr-2"></i>
            <span><?php echo __('Navegação Rápida'); ?></span>
        </div>
        <button id="closeMobileMenu" class="btn-close-menu">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="mobile-menu-content">
        <div class="menu-grid">
            <!-- SECTION: PRINCIPAL -->
            <a href="index.php" class="menu-card <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-chart-pie"></i></div>
                <div class="card-label"><?php echo __('Dashboard'); ?></div>
            </a>

            <?php if ($isAdminOrSuporte): ?>
            <a href="inicio.php" class="menu-card <?php echo ($current_page == 'inicio.php') ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-tachometer-alt"></i></div>
                <div class="card-label"><?php echo __('Console'); ?></div>
            </a>

            <!-- SECTION: GESTÃO -->
            <a href="equipamentos.php" class="menu-card <?php echo ($current_page == 'equipamentos.php' && !isset($_GET['status'])) ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-laptop-medical"></i></div>
                <div class="card-label"><?php echo __('Inventário'); ?></div>
            </a>
            <a href="equipamentos.php?status=Manutencao" class="menu-card <?php echo (isset($_GET['status']) && $_GET['status'] == 'Manutencao') ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-tools"></i></div>
                <div class="card-label"><?php echo __('Manutenção'); ?></div>
            </a>
            <a href="leiloes.php" class="menu-card <?php echo (strpos($current_page, 'leiloe') !== false) ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-gavel"></i></div>
                <div class="card-label"><?php echo __('Leilões'); ?></div>
            </a>
            <a href="licencas.php" class="menu-card <?php echo (strpos($current_page, 'licenca') !== false) ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-file-contract"></i></div>
                <div class="card-label"><?php echo __('Licenças'); ?></div>
            </a>
            <a href="ativos_doados.php" class="menu-card <?php echo (strpos($current_page, 'doado') !== false) ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-hand-holding-heart"></i></div>
                <div class="card-label"><?php echo __('Doações'); ?></div>
            </a>
            <a href="ativos_leiloados.php" class="menu-card <?php echo (strpos($current_page, 'leiloados') !== false) ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="card-label"><?php echo __('H. Leilões'); ?></div>
            </a>
            <a href="centro_de_custo.php" class="menu-card <?php echo (strpos($current_page, 'centro_de_custo') !== false) ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-wallet"></i></div>
                <div class="card-label"><?php echo __('C. Custos'); ?></div>
            </a>
            <a href="locais.php" class="menu-card <?php echo (strpos($current_page, 'locais') !== false) ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-map-marked-alt"></i></div>
                <div class="card-label"><?php echo __('Locais'); ?></div>
            </a>
            <a href="fornecedores.php" class="menu-card <?php echo (strpos($current_page, 'fornecedor') !== false) ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-handshake"></i></div>
                <div class="card-label"><?php echo __('Fornecedores'); ?></div>
            </a>
            <a href="usuarios.php" class="menu-card <?php echo (strpos($current_page, 'usuario') !== false) ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-user-shield"></i></div>
                <div class="card-label"><?php echo __('Usuários'); ?></div>
            </a>
            <?php endif; ?>

            <!-- SECTION: OPERACIONAL -->
            <a href="chamados.php" class="menu-card <?php echo (strpos($current_page, 'chamado') !== false) ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-ticket-alt"></i></div>
                <div class="card-label"><?php echo __('Chamados'); ?></div>
            </a>
            <?php if ($isAdminOrSuporte): ?>
            <a href="relatorios.php" class="menu-card <?php echo (strpos($current_page, 'relatorio') !== false) ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-file-invoice"></i></div>
                <div class="card-label"><?php echo __('Relatórios'); ?></div>
            </a>
            <?php endif; ?>

            <!-- SECTION: INTELIGÊNCIA -->
            <a href="agent.php" class="menu-card <?php echo ($current_page == 'agent.php') ? 'active' : ''; ?>">
                <div class="card-icon icon-ia"><i class="fas fa-brain"></i></div>
                <div class="card-label"><?php echo __('Assistente IA'); ?></div>
            </a>
            <?php if ($isAdminOrSuporte): ?>
            <a href="insights.php" class="menu-card <?php echo ($current_page == 'insights.php') ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-lightbulb"></i></div>
                <div class="card-label"><?php echo __('Insights'); ?></div>
            </a>
            <?php endif; ?>
            <a href="chat_interno.php" class="menu-card <?php echo ($current_page == 'chat_interno.php') ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-comments"></i></div>
                <div class="card-label"><?php echo __('Chat'); ?></div>
            </a>
            <a href="gerenciar_kb.php" class="menu-card <?php echo (strpos($current_page, 'kb') !== false) ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-book-open"></i></div>
                <div class="card-label"><?php echo __('Conhecimento'); ?></div>
            </a>
            <a href="suporte.php" class="menu-card <?php echo ($current_page == 'suporte.php') ? 'active' : ''; ?>">
                <div class="card-icon"><i class="fas fa-headset"></i></div>
                <div class="card-label"><?php echo __('Suporte'); ?></div>
            </a>
        </div>
    </div>

    <div class="mobile-menu-footer">
        <p>© <?php echo date('Y'); ?> DEGB Asset Management</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenu = document.getElementById('mobileQuickMenu');
    const closeBtn = document.getElementById('closeMobileMenu');
    const openBtn = document.getElementById('sidebarToggleTop');

    function toggleMenu() {
        if (window.innerWidth <= 768) {
            mobileMenu.classList.toggle('active');
            document.body.classList.toggle('mobile-menu-open');
        }
    }

    if (openBtn) {
        // Interceptar o clique original do hamburger no mobile
        openBtn.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                e.stopPropagation();
                toggleMenu();
            }
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', toggleMenu);
    }

    // Fechar ao clicar fora do grid
    mobileMenu.addEventListener('click', function(e) {
        if (e.target === mobileMenu) toggleMenu();
    });
});
</script>
