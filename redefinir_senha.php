<?php
include 'conexao.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$valido = false;
$user_id = 0;

if (!empty($token)) {
    // Verificar token e expiração
    $stmt = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE reset_token = ? AND reset_token_expira > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id_usuarios'];
        $valido = true;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['idioma'] ?? 'pt-br'; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Redefinir Senha - Asset Management</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <style>
        body {
            font-family: 'Inter', sans-serif !important;
            background: radial-gradient(at 0% 0%, #1a2a33 0, transparent 50%),
                radial-gradient(at 50% 0%, #2c404a 0, transparent 50%),
                radial-gradient(at 100% 0%, #1a2a33 0, transparent 50%),
                linear-gradient(135deg, #1a2a33 0%, #0d1418 100%) !important;
            background-attachment: fixed !important;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .card {
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            max-width: 450px;
            width: 100%;
            border: none;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        .btn-primary {
            background-color: #2c404a !important;
            border: none !important;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center">
        <div class="card p-4 shadow">
            <?php if ($valido): ?>
                <div class="text-center mb-4">
                    <h3 style="color: #2c404a; font-weight: 800; text-transform: uppercase;"><?php echo __('Nova Senha'); ?></h3>
                    <p class="text-muted small"><?php echo __('Crie uma nova senha para sua conta.'); ?></p>
                </div>
                <form action="processar_redefinicao_senha.php" method="post" id="formRedefinir">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group">
                        <label for="nova_senha"><?php echo __('Nova Senha'); ?></label>
                        <input type="password" name="nova_senha" id="nova_senha" class="form-control" required style="border-radius: 10px; height: 50px;">
                    </div>
                    <div class="form-group">
                        <label for="confirmar_senha"><?php echo __('Confirmar Nova Senha'); ?></label>
                        <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control" required style="border-radius: 10px; height: 50px;">
                    </div>
                    <div id="error-msg" class="alert alert-danger d-none mt-2" style="border-radius: 10px;"></div>
                    <button type="submit" class="btn btn-primary btn-block mt-4 text-white"><?php echo __('Salvar Nova Senha'); ?></button>
                </form>
            <?php else: ?>
                <div class="text-center p-3">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h4 class="text-danger"><?php echo __('Link Inválido ou Expirado'); ?></h4>
                    <p class="text-muted"><?php echo __('Desculpe, este link de recuperação não é mais válido ou já expirou.'); ?></p>
                    <a href="esqueceu_senha.php" class="btn btn-primary btn-block mt-3 text-white"><?php echo __('Solicitar Novo Link'); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $('#formRedefinir').on('submit', function(e) {
            const s1 = $('#nova_senha').val();
            const s2 = $('#confirmar_senha').val();
            const error = $('#error-msg');

            if (s1 !== s2) {
                e.preventDefault();
                error.text('<?php echo __('As senhas não coincidem.'); ?>').removeClass('d-none');
            } else if (s1.length < 4) {
                e.preventDefault();
                error.text('<?php echo __('A senha deve ter pelo menos 4 caracteres.'); ?>').removeClass('d-none');
            }
        });
    </script>
</body>
</html>
