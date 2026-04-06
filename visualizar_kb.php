<?php
/**
 * VISUALIZAR ARTIGO: visualizar_kb.php
 * Exibição do conteúdo do artigo para usuários e técnicos.
 */
include_once 'auth.php';
include_once 'conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Incrementa visualização ou lógica de log se necessário
$sql = "SELECT b.*, u.nome, u.sobrenome FROM base_conhecimento b 
        LEFT JOIN usuarios u ON b.autor_id = u.id_usuarios 
        WHERE b.id = $id";
$res = $conn->query($sql);
$article = $res->fetch_assoc();

if (!$article) {
    echo "<script>alert('Artigo não encontrado.'); window.close();</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo htmlspecialchars($article['titulo']); ?> - KB</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <?php include_once 'sidebar_style.php'; ?>
    <style>
        .article-container { max-width: 900px; margin: 40px auto; }
        .article-header { border-bottom: 2px solid #f8f9fc; margin-bottom: 30px; padding-bottom: 20px; }
        .article-content { font-size: 1.1rem; line-height: 1.8; color: #2c404a; }
        .article-content img { max-width: 100%; border-radius: 10px; margin: 20px 0; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .kb-badge { background: #eef2f7; color: #2c404a; font-weight: 700; border-radius: 5px; padding: 5px 12px; }
        .utility-box { background: #f8f9fc; border-radius: 15px; padding: 25px; margin-top: 50px; text-align: center; }
    </style>
</head>

<body class="bg-light">
    <div class="container article-container animate__animated animate__fadeIn">
        <div class="card shadow-lg border-0 overflow-hidden" style="border-radius: 20px;">
            <div class="card-body p-5">
                <div class="article-header">
                    <div class="mb-3">
                        <span class="kb-badge"><i class="fas fa-tag mr-1"></i><?php echo __($article['categoria']); ?></span>
                        <span class="ml-2 text-muted small"><i class="far fa-calendar-alt mr-1"></i><?php echo date('d/m/Y', strtotime($article['data_atualizacao'])); ?></span>
                    </div>
                    <h1 class="font-weight-bold text-dark display-4" style="font-size: 2.5rem;"><?php echo htmlspecialchars($article['titulo']); ?></h1>
                    <div class="d-flex align-items-center mt-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
                            <?php echo strtoupper(substr($article['nome'], 0, 1)); ?>
                        </div>
                        <span class="text-muted small"><?php echo __('Publicado por'); ?> <strong><?php echo htmlspecialchars($article['nome'] . ' ' . $article['sobrenome']); ?></strong></span>
                    </div>
                </div>

                <div class="article-content">
                    <?php echo $article['conteudo']; ?>
                </div>

                <!-- Tags -->
                <?php if (!empty($article['palavras_chave'])): ?>
                <div class="mt-5">
                    <h6 class="font-weight-bold text-muted small text-uppercase mb-2"><?php echo __('Tags Relacionadas'); ?></h6>
                    <div class="d-flex flex-wrap" style="gap: 8px;">
                        <?php 
                        $tags = explode(',', $article['palavras_chave']);
                        foreach ($tags as $tag): 
                        ?>
                            <span class="badge badge-light border py-2 px-3" style="border-radius: 8px;"><?php echo trim(htmlspecialchars($tag)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="utility-box">
                    <h5 class="font-weight-bold mb-3"><?php echo __('Este artigo foi útil?'); ?></h5>
                    <div class="d-flex justify-content-center" style="gap: 15px;">
                        <button class="btn btn-outline-success px-4 py-2" style="border-radius: 10px;" onclick="vote(<?php echo $id; ?>, 'up')">
                            <i class="far fa-thumbs-up mr-2"></i><?php echo __('Sim'); ?>
                        </button>
                        <button class="btn btn-outline-danger px-4 py-2" style="border-radius: 10px;" onclick="vote(<?php echo $id; ?>, 'down')">
                            <i class="far fa-thumbs-down mr-2"></i><?php echo __('Não'); ?>
                        </button>
                    </div>
                    <p class="text-muted small mt-3"><?php echo $article['votos_uteis']; ?> <?php echo __('pessoas acharam isso útil'); ?></p>
                </div>

                <div class="mt-4 text-center">
                    <button class="btn btn-link text-muted" onclick="window.close()"><?php echo __('Fechar Janela'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function vote(id, type) {
            fetch(`processar_voto_kb.php?id=${id}&type=${type}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '<?php echo __('Obrigado!'); ?>',
                            text: '<?php echo __('Seu feedback ajuda a melhorar nossa base de conhecimento.'); ?>',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    }
                });
        }
    </script>
</body>

</html>
