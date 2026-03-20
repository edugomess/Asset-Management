<?php
/**
 * CADASTRO DE USUÁRIOS: cadastro_de_usuario.php
 * Interface administrativa para criação de novos acessos ao sistema Asset MGT.
 */
include_once 'auth.php'; // Proteção de sessão
include_once 'conexao.php'; // Banco de Dados

// LÓGICA DE MATRÍCULA: Sugere o próximo ID disponível como matrícula provisória
$sql_max = "SELECT MAX(id_usuarios) as max_id FROM usuarios";
$res_max = $conn->query($sql_max);
$next_id = 1;
if ($res_max && $row_max = $res_max->fetch_assoc()) {
    $next_id = $row_max['max_id'] + 1;
}
?>
<!DOCTYPE html>
<html lang="<?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'pt-BR') ? 'pt-br' : 'en'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Cadastro de Usuário'); ?> - Asset MGT</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=3532322f32770367812050c1dddc256c">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=f3d9abe8d5aa7831c01bfaa2a1563712">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=41e93b37bc495fd67938799bb3a6adaf">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
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
                    <h3 class="text-dark mb-1"><?php echo __('Cadastro de Usuário'); ?></h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="inserir_usuario.php" method="post" enctype="multipart/form-data">
                                <!-- Row 1: Nome e Sobrenome -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="nome"><?php echo __('Nome'); ?></label>
                                            <input class="form-control" name="nome" id="nome" type="text" placeholder="<?php echo __('Ex: João'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="sobrenome"><?php echo __('Sobrenome'); ?></label>
                                            <input class="form-control" name="sobrenome" id="sobrenome" type="text" placeholder="<?php echo __('Ex: da Silva'); ?>" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 2: Login AD e E-mail -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="usuarioAD"><?php echo __('Nome de Usuário (Login)'); ?></label>
                                            <input class="form-control" name="usuarioAD" id="usuarioAD" type="text" placeholder="<?php echo __('Ex: joao.silva'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="email"><?php echo __('E-mail'); ?></label>
                                            <input class="form-control" name="email" id="email" type="email" placeholder="<?php echo __('exemplo@empresa.com'); ?>" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 3: Função e Data de Nascimento -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="funcao"><?php echo __('Função / Cargo'); ?></label>
                                            <input class="form-control" name="funcao" id="funcao" type="text" placeholder="<?php echo __('Ex: Analista de Sistemas'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="dataNascimento"><?php echo __('Data de Nascimento'); ?></label>
                                            <input class="form-control" name="dataNascimento" id="dataNascimento" type="date" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 4: Senha e Confirmar Senha -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="senha"><?php echo __('Senha'); ?></label>
                                            <input class="form-control" name="senha" id="senha" type="password" placeholder="<?php echo __('Digite a senha'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="confirmarSenha"><?php echo __('Confirmar Senha'); ?></label>
                                            <input class="form-control" name="confirmarSenha" id="confirmarSenha" type="password" placeholder="<?php echo __('Confirme a senha'); ?>" required="">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 5: Matrícula e Telefone -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="matricula"><?php echo __('Matrícula'); ?></label>
                                            <input class="form-control" name="matricula" id="matricula" type="text" value="<?php echo $next_id; ?>" required="" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="telefone"><?php echo __('Telefone / Contato'); ?></label>
                                            <input class="form-control" name="telefone" id="telefone" type="text" placeholder="<?php echo __('(00) 00000-0000'); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 6: Centro de Custo e Unidade -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="centroDeCusto"><?php echo __('Centro de Custo'); ?></label>
                                            <select class="form-control" name="centroDeCusto" id="centroDeCusto">
                                                <option value="Nenhum"><?php echo __('Nenhum'); ?></option>
                                                <?php
                                                $sql_cc = "SELECT nomeSetor FROM centro_de_custo ORDER BY nomeSetor ASC";
                                                $res_cc = $conn->query($sql_cc);
                                                if ($res_cc && $res_cc->num_rows > 0) {
                                                    while ($row_cc = $res_cc->fetch_assoc()) {
                                                        echo '<option value="' . $row_cc['nomeSetor'] . '">' . __($row_cc['nomeSetor']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="unidade"><?php echo __('Unidade / Local'); ?></label>
                                            <input class="form-control" name="unidade" id="unidade" type="text" placeholder="<?php echo __('Ex: Matriz'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="tipoContrato"><?php echo __('Tipo de Contrato'); ?></label>
                                            <select class="form-control" name="tipoContrato" id="tipoContrato">
                                                <option value="CLT"><?php echo __('CLT'); ?></option>
                                                <option value="PJ"><?php echo __('PJ'); ?></option>
                                                <option value="Estágio"><?php echo __('Estágio'); ?></option>
                                                <option value="Terceirizado"><?php echo __('Terceirizado'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 7: Nível de Acesso, Foto e Status -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="nivelUsuario"><?php echo __('Perfil de Acesso'); ?></label>
                                            <select class="form-control" name="nivelUsuario" id="nivelUsuario" required="">
                                                <option value="3"><?php echo __('Usuário'); ?></option>
                                                <option value="2"><?php echo __('Suporte'); ?></option>
                                                <option value="1"><?php echo __('Administrador'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="foto_perfil"><?php echo __('Foto de Perfil'); ?></label>
                                            <input class="form-control-file" name="foto_perfil" id="foto_perfil" type="file" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="custom-control custom-switch" style="margin-top: 32px;">
                                            <input type="hidden" name="status" value="Inativo">
                                            <input type="checkbox" class="custom-control-input" id="statusSwitch" name="status" value="Ativo" checked>
                                            <label class="custom-control-label" for="statusSwitch"><?php echo __('Ativo'); ?></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-4 offset-md-4">
                                        <button class="btn btn-success btn-block active text-white pulse animated btn-user" type="submit" style="background: rgb(44,64,74);border-radius: 10px;border-width: 0px;height: 50px;"><?php echo __('Cadastrar'); ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.js"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script src="/assets/js/global_search.js"></script>
</body>

</html>