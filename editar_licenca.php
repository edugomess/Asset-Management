<?php
include 'auth.php';
include 'conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $sql = "SELECT * FROM licencas WHERE id_licenca = $id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        die("Licença não encontrada.");
    }

    // Fetch Cost Centers
    $sql_cc = "SELECT id_centro_de_custo, nomeSetor FROM centro_de_custo ORDER BY nomeSetor ASC";
    $result_cc = mysqli_query($conn, $sql_cc);
} else {
    die("ID inválido.");
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Editar Licença - Asset Mgt</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=19488c1c6619bc9bd5c02de5f7ffbfd4">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=193916adb9d7af47fe74d9a2270caac3">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <a class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#">
                    <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-boxes"></i></div>
                    <div class="sidebar-brand-text mx-3"><span>ASSET MGT</span></div>
                </a>
                <hr class="sidebar-divider my-0">
                <ul class="navbar-nav text-light" id="accordionSidebar">
                    <li class="nav-item"><a class="nav-link" href="/index.php"><i
                                class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/inicio.php"><i class="fas fa-home"></i><span>
                                Início</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/usuarios.php"><i class="fas fa-user-alt"></i><span>
                                Usuários</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/centro_de_custo.php"><i
                                class="fas fa-file-invoice-dollar"></i><span> Centro de Custo</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/fornecedores.php"><i
                                class="fas fa-hands-helping"></i><span> Fornecedores</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/equipamentos.php"><i class="fas fa-boxes"></i><span>
                                Ativos</span></a></li>
                    <li class="nav-item"><a class="nav-link active" href="/licencas.php"><i
                                class="fas fa-key"></i><span> Licenças</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/relatorios.php"><i class="fas fa-scroll"></i><span>
                                Relatórios</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/chamados.php"><i class="fas fa-headset"></i><span>
                                Chamados</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/suporte.php"><i class="fas fa-user-cog"></i><span>
                                Suporte</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="/agent.php"><i class="fas fa-robot"></i><span> IA
                                Agent</span></a></li>
                </ul>
                <div class="text-center d-none d-md-inline"><button class="btn rounded-circle border-0"
                        id="sidebarToggle" type="button"></button></div>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper" style="min-height: 100vh;">
            <div id="content" style="flex: 1 0 auto;">
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top"
                    style="margin: 23px;">
                    <div class="container-fluid"><button class="btn btn-link d-md-none rounded-circle mr-3"
                            id="sidebarToggleTop-1" type="button"><i class="fas fa-bars"></i></button>
                        <ul class="navbar-nav flex-nowrap ml-auto">
                            <li class="nav-item dropdown no-arrow mx-1">
                                <div class="nav-item dropdown no-arrow"><a class="dropdown-toggle nav-link"
                                        aria-expanded="false" data-toggle="dropdown" href="#"><span
                                            class="d-none d-lg-inline mr-2 text-gray-600 small">
                                            <?php echo htmlspecialchars($_SESSION['nome_usuario']); ?>
                                        </span><img class="border rounded-circle img-profile"
                                            src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/Captura%20de%20Tela%202021-08-04%20às%2012.25.13.png?h=fcfb924f0ac1ab5f595f029bf526e62d'; ?>"></a>
                                    <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in"><a
                                            class="dropdown-item" href="profile.php"><i
                                                class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a><a
                                            class="dropdown-item" href="configuracoes.php"><i
                                                class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a>
                                        <div class="dropdown-divider"></div><a class="dropdown-item" href="login.php"><i
                                                class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="container-fluid">
                    <h3 class="text-dark mb-1">Editar Licença</h3>
                </div>
                <form action="update_licenca.php" method="post">
                    <input type="hidden" name="id_licenca" value="<?php echo $row['id_licenca']; ?>">
                    <div class="form-row">
                        <div class="col-sm-6 col-xl-4 offset-xl-1">
                            <div class="form-group">
                                <label>Software / Aplicação</label>
                                <input class="form-control" name="software" type="text"
                                    value="<?php echo htmlspecialchars($row['software']); ?>" required="">
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-4 offset-xl-1">
                            <div class="form-group">
                                <label>Fabricante</label>
                                <input class="form-control" name="fabricante" type="text"
                                    value="<?php echo htmlspecialchars($row['fabricante']); ?>" required="">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-sm-6 col-xl-4 offset-xl-1">
                            <div class="form-group">
                                <label>Tipo de Licença</label>
                                <select class="form-control" name="tipo">
                                    <option value="Assinatura" <?php echo $row['tipo'] == 'Assinatura' ? 'selected' : ''; ?>>Assinatura</option>
                                    <option value="Vitalícia" <?php echo $row['tipo'] == 'Vitalícia' ? 'selected' : ''; ?>>Vitalícia</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-4 offset-xl-1">
                            <div class="form-group">
                                <label>Chave de Licença</label>
                                <input class="form-control" name="chave" type="text"
                                    value="<?php echo htmlspecialchars($row['chave']); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-sm-4 col-xl-2 offset-xl-1">
                            <div class="form-group">
                                <label>Quantidade Seats</label>
                                <input class="form-control" name="quantidade_total" type="number"
                                    value="<?php echo $row['quantidade_total']; ?>" min="1">
                            </div>
                        </div>
                        <div class="col-sm-4 col-xl-2">
                            <div class="form-group">
                                <label>Valor Unitário</label>
                                <input class="form-control" name="valor_unitario" type="number" step="0.01"
                                    value="<?php echo $row['valor_unitario']; ?>">
                            </div>
                        </div>
                        <div class="col-xl-2">
                            <label>Centro de Custo</label>
                            <select class="form-control" name="id_centro_custo">
                                <option value="">Nenhum</option>
                                <?php
                                mysqli_data_seek($result_cc, 0);
                                while ($row_cc = mysqli_fetch_assoc($result_cc)) {
                                    $selected = ($row_cc['id_centro_de_custo'] == $row['id_centro_custo']) ? 'selected' : '';
                                    echo "<option value='" . $row_cc['id_centro_de_custo'] . "' $selected>" . htmlspecialchars($row_cc['nomeSetor']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-xl-2">
                            <label>Data de Aquisição</label>
                            <input class="form-control" name="data_aquisicao" type="date"
                                value="<?php echo $row['data_aquisicao']; ?>">
                        </div>
                        <div class="col-xl-2">
                            <label>Data de Expiração</label>
                            <input class="form-control" name="data_expiracao" type="date"
                                value="<?php echo $row['data_expiracao']; ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-sm-4 col-xl-2 offset-xl-1">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" name="status">
                                    <option value="Ativa" <?php echo $row['status'] == 'Ativa' ? 'selected' : ''; ?>>Ativa
                                    </option>
                                    <option value="Expirada" <?php echo $row['status'] == 'Expirada' ? 'selected' : ''; ?>>Expirada</option>
                                    <option value="Cancelada" <?php echo $row['status'] == 'Cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-xl-4 offset-xl-4">
                            <button class="btn btn-success btn-block active text-white pulse animated btn-user"
                                type="submit"
                                style="background: rgb(44,64,74);border-radius: 10px;padding: 30px, 30px;border-width: 0px;height: 50px;margin-top: 50px;">Atualizar
                                Licença</button>
                        </div>
                    </div>
                </form>
            </div>
            <footer class="bg-white sticky-footer" style="padding: 10px 0; flex-shrink: 0;">
                <section class="text-center footer">
                    <p style="margin-bottom: 0px;font-size: 15px;">DEGB&nbsp;Copyright © 2015-2024<br></p>
                </section>
            </footer>
        </div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.js"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
</body>

</html>