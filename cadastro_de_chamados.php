<?php
/**
 * ABERTURA DE CHAMADOS: cadastro_de_chamados.php
 * Interface para usuários e técnicos registrarem novos tickets de suporte.
 * Inclui campos para título, categoria, solicitante, prioridade e anexos.
 */
include_once 'auth.php'; // Proteção de sessão
?>
<!DOCTYPE html>
<html lang="<?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'pt-BR') ? 'pt-br' : 'en'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Novo Chamado'); ?> - Asset MGT</title>
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
                    <h3 class="text-dark mb-1"><?php echo __('Novo Chamado'); ?></h3>
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="inserir_chamado.php" method="post" id="form-novo-chamado" enctype="multipart/form-data">

                                <!-- Row 1: Título e Tipo -->
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="titulo"><?php echo __('Assunto / Título do Chamado'); ?></label>
                                            <input class="form-control" name="titulo" id="titulo" type="text" placeholder="<?php echo __('Ex: Problema com impressora no RH'); ?>" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="categoria"><?php echo __('Tipo de Atendimento'); ?></label>
                                            <select class="form-control" name="categoria" id="categoria" required="">
                                                <option value="Incidente"><?php echo __('Incidente (Falha/Erro)'); ?></option>
                                                <option value="Mudança"><?php echo __('Mudança (Solicitação de Alteração)'); ?></option>
                                                <option value="Requisição"><?php echo __('Requisição (Pedido Novo)'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 1.5: Service Tag (Visible only for Incidente) -->
                                <div class="row" id="row-service-tag" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="service_tag"><?php echo __('Service Tag / Identificação do Ativo'); ?></label>
                                            <div class="input-group">
                                                <input class="form-control" name="service_tag" id="service_tag" type="text" placeholder="<?php echo __('Informe a Service Tag ou ID do ativo com problema'); ?>">
                                                <div class="input-group-append" id="spinner-search" style="display: none;">
                                                    <span class="input-group-text bg-white border-left-0">
                                                        <span class="spinner-border spinner-border-sm text-primary"></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <small class="text-muted"><?php echo __('Ao digitar, o sistema buscará o ativo automaticamente.'); ?></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Selected Asset Info (AJAX) -->
                                <div class="row" id="asset-preview" style="display: none; margin-top: -10px;">
                                    <div class="col-md-12">
                                        <div class="alert alert-info d-flex align-items-center mb-3 shadow-sm border-left-info" style="padding: 10px 20px;">
                                            <i class="fas fa-laptop fa-2x mr-3 text-info"></i>
                                            <div class="flex-grow-1">
                                                <div class="font-weight-bold mb-0"><?php echo __('Ativo Selecionado:'); ?> <span id="asset-name" class="text-dark"></span></div>
                                                <div class="small text-muted"><span id="asset-info"></span></div>
                                            </div>
                                            <?php if ($_SESSION['nivelUsuario'] == 'Admin' || $_SESSION['nivelUsuario'] == 'Suporte'): ?>
                                                <a id="asset-link" href="#" target="_blank" class="btn btn-sm btn-outline-info mr-2" title="<?php echo __('Ver Detalhes do Ativo'); ?>"><i class="fas fa-external-link-alt"></i></a>
                                            <?php endif; ?>
                                            <input type="hidden" name="id_asset" id="id_asset">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 1.6: Gestor Aprovador (Visible only for Requisição and Mudança) -->
                                <div class="row" id="row-gestor" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="gestor_search"><?php echo __('Gestor para Aprovação'); ?></label>
                                            <div class="input-group">
                                                <input class="form-control" id="gestor_search" type="text" placeholder="<?php echo __('Digite o nome do gestor responsável pela aprovação...'); ?>" autocomplete="off">
                                                <div class="input-group-append" id="spinner-gestor" style="display: none;">
                                                    <span class="input-group-text bg-white border-left-0">
                                                        <span class="spinner-border spinner-border-sm text-primary"></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div id="gestor-results" class="list-group shadow-sm mt-1" style="position: absolute; width: 100%; z-index: 1000; display: none;"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Selected Gestor Info -->
                                <div class="row" id="gestor-preview" style="display: none; margin-top: -10px;">
                                    <div class="col-md-12">
                                        <div class="alert alert-primary d-flex align-items-center mb-3 shadow-sm border-left-primary" style="padding: 10px 20px;">
                                            <i class="fas fa-user-tie fa-2x mr-3 text-primary"></i>
                                            <div class="flex-grow-1">
                                                <div class="font-weight-bold mb-0"><?php echo __('Gestor Selecionado:'); ?> <span id="gestor-name" class="text-dark"></span></div>
                                                <div class="small text-muted"><span id="gestor-info"></span></div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSelectedGestor()"><i class="fas fa-times"></i></button>
                                            <input type="hidden" name="id_gestor_aprovador" id="id_gestor_aprovador">
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 2: Solicitante, Prioridade e Anexo -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="usuario_id"><?php echo __('Solicitante'); ?></label>
                                            <select class="form-control" name="usuario_id" id="usuario_id" required="">
                                                <?php
                                                include_once 'conexao.php';
                                                $sql = "SELECT id_usuarios, nome, sobrenome FROM usuarios ORDER BY nome";
                                                $result = $conn->query($sql);
                                                if ($result && $result->num_rows > 0) {
                                                    while ($row = $result->fetch_assoc()) {
                                                        $selected = ($row['id_usuarios'] == $_SESSION['id_usuarios']) ? 'selected' : '';
                                                        echo '<option value="' . $row['id_usuarios'] . '" ' . $selected . '>' . htmlspecialchars($row['nome'] . ' ' . $row['sobrenome']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="prioridade"><?php echo __('Prioridade'); ?></label>
                                            <select class="form-control" name="prioridade" id="prioridade" required="">
                                                <option value="Baixa"><?php echo __('Baixa'); ?></option>
                                                <option value="Média" selected><?php echo __('Média'); ?></option>
                                                <option value="Alta"><?php echo __('Alta'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="anexo"><i class="fas fa-paperclip"></i> <?php echo __('Anexar Arquivo'); ?></label>
                                            <input type="file" name="anexo" id="anexo" class="form-control-file" accept=".jpg,.jpeg,.png,.gif,.bmp,.pdf,.doc,.docx">
                                            <small class="text-muted d-block"><?php echo __('Evidências (Máx. 5MB)'); ?></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row 3: Descrição -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="text-gray-600 small font-weight-bold" for="descricao"><?php echo __('Descrição Detalhada'); ?></label>
                                            <textarea class="form-control" name="descricao" id="descricao" placeholder="<?php echo __('Descreva o problema ou solicitação com o máximo de detalhes possível...'); ?>" style="height: 120px;" required=""></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-4 offset-md-4">
                                        <button class="btn btn-success btn-block active text-white pulse animated btn-user" type="submit" style="background: rgb(44,64,74);border-radius: 10px;border-width: 0px;height: 50px;"><?php echo __('Abrir Chamado'); ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Success Modal -->
                <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="successModalLabel"><?php echo __('Sucesso!'); ?></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p><?php echo __('Chamado criado com sucesso!'); ?></p>
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
        // Mostrar/Ocultar campo Service Tag baseado na categoria
        const selectCategoria = document.getElementById('categoria');
        const rowServiceTag = document.getElementById('row-service-tag');
        const rowGestor = document.getElementById('row-gestor');
        const inputServiceTag = document.getElementById('service_tag');
        const inputGestorSearch = document.getElementById('gestor_search');

        function toggleServiceTag() {
            const categoria = selectCategoria.value;
            
            // Reset visibility
            rowServiceTag.style.display = 'none';
            rowGestor.style.display = 'none';
            inputServiceTag.removeAttribute('required');
            inputGestorSearch.removeAttribute('required');

            if (categoria === 'Incidente') {
                rowServiceTag.style.display = 'block';
                inputServiceTag.setAttribute('required', 'required');
            } else if (categoria === 'Requisição' || categoria === 'Mudança') {
                rowGestor.style.display = 'block';
                inputGestorSearch.setAttribute('required', 'required');
            }

            // Preview logic
            if (categoria !== 'Incidente') {
                document.getElementById('asset-preview').style.display = 'none';
            }
            if (categoria !== 'Requisição' && categoria !== 'Mudança') {
                document.getElementById('gestor-preview').style.display = 'none';
            }
        }

        selectCategoria.addEventListener('change', toggleServiceTag);
        
        // Executar ao carregar para garantir o estado correto (caso venha pré-selecionado)
        window.addEventListener('DOMContentLoaded', toggleServiceTag);

        // BUSCA DE ATIVO EM TEMPO REAL
        let searchTimeout;
        inputServiceTag.addEventListener('input', function() {
            const tag = this.value.trim();
            const preview = document.getElementById('asset-preview');
            const spinner = document.getElementById('spinner-search');
            
            clearTimeout(searchTimeout);
            
            if (tag.length < 3) {
                preview.style.display = 'none';
                document.getElementById('id_asset').value = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                spinner.style.display = 'flex';
                fetch(`ajax_buscar_ativo.php?tag=${encodeURIComponent(tag)}`)
                    .then(response => response.json())
                    .then(data => {
                        spinner.style.display = 'none';
                        if (data.success) {
                            document.getElementById('id_asset').value = data.ativo.id;
                            document.getElementById('asset-name').textContent = `${data.ativo.fabricante} ${data.ativo.modelo}`;
                            document.getElementById('asset-info').textContent = `Categoria: ${data.ativo.categoria} | Status: ${data.ativo.status}`;
                            
                            const assetLink = document.getElementById('asset-link');
                            if (assetLink) {
                                assetLink.href = data.ativo.link_perfil;
                            }
                            
                            preview.style.display = 'flex';
                            $(preview).addClass('animated fadeIn');
                        } else {
                            preview.style.display = 'none';
                            document.getElementById('id_asset').value = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        spinner.style.display = 'none';
                    });
            }, 500);
        });

        // BUSCA DE GESTOR EM TEMPO REAL
        let gestorTimeout;
        inputGestorSearch.addEventListener('input', function() {
            const query = this.value.trim();
            const resultsDiv = document.getElementById('gestor-results');
            const spinner = document.getElementById('spinner-gestor');
            
            clearTimeout(gestorTimeout);
            
            if (query.length < 3) {
                resultsDiv.style.display = 'none';
                return;
            }

            gestorTimeout = setTimeout(() => {
                spinner.style.display = 'flex';
                fetch(`ajax_buscar_usuario.php?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        spinner.style.display = 'none';
                        resultsDiv.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(user => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'list-group-item list-group-item-action flex-column align-items-start';
                                btn.innerHTML = `
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 font-weight-bold">${user.nome_completo}</h6>
                                        <small>${user.funcao || ''}</small>
                                    </div>
                                    <p class="mb-1 small">${user.email}</p>
                                `;
                                btn.onclick = () => selectGestor(user);
                                resultsDiv.appendChild(btn);
                            });
                            resultsDiv.style.display = 'block';
                        } else {
                            resultsDiv.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        spinner.style.display = 'none';
                    });
            }, 300);
        });

        function selectGestor(user) {
            document.getElementById('id_gestor_aprovador').value = user.id;
            document.getElementById('gestor-name').textContent = user.nome_completo;
            document.getElementById('gestor-info').textContent = `${user.email} | ${user.funcao || 'N/A'}`;
            document.getElementById('gestor-preview').style.display = 'flex';
            document.getElementById('gestor-results').style.display = 'none';
            document.getElementById('gestor_search').value = user.nome_completo;
        }

        function clearSelectedGestor() {
            document.getElementById('id_gestor_aprovador').value = '';
            document.getElementById('gestor-preview').style.display = 'none';
            document.getElementById('gestor_search').value = '';
            document.getElementById('gestor-results').style.display = 'none';
        }

        // Fechar resultados ao clicar fora
        document.addEventListener('click', function(e) {
            if (!document.getElementById('row-gestor').contains(e.target)) {
                document.getElementById('gestor-results').style.display = 'none';
            }
        });

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
                    alert('<?php echo __('Ocorreu um erro na requisição. Verifique se o banco de dados está atualizado.'); ?>');
                });
        });
    </script>
    <script src="/assets/js/global_search.js"></script>
</body>

</html>