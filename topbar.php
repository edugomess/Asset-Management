<?php
$userName = htmlspecialchars($_SESSION['nome_usuario']);
$fotoPerfil = !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/avatar5.jpeg';
$isAdminOrSuporte = $_SESSION['nivelUsuario'] !== 'Usuário';
?>
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
            <li class="nav-item dropdown no-arrow">
                <a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown" href="#">
                    <span class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo $userName; ?></span>
                    <img class="border rounded-circle img-profile" src="<?php echo $fotoPerfil; ?>">
                </a>
                <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in">
                    <a class="dropdown-item" href="profile.php"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i><?php echo __('Perfil'); ?></a>
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
