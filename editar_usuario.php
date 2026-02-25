<?php
include_once 'auth.php';
include_once 'conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $sql = "SELECT * FROM usuarios WHERE id_usuarios = $id";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        header("Location: usuarios.php");
        exit();
    }
} else {
    header("Location: usuarios.php");
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Editar Usuário - Asset Mgt</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <?php include 'sidebar_style.php'; ?>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion p-0">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include 'sidebar_brand.php'; ?>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <nav class="navbar navbar-light navbar-expand bg-white shadow mb-4 topbar static-top"
                    style="margin: 5px 23px;">
                    <div class="container-fluid">
                        <button class="btn btn-link d-md-none rounded-circle mr-3" id="sidebarToggleTop-1"
                            type="button"><i class="fas fa-bars"></i></button>
                        <ul class="navbar-nav flex-nowrap ml-auto">
                            <li class="nav-item no-arrow mx-1 d-flex align-items-center">
                            </li>
                            <div class="d-none d-sm-block topbar-divider"></div>
                            <li class="nav-item dropdown no-arrow">
                                <div class="nav-item dropdown no-arrow">
                                    <a class="dropdown-toggle nav-link" aria-expanded="false" data-toggle="dropdown"
                                        href="#">
                                        <span
                                            class="d-none d-lg-inline mr-2 text-gray-600 small"><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span>
                                        <img class="border rounded-circle img-profile"
                                            src="<?php echo !empty($_SESSION['foto_perfil']) ? htmlspecialchars($_SESSION['foto_perfil']) : '/assets/img/avatars/avatar1.jpeg'; ?>">
                                    </a>
                                    <div class="dropdown-menu shadow dropdown-menu-right animated--grow-in">
                                        <a class="dropdown-item" href="profile.php"><i
                                                class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Perfil</a>
                                        <a class="dropdown-item" href="configuracoes.php"><i
                                                class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>Configuraçoes</a>
                                        <a class="dropdown-item" href="equipamentos.php?status=Manutencao"><i
                                                class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>Ativos em
                                            Manutenção</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="login.php"><i
                                                class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>&nbsp;Sair</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="container-fluid">
                    <h3 class="text-dark mb-4">Editar Usuário</h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="update_usuario.php" method="post" enctype="multipart/form-data"
                                onsubmit="return validateCPF()">
                                <input type="hidden" name="id_usuarios" value="<?php echo $user['id_usuarios']; ?>">

                                <!-- Row 1: Nome, Sobrenome -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label>Nome</label>
                                            <input class="form-control" name="nome" type="text"
                                                value="<?php echo htmlspecialchars($user['nome']); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label>Sobrenome</label>
                                            <input class="form-control" name="sobrenome" type="text"
                                                value="<?php echo htmlspecialchars($user['sobrenome']); ?>" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 2: Usuário AD, Função -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label>Usuário AD</label>
                                            <input class="form-control" name="usuarioAD" type="text"
                                                value="<?php echo htmlspecialchars($user['usuarioAD']); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label>Função</label>
                                            <input class="form-control" name="funcao" type="text"
                                                value="<?php echo htmlspecialchars($user['funcao']); ?>" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 3: Data Nasc, Email -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label>Data de Nascimento</label>
                                            <input class="form-control" name="dataNascimento" type="date"
                                                value="<?php echo $user['dataNascimento']; ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input class="form-control" name="email" type="email"
                                                value="<?php echo htmlspecialchars($user['email']); ?>" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 4: CPF, Centro de Custo -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-danger">CPF (Somente Números)</label>
                                            <input class="form-control" name="cpf" id="cpf" type="text"
                                                value="<?php echo htmlspecialchars($user['cpf'] ?? ''); ?>" required=""
                                                maxlength="14" oninput="maskCPF(this)">
                                            <div id="cpf-error" class="text-danger small mt-1" style="display:none;">CPF
                                                Inválido</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label>Centro de Custo</label>
                                            <select class="form-control" name="centroDeCusto">
                                                <option value="Nenhum" <?php echo ($user['centroDeCusto'] == 'Nenhum') ? 'selected' : ''; ?>>Nenhum</option>
                                                <?php
                                                $sql_cc = "SELECT nomeSetor FROM centro_de_custo ORDER BY nomeSetor ASC";
                                                $res_cc = $conn->query($sql_cc);
                                                if ($res_cc && $res_cc->num_rows > 0) {
                                                    while ($row_cc = $res_cc->fetch_assoc()) {
                                                        $selected = ($user['centroDeCusto'] == $row_cc['nomeSetor']) ? 'selected' : '';
                                                        echo '<option value="' . $row_cc['nomeSetor'] . '" ' . $selected . '>' . $row_cc['nomeSetor'] . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 5: Matrícula, Telefone -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label>Matrícula</label>
                                            <input class="form-control" name="matricula" type="text"
                                                value="<?php echo htmlspecialchars($user['matricula']); ?>" readonly
                                                required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label>Telefone</label>
                                            <input class="form-control" name="telefone" type="text"
                                                value="<?php echo htmlspecialchars($user['telefone']); ?>"
                                                inputmode="tel" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 6: Tipo de Contrato, Tipo de Usuário -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label>Tipo de Contrato</label>
                                            <select class="form-control" name="tipoContrato" required="">
                                                <option value="CLT" <?php echo ($user['tipoContrato'] == 'CLT') ? 'selected' : ''; ?>>CLT</option>
                                                <option value="PJ" <?php echo ($user['tipoContrato'] == 'PJ') ? 'selected' : ''; ?>>PJ</option>
                                                <option value="Cooperativa" <?php echo ($user['tipoContrato'] == 'Cooperativa') ? 'selected' : ''; ?>>
                                                    Cooperativa</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label>Tipo de Usuário</label>
                                            <select class="form-control" name="nivelUsuario" required="">
                                                <option value="1" <?php echo ($user['nivelUsuario'] == '1') ? 'selected' : ''; ?>>Administrador</option>
                                                <option value="2" <?php echo ($user['nivelUsuario'] == '2') ? 'selected' : ''; ?>>Suporte</option>
                                                <option value="3" <?php echo ($user['nivelUsuario'] == '3') ? 'selected' : ''; ?>>Usuário</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 7: Unidade, Status -->
                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label>Unidade</label>
                                            <select class="form-control" name="unidade">
                                                <?php
                                                $sql_un = "SELECT unidade FROM unidade ORDER BY unidade ASC";
                                                $res_un = $conn->query($sql_un);
                                                while ($row_un = $res_un->fetch_assoc()) {
                                                    $selected = ($user['unidade'] == $row_un['unidade']) ? 'selected' : '';
                                                    echo '<option value="' . $row_un['unidade'] . '" ' . $selected . '>' . $row_un['unidade'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select class="form-control" name="status">
                                                <option value="Ativo" <?php echo ($user['status'] == 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                                                <option value="Inativo" <?php echo ($user['status'] == 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 8: Foto de Perfil -->
                                <div class="form-row mt-4">
                                    <div class="col-xl-10 offset-xl-1 d-flex align-items-center">
                                        <div class="mr-4">
                                            <label>Foto Atual</label><br>
                                            <img src="<?php echo !empty($user['foto_perfil']) ? htmlspecialchars($user['foto_perfil']) : '/assets/img/avatars/avatar1.jpeg'; ?>"
                                                class="img-thumbnail" style="max-width: 100px;">
                                        </div>
                                        <div class="flex-grow-1">
                                            <label>Alterar Foto de Perfil</label>
                                            <input type="file" class="form-control-file" name="foto_perfil"
                                                accept="image/*">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 9: Submit Button -->
                                <div class="form-row mt-5 mb-4">
                                    <div class="col-xl-4 offset-xl-4">
                                        <button class="btn btn-primary btn-block text-white shadow-sm" type="submit"
                                            style="background: #2c404a; border-radius: 10px; height: 50px; font-weight: 600;">
                                            <i class="fas fa-save mr-2"></i>Salvar Alterações
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="sticky-footer">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>DEGB&nbsp;Copyright © 2015-2024</span>
                    </div>
                </div>
            </footer>
        </div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js"></script>
    <script src="/assets/js/theme.js"></script>
    <script src="/assets/js/global_search.js"></script>

    <script>
        function maskCPF(i) {
            var v = i.value;
            if (isNaN(v[v.length - 1])) {
                i.value = v.substring(0, v.length - 1);
                return;
            }
            i.setAttribute("maxlength", "14");
            if (v.length == 3 || v.length == 7) i.value += ".";
            if (v.length == 11) i.value += "-";

            if (v.length == 14) {
                validateCPF(v);
            } else {
                document.getElementById('cpf-error').style.display = 'none';
                i.setCustomValidity('');
            }
        }

        function validateCPF(cpf) {
            cpf = cpf.replace(/[^\d]+/g, '');
            if (cpf == '') return false;
            // Elimina CPFs invalidos conhecidos
            if (cpf.length != 11 ||
                cpf == "00000000000" ||
                cpf == "11111111111" ||
                cpf == "22222222222" ||
                cpf == "33333333333" ||
                cpf == "44444444444" ||
                cpf == "55555555555" ||
                cpf == "66666666666" ||
                cpf == "77777777777" ||
                cpf == "88888888888" ||
                cpf == "99999999999") {
                showCPFError(true);
                return false;
            }
            // Valida 1o digito	
            add = 0;
            for (i = 0; i < 9; i++)
                add += parseInt(cpf.charAt(i)) * (10 - i);
            rev = 11 - (add % 11);
            if (rev == 10 || rev == 11)
                rev = 0;
            if (rev != parseInt(cpf.charAt(9))) {
                showCPFError(true);
                return false;
            }
            // Valida 2o digito	
            add = 0;
            for (i = 0; i < 10; i++)
                add += parseInt(cpf.charAt(i)) * (11 - i);
            rev = 11 - (add % 11);
            if (rev == 10 || rev == 11)
                rev = 0;
            if (rev != parseInt(cpf.charAt(10))) {
                showCPFError(true);
                return false;
            }
            showCPFError(false);
            return true;
        }

        function showCPFError(hasError) {
            const errorEl = document.getElementById('cpf-error');
            const inputEl = document.getElementById('cpf');
            if (hasError) {
                errorEl.style.display = 'block';
                inputEl.setCustomValidity('CPF Inválido');
            } else {
                errorEl.style.display = 'none';
                inputEl.setCustomValidity('');
            }
        }

        document.querySelector('form').addEventListener('submit', function (e) {
            const cpfEl = document.getElementById('cpf');
            const cpfValue = cpfEl.value;
            if (!validateCPF(cpfValue)) {
                e.preventDefault();
                alert('Por favor, insira um CPF válido.');
            }
        });
    </script>
</body>

</html>