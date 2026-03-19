<?php include 'conexao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Esqueceu a Senha - Asset Management</title>
    <link rel="icon" type="image/jpeg" sizes="800x800" href="/assets/img/1.gif?h=a002dd0d4fa7f57eb26a5036bc012b90">
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css?h=10db4134a440e5796ec9b2db37a80278">
    <link rel="stylesheet" href="/assets/css/Montserrat.css?h=4f0fce47efb23b5c354caba98ff44c36">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif !important;
            background: radial-gradient(at 0% 0%, #1a2a33 0, transparent 50%),
                radial-gradient(at 50% 0%, #2c404a 0, transparent 50%),
                radial-gradient(at 100% 0%, #1a2a33 0, transparent 50%),
                linear-gradient(135deg, #1a2a33 0%, #0d1418 100%) !important;
            background-attachment: fixed !important;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
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
        .brand-title {
            color: #2c404a;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center">
        <div class="card p-4 shadow pulse animated">
            <div class="text-center mb-4">
                <h3 class="brand-title"><?php echo __('Recuperar Senha'); ?></h3>
                <p class="text-muted small"><?php echo __('Insira seu e-mail para receber um link de redefinição.'); ?></p>
            </div>
            <form action="processar_esqueceu_senha.php" method="post">
                <div class="form-group">
                    <label for="email"><?php echo __('E-mail cadastrado'); ?></label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="<?php echo __('exemplo@email.com'); ?>" required style="border-radius: 10px; height: 50px;">
                </div>
                <button type="submit" class="btn btn-primary btn-block mt-4 text-white"><?php echo __('Enviar Link de Recuperação'); ?></button>
                <div class="text-center mt-3">
                    <a href="login.php" class="small text-muted" style="text-decoration: none; font-weight: 600;"><?php echo __('Voltar ao Login'); ?></a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
