<?php
/**
 * ABERTURA DE CHAMADOS: cadastro_de_chamados.php
 * Interface para usuários e técnicos registrarem novos tickets de suporte.
 * Inclui campos para título, categoria, solicitante, prioridade e anexos.
 */
include 'auth.php'; // Proteção de sessão
?>
<!DOCTYPE html>
<html style="margin: 0px, 0px, 0px;margin-bottom: 0px;margin-top: 0px;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Novo Chamado'); ?></title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=3265483e434712d72c41db9eebc4c8bb">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=d6a29779d310462e7fcdde7b9a80e0db">
    <link rel="stylesheet" href="/assets/css/Nunito.css?h=5f41e73f827c7b56616237a1da13b6e2">
    <link rel="stylesheet" href="/assets/css/Raleway.css?h=19488c1c6619bc9bd5c02de5f7ffbfd4">
    <link rel="stylesheet" href="/assets/css/Roboto.css?h=193916adb9d7af47fe74d9a2270caac3">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="/assets/fonts/fontawesome5-overrides.min.css?h=a0e894d2f295b40fda5171460781b200">
    <link rel="stylesheet" href="/assets/css/Footer-Dark.css?h=cabc25193678a4e8700df5b6f6e02b7c">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="/assets/css/Simple-footer-by-krissy.css?h=73316da5ae5ad6b51632cd2e5413f263">
    <?php include 'sidebar_style.php'; ?>
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0"
            style="background: rgb(44,64,74);">
            <div class="container-fluid d-flex flex-column p-0">
                <?php include 'sidebar_brand.php'; ?>
                <?php include 'sidebar_menu.php'; ?>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <?php include 'topbar.php'; ?>
                <div class="container-fluid">
                    <h3 class="text-dark mb-1"><?php echo __('Novo Chamado'); ?></h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="inserir_chamado.php" method="post" id="form-novo-chamado"
                                enctype="multipart/form-data">

                                <div class="form-row">
                                    <div class="col-sm-12 col-xl-6 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Assunto / Título do Chamado'); ?></label>
                                            <input class="form-control" name="titulo" type="text"
                                                placeholder="<?php echo __('Ex: Problema com impressora no RH'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Tipo de Atendimento'); ?></label>
                                            <select class="form-control" name="categoria" required="">
                                                <option value="Incidente"><?php echo __('Incidente (Falha/Erro)'); ?></option>
                                                <option value="Mudança"><?php echo __('Mudança (Solicitação de Alteração)'); ?></option>
                                                <option value="Requisição"><?php echo __('Requisição (Pedido Novo)'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="col-sm-6 col-xl-4 offset-xl-1">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Solicitante'); ?></label>
                                            <select class="form-control" name="usuario_id" required="">
                                                <optgroup label="<?php echo __('Selecione o Solicitante'); ?>">
                                                    <?php
                                                    // SOLICITANTES: Lista todos os usuários cadastrados para seleção no chamado
                                                    include_once 'conexao.php';
                                                    $sql = "SELECT id_usuarios, nome, sobrenome FROM usuarios ORDER BY nome";
                                                    $result = $conn->query($sql);
                                                    if ($result->num_rows > 0) {
                                                        while ($row = $result->fetch_assoc()) {
                                                            $selected = ($row['id_usuarios'] == $_SESSION['id_usuarios']) ? 'selected' : '';
                                                            echo '<option value="' . $row['id_usuarios'] . '" ' . $selected . '>' . htmlspecialchars($row['nome'] . ' ' . $row['sobrenome']) . '</option>';
                                                        }
                                                    } else {
                                                        echo '<option value="">' . __('Nenhum usuário encontrado') . '</option>';
                                                    }
                                                    ?>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><?php echo __('Prioridade'); ?></label>
                                            <select class="form-control" name="prioridade" required="">
                                                <optgroup label="<?php echo __('Selecione a Prioridade'); ?>">
                                                    <option value="Baixa"><?php echo __('Baixa'); ?></option>
                                                    <option value="Média" selected><?php echo __('Média'); ?></option>
                                                    <option value="Alta"><?php echo __('Alta'); ?></option>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xl-2">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold"><i class="fas fa-paperclip"></i> <?php echo __('Anexar Arquivo'); ?></label>
                                            <input type="file" name="anexo" class="form-control-file"
                                                accept=".jpg,.jpeg,.png,.gif,.bmp,.pdf,.doc,.docx"
                                                style="font-size: 0.8rem;">
                                            <small class="text-muted"><?php echo __('Evidências (Máx. 5MB)'); ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-xl-10 offset-xl-1">
                                    <label class="text-gray-600 small font-weight-bold"><?php echo __('Descrição Detalhada'); ?></label>
                                    <textarea class="form-control" name="descricao"
                                        placeholder="<?php echo __('Descreva o problema ou solicitação com o máximo de detalhes possível...'); ?>"
                                        style="height: 120px; margin-bottom: 0px;" required=""></textarea>
                                </div>
                                <div class="col-xl-4 offset-xl-4"><button
                                        class="btn btn-success btn-block active text-white pulse animated btn-user"
                                        type="submit"
                                        style="background: rgb(44,64,74);border-radius: 10px;padding: 30px, 30px;border-width: 0px;height: 50px;margin-top: 50px;"><?php echo __('Abrir Chamado'); ?></button></div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Success Modal -->
                <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="successModalLabel"><?php echo __('Sucesso!'); ?></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php echo __('Chamado criado com sucesso!'); ?><br>
                                <strong><?php echo __('Número do Chamado:'); ?> <span id="modal-chamado-id"></span></strong>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" id="btn-redirect-chamados"><?php echo __('OK'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/bs-init.js?h=18f231563042f968d98f0c7a068280c6"></script>
    <script src="/assets/js/theme.js?h=6d33b44a6dcb451ae1ea7efc7b5c5e30"></script>
    <script>
        // ENVIO VIA AJAX: Processa o formulário sem recarregar a página
        document.getElementById('form-novo-chamado').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('inserir_chamado.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('modal-chamado-id').textContent = '#' + data.id;
                        $('#successModal').modal('show');

                        document.getElementById('btn-redirect-chamados').onclick = function () {
                            window.location.href = 'chamados.php';
                        };
                    } else {
                        alert('<?php echo __('Erro ao criar chamado: '); ?>' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php echo __('Ocorreu um erro na requisição.'); ?>');
                });
        });
    </script>
    <script src="/assets/js/global_search.js"></script>
</body>

</html>