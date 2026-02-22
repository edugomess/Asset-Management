<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<hr class="sidebar-divider my-0">
<ul class="navbar-nav text-light" id="accordionSidebar">
    <!-- Principal -->
    <div class="sidebar-heading">Menu Principal</div>
    <li class="nav-item">
        <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="/index.php">
            <i class="fas fa-chart-pie"></i><span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo ($current_page == 'inicio.php') ? 'active' : ''; ?>" href="/inicio.php">
            <i class="fas fa-tachometer-alt"></i><span>Visão Geral</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <!-- Gestão -->
    <div class="sidebar-heading">Gestão de Ativos</div>
    <li class="nav-item">
        <a class="nav-link <?php echo (strpos($current_page, 'equipamento') !== false || strpos($current_page, 'ativo') !== false) && !isset($_GET['status']) ? 'active' : ''; ?>"
            href="/equipamentos.php">
            <i class="fas fa-laptop-medical"></i><span>Inventário de Ativos</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo isset($_GET['status']) && $_GET['status'] == 'Manutencao' ? 'active' : ''; ?>"
            href="/equipamentos.php?status=Manutencao">
            <i class="fas fa-tools"></i><span>Ativos em Manutenção</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (strpos($current_page, 'licenca') !== false) ? 'active' : ''; ?>"
            href="/licencas.php">
            <i class="fas fa-file-contract"></i><span>Licenças & Software</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (strpos($current_page, 'centro_de_custo') !== false) ? 'active' : ''; ?>"
            href="/centro_de_custo.php">
            <i class="fas fa-wallet"></i><span>Centros de Custo</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (strpos($current_page, 'fornecedor') !== false) ? 'active' : ''; ?>"
            href="/fornecedores.php">
            <i class="fas fa-handshake"></i><span>Fornecedores</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (strpos($current_page, 'usuario') !== false) ? 'active' : ''; ?>"
            href="/usuarios.php">
            <i class="fas fa-user-shield"></i><span>Controle de Usuários</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <!-- Operacional -->
    <div class="sidebar-heading">Operacional</div>
    <li class="nav-item">
        <a class="nav-link <?php echo (strpos($current_page, 'chamado') !== false) ? 'active' : ''; ?>"
            href="/chamados.php">
            <i class="fas fa-ticket-alt"></i><span>Central de Chamados</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (strpos($current_page, 'relatorio') !== false) ? 'active' : ''; ?>"
            href="/relatorios.php">
            <i class="fas fa-file-invoice"></i><span>Relatórios Internos</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <!-- Outros -->
    <div class="sidebar-heading">Inteligência & Ajuda</div>
    <li class="nav-item">
        <a class="nav-link <?php echo ($current_page == 'agent.php') ? 'active' : ''; ?>" href="/agent.php">
            <i class="fas fa-brain"></i><span>Assistente IA</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo ($current_page == 'suporte.php') ? 'active' : ''; ?>" href="/suporte.php">
            <i class="fas fa-headset"></i><span>Suporte Técnico</span>
        </a>
    </li>
</ul>
<div class="text-center d-none d-md-inline">
    <button class="btn rounded-circle border-0" id="sidebarToggle" type="button"></button>
</div>