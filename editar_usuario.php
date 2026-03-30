<?php
/**
 * EDIÇÃO DE USUÁRIO: editar_usuario.php
 * Interface administrativa para modificar dados de usuários existentes.
 * Mantém restrições de edição para usuários comuns (apenas foto).
 */
include_once 'auth.php'; // Proteção de sessão
include_once 'conexao.php'; // Banco de Dados

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

// Verifica se o usuário tem permissão para editar (Admin, Suporte ou o próprio perfil)
$can_edit_all = ($_SESSION['nivelUsuario'] === 'Admin' || $_SESSION['nivelUsuario'] === 'Suporte');
?>
<!DOCTYPE html>
<html lang="<?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'pt-BR') ? 'pt-br' : 'en'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Editar Usuário'); ?> - Asset MGT</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=f3d9abe8d5aa7831c01bfaa2a1563712">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=41e93b37bc495fd67938799bb3a6adaf">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
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
                <div class="container-fluid">
                    <h3 class="text-dark mb-1"><?php echo __('Editar Usuário'); ?></h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="update_usuario.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
                                <input type="hidden" name="id_usuarios" value="<?php echo $user['id_usuarios']; ?>">

                                <!-- Row 1: Nome e Sobrenome -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="nome"><?php echo __('Nome'); ?></label>
                                            <input class="form-control" name="nome" id="nome" type="text" value="<?php echo htmlspecialchars($user['nome']); ?>" required="" <?php echo $can_edit_all ? '' : 'readonly'; ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="sobrenome"><?php echo __('Sobrenome'); ?></label>
                                            <input class="form-control" name="sobrenome" id="sobrenome" type="text" value="<?php echo htmlspecialchars($user['sobrenome']); ?>" required="" <?php echo $can_edit_all ? '' : 'readonly'; ?>>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 2: Login AD e E-mail -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="usuarioAD"><?php echo __('Nome de Usuário (Login)'); ?></label>
                                            <input class="form-control" name="usuarioAD" id="usuarioAD" type="text" value="<?php echo htmlspecialchars($user['usuarioAD']); ?>" required="" <?php echo $can_edit_all ? '' : 'readonly'; ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="email"><?php echo __('E-mail'); ?></label>
                                            <input class="form-control" name="email" id="email" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" required="" <?php echo $can_edit_all ? '' : 'readonly'; ?>>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 3: Função e Data de Nascimento -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="funcao"><?php echo __('Função / Cargo'); ?></label>
                                            <input class="form-control" name="funcao" id="funcao" type="text" value="<?php echo htmlspecialchars($user['funcao']); ?>" required="" <?php echo $can_edit_all ? '' : 'readonly'; ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="dataNascimento"><?php echo __('Data de Nascimento'); ?></label>
                                            <input class="form-control" name="dataNascimento" id="dataNascimento" type="date" value="<?php echo $user['dataNascimento']; ?>" required="" <?php echo $can_edit_all ? '' : 'readonly'; ?>>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 4: CPF e Matrícula -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold text-danger" for="cpf"><?php echo __('CPF (Somente Números)'); ?></label>
                                            <input class="form-control" name="cpf" id="cpf" type="text" value="<?php echo htmlspecialchars($user['cpf'] ?? ''); ?>" required="" maxlength="14" oninput="maskCPF(this)" <?php echo $can_edit_all ? '' : 'readonly'; ?>>
                                            <div id="cpf-error" class="text-danger small mt-1" style="display:none;"><?php echo __('CPF Inválido'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="matricula"><?php echo __('Matrícula'); ?></label>
                                            <input class="form-control" name="matricula" id="matricula" type="text" value="<?php echo htmlspecialchars($user['matricula']); ?>" readonly required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 5: Telefone, Setor e Centro de Custo -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="telefone"><?php echo __('Telefone / Contato'); ?></label>
                                            <input class="form-control" name="telefone" id="telefone" type="text" value="<?php echo htmlspecialchars($user['telefone']); ?>" <?php echo $can_edit_all ? '' : 'readonly'; ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="setor"><?php echo __('Setor'); ?></label>
                                            <input class="form-control" name="setor" id="setor" type="text" value="<?php echo htmlspecialchars($user['setor'] ?? ''); ?>" placeholder="<?php echo __('Ex: TI / RH'); ?>" <?php echo $can_edit_all ? '' : 'readonly'; ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="centroDeCusto"><?php echo __('Centro de Custo'); ?></label>
                                            <select class="form-control" name="centroDeCusto" id="centroDeCusto" <?php echo $can_edit_all ? '' : 'disabled'; ?>>
                                                <option value="Nenhum" <?php echo ($user['centroDeCusto'] == 'Nenhum') ? 'selected' : ''; ?>><?php echo __('Nenhum'); ?></option>
                                                <?php
                                                $sql_cc = "SELECT nomeSetor FROM centro_de_custo ORDER BY nomeSetor ASC";
                                                $res_cc = $conn->query($sql_cc);
                                                if ($res_cc && $res_cc->num_rows > 0) {
                                                    while ($row_cc = $res_cc->fetch_assoc()) {
                                                        $selected = ($user['centroDeCusto'] == $row_cc['nomeSetor']) ? 'selected' : '';
                                                        echo '<option value="' . $row_cc['nomeSetor'] . '" ' . $selected . '>' . __($row_cc['nomeSetor']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <?php if (!$can_edit_all): ?>
                                                <input type="hidden" name="centroDeCusto" value="<?php echo htmlspecialchars($user['centroDeCusto']); ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 6: Unidade, Tipo de Contrato e Perfil -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="unidade"><?php echo __('Unidade / Local'); ?></label>
                                            <select class="form-control" name="unidade" id="unidade" <?php echo $can_edit_all ? '' : 'disabled'; ?>>
                                                <?php
                                                $sql_un = "SELECT unidade FROM unidade ORDER BY unidade ASC";
                                                $res_un = $conn->query($sql_un);
                                                while ($row_un = $res_un->fetch_assoc()) {
                                                    $selected = ($user['unidade'] == $row_un['unidade']) ? 'selected' : '';
                                                    echo '<option value="' . $row_un['unidade'] . '" ' . $selected . '>' . $row_un['unidade'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <?php if (!$can_edit_all): ?>
                                                <input type="hidden" name="unidade" value="<?php echo htmlspecialchars($user['unidade']); ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="tipoContrato"><?php echo __('Tipo de Contrato'); ?></label>
                                            <select class="form-control" name="tipoContrato" id="tipoContrato" <?php echo $can_edit_all ? '' : 'disabled'; ?>>
                                                <option value="CLT" <?php echo (($user['tipoContrato'] ?? '') == 'CLT') ? 'selected' : ''; ?>><?php echo __('CLT'); ?></option>
                                                <option value="PJ" <?php echo (($user['tipoContrato'] ?? '') == 'PJ') ? 'selected' : ''; ?>><?php echo __('PJ'); ?></option>
                                                <option value="Estágio" <?php echo (($user['tipoContrato'] ?? '') == 'Estágio') ? 'selected' : ''; ?>><?php echo __('Estágio'); ?></option>
                                                <option value="Terceirizado" <?php echo (($user['tipoContrato'] ?? '') == 'Terceirizado') ? 'selected' : ''; ?>><?php echo __('Terceirizado'); ?></option>
                                            </select>
                                            <?php if (!$can_edit_all): ?>
                                                <input type="hidden" name="tipoContrato" value="<?php echo htmlspecialchars($user['tipoContrato'] ?? ''); ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="nivelUsuario"><?php echo __('Perfil de Acesso'); ?></label>
                                            <select class="form-control" name="nivelUsuario" id="nivelUsuario" required="" <?php echo $can_edit_all ? '' : 'disabled'; ?>>
                                                <?php
                                                $nivelValue = '3';
                                                $currentNivel = trim($user['nivelUsuario'] ?? '');
                                                if ($currentNivel == 'Admin') $nivelValue = '1';
                                                elseif ($currentNivel == 'Suporte') $nivelValue = '2';
                                                ?>
                                                <option value="3" <?php echo ($nivelValue == '3') ? 'selected' : ''; ?>><?php echo __('Usuário'); ?></option>
                                                <option value="2" <?php echo ($nivelValue == '2') ? 'selected' : ''; ?>><?php echo __('Suporte'); ?></option>
                                                <option value="1" <?php echo ($nivelValue == '1') ? 'selected' : ''; ?>><?php echo __('Administrador'); ?></option>
                                            </select>
                                            <?php if (!$can_edit_all): ?>
                                                <input type="hidden" name="nivelUsuario" value="<?php echo $nivelValue; ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 7: Foto e Status -->
                                <div class="row align-items-center">
                                    <div class="col-md-2 text-center">
                                        <label class="text-gray-600 small font-weight-bold d-block"><?php echo __('Foto Atual'); ?></label>
                                        <img src="<?php echo !empty($user['foto_perfil']) ? htmlspecialchars($user['foto_perfil']) : '/assets/img/avatars/avatar1.jpeg'; ?>"
                                            class="rounded-circle img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                    </div>
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="foto_perfil"><?php echo __('Alterar Foto de Perfil'); ?></label>
                                            <input class="form-control-file" name="foto_perfil" id="foto_perfil" type="file" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="custom-control custom-switch" style="margin-top: 15px;">
                                            <input type="hidden" name="status" value="Inativo">
                                            <input type="checkbox" class="custom-control-input" id="statusSwitch" name="status" value="Ativo" <?php echo ($user['status'] == 'Ativo') ? 'checked' : ''; ?> <?php echo $can_edit_all ? '' : 'disabled'; ?>>
                                            <label class="custom-control-label font-weight-bold" for="statusSwitch"><?php echo __('Ativo'); ?></label>
                                            <?php if (!$can_edit_all): ?>
                                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($user['status']); ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 8: Action Buttons -->
                                <div class="row mt-5 mb-3">
                                    <div class="col-12 d-flex justify-content-end align-items-center" style="gap: 15px;">
                                        <button class="btn btn-outline-primary" type="button"
                                            style="border-radius: 10px; padding: 10px 25px; font-weight: 600; border: 2px solid #2c404a; color: #2c404a; background: transparent;"
                                            data-toggle="modal" data-target="#modalAlterarSenha">
                                            <i class="fas fa-lock mr-2"></i><?php echo __('Alterar senha...'); ?>
                                        </button>
                                        <a class="btn btn-secondary" href="usuarios.php" 
                                            style="border-radius: 10px; padding: 10px 25px; border: none; background: #2c404a; font-weight: 600;">
                                            <?php echo __('Voltar'); ?>
                                        </a>
                                        <button class="btn btn-success active pulse animated" type="submit" 
                                            style="background: #2c404a; border-radius: 10px; padding: 10px 25px; border: none; font-weight: 600;">
                                            <i class="fas fa-save mr-2"></i><?php echo __('Salvar Alterações'); ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>

    <!-- Modal Alterar Senha -->
    <div class="modal fade" id="modalAlterarSenha" tabindex="-1" role="dialog" aria-labelledby="modalAlterarSenhaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 p-4" style="background: #2c404a; color: white;">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 mr-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-key text-primary"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-weight-bold mb-0" id="modalAlterarSenhaLabel"><?php echo __('Alterar Senha'); ?></h5>
                            <small class="text-white-50"><?php echo __('Mantenha sua conta segura com uma senha forte.'); ?></small>
                        </div>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <form id="formAlterarSenha">
                        <input type="hidden" name="id_usuario_senha" value="<?php echo $user['id_usuarios']; ?>">
                        <div class="form-group">
                            <label class="text-gray-600 small font-weight-bold" for="senha_atual"><?php echo __('Senha Atual'); ?></label>
                            <input type="password" class="form-control" id="senha_atual" name="senha_atual" required style="border-radius: 8px;">
                        </div>
                        <div class="alert alert-info border-0 small mb-3" style="border-radius: 10px; background: rgba(78, 115, 223, 0.1); color: #4e73df;">
                            <i class="fas fa-info-circle mr-2"></i><?php echo __('A nova senha deve ter pelo menos 4 caracteres.'); ?>
                        </div>
                        <div class="form-group mb-3">
                            <label class="text-gray-600 small font-weight-bold" for="nova_senha"><?php echo __('Nova Senha'); ?></label>
                            <input type="password" class="form-control" id="nova_senha" name="nova_senha" required style="border-radius: 12px; height: 45px;">
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-gray-600 small font-weight-bold" for="confirmar_senha"><?php echo __('Confirmar Nova Senha'); ?></label>
                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required style="border-radius: 12px; height: 45px;">
                        </div>
                        <div id="password-error" class="alert alert-danger d-none mt-3" style="border-radius: 10px;"></div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-3 bg-white d-flex justify-content-between">
                    <button type="button" class="btn btn-link text-muted font-weight-bold" data-dismiss="modal">
                        <?php echo __('Cancelar'); ?>
                    </button>
                    <button type="button" class="btn btn-primary px-4 font-weight-bold shadow-sm" id="btnSalvarSenha" style="border-radius: 12px; background: #2c404a; border: none;">
                        <i class="fas fa-check mr-2"></i><?php echo __('Atualizar Senha'); ?>
                    </button>
                </div>
            </div>
        </div>
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
            if (cpf.length != 11 ||
                cpf == "00000000000" || cpf == "11111111111" || cpf == "22222222222" ||
                cpf == "33333333333" || cpf == "44444444444" || cpf == "55555555555" ||
                cpf == "66666666666" || cpf == "77777777777" || cpf == "88888888888" ||
                cpf == "99999999999") {
                showCPFError(true);
                return false;
            }
            add = 0;
            for (i = 0; i < 9; i++) add += parseInt(cpf.charAt(i)) * (10 - i);
            rev = 11 - (add % 11);
            if (rev == 10 || rev == 11) rev = 0;
            if (rev != parseInt(cpf.charAt(9))) {
                showCPFError(true);
                return false;
            }
            add = 0;
            for (i = 0; i < 10; i++) add += parseInt(cpf.charAt(i)) * (11 - i);
            rev = 11 - (add % 11);
            if (rev == 10 || rev == 11) rev = 0;
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
                inputEl.setCustomValidity('<?php echo __('CPF Inválido'); ?>');
            } else {
                errorEl.style.display = 'none';
                inputEl.setCustomValidity('');
            }
        }

        function validateForm() {
            const cpfEl = document.getElementById('cpf');
            if (!validateCPF(cpfEl.value)) {
                alert('<?php echo __('Por favor, insira um CPF válido.'); ?>');
                return false;
            }
            return true;
        }

        $(document).ready(function() {
            $('#btnSalvarSenha').on('click', function() {
                const form = $('#formAlterarSenha');
                const passwordError = $('#password-error');
                const btn = $(this);
                
                const novaSenha = $('#nova_senha').val();
                const confirmarSenha = $('#confirmar_senha').val();

                if (novaSenha !== confirmarSenha) {
                    passwordError.text('<?php echo __('As novas senhas não coincidem.'); ?>').removeClass('d-none');
                    return;
                }

                if (novaSenha.length < 4) {
                    passwordError.text('<?php echo __('A nova senha deve ter pelo menos 4 caracteres.'); ?>').removeClass('d-none');
                    return;
                }

                passwordError.addClass('d-none');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i><?php echo __('Processando...'); ?>');

                $.ajax({
                    url: 'ajax_alterar_senha.php',
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            $('#modalAlterarSenha').modal('hide');
                            form[0].reset();
                        } else {
                            passwordError.text(response.message).removeClass('d-none');
                        }
                    },
                    error: function() {
                        passwordError.text('<?php echo __('Ocorreu um erro ao processar a solicitação.'); ?>').removeClass('d-none');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-check mr-2"></i><?php echo __('Atualizar Senha'); ?>');
                    }
                });
            });

            $('#modalAlterarSenha').on('hidden.bs.modal', function () {
                $('#formAlterarSenha')[0].reset();
                $('#password-error').addClass('d-none');
            });
        });
    </script>
</body>

</html>