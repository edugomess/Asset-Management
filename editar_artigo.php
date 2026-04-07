<?php
/**
 * EDITAR ARTIGO: editar_artigo.php
 * Formulário para modificação de artigos existentes na Base de Conhecimento.
 */
include_once 'auth.php';
include_once 'conexao.php';

if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    header("Location: index.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM base_conhecimento WHERE id = $id";
$res = $conn->query($sql);
$article = $res->fetch_assoc();

if (!$article) {
    header("Location: gerenciar_kb.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo __('Editar Artigo'); ?> - Asset Mgt</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
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
                    <div class="d-sm-flex justify-content-between align-items-center mb-4">
                        <h3 class="text-dark mb-0"><?php echo __('Editar Artigo'); ?></h3>
                        <a href="gerenciar_kb.php" class="btn btn-secondary shadow-sm" style="border-radius: 10px; padding: 8px 20px;">
                            <i class="fas fa-arrow-left mr-2"></i><?php echo __('Voltar'); ?>
                        </a>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form action="processar_kb.php" method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-700"><?php echo __('Título do Artigo'); ?></label>
                                            <input type="text" name="titulo" class="form-control form-control-lg premium-input" 
                                                value="<?php echo htmlspecialchars($article['titulo']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-gray-700"><?php echo __('Categoria'); ?></label>
                                            <select name="categoria" class="form-control form-control-lg premium-input" required>
                                                <?php
                                                $categories = ['Software', 'Hardware', 'Rede', 'Segurança', 'Procedimentos'];
                                                foreach ($categories as $cat) {
                                                    $selected = ($article['categoria'] == $cat) ? 'selected' : '';
                                                    echo "<option value='$cat' $selected>".__($cat)."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label class="font-weight-bold text-gray-700"><?php echo __('Palavras-chave (Tags)'); ?></label>
                                    <input type="text" name="palavras_chave" class="form-control" 
                                        value="<?php echo htmlspecialchars($article['palavras_chave']); ?>"
                                        placeholder="<?php echo __('Separe por vírgulas: vpn, cisco, acesso remoto'); ?>">
                                </div>

                                <div class="form-group mt-4">
                                    <label class="font-weight-bold text-gray-700"><?php echo __('Conteúdo do Artigo'); ?></label>
                                    <textarea id="summernote" name="conteudo" required><?php echo $article['conteudo']; ?></textarea>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary px-5 shadow-sm" style="background: #2c404a; border: none; border-radius: 10px; height: 50px; font-weight: 600;">
                                        <i class="fas fa-save mr-2"></i><?php echo __('Atualizar Artigo'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                tabsize: 2,
                height: 400,
                dialogsInBody: true,
                dialogsFade: true,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'video', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        for (let i = 0; i < files.length; i++) {
                            uploadImage(files[i], this);
                        }
                    }
                }
            });
        });

        function uploadImage(file, editor) {
            let data = new FormData();
            data.append("image", file);
            $.ajax({
                url: "ajax_upload_summernote.php",
                cache: false,
                contentType: false,
                processData: false,
                data: data,
                type: "POST",
                success: function(response) {
                    if (response.success) {
                        $(editor).summernote('insertImage', response.url);
                    } else {
                        alert("Erro no upload: " + response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error(textStatus + " " + errorThrown);
                }
            });
        }
    </script>
</body>

</html>
