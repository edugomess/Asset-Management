<?php
/**
 * PÁGINA DE IMPRESSÃO DE ETIQUETA V2: imprimir_etiqueta.php
 * Específicamente recriada do zero para ser compatível com impressoras térmicas (Zebra, Brother, ELGIN).
 */
include_once 'auth.php';
include_once 'conexao.php';
include_once 'language.php';

if ($_SESSION['nivelUsuario'] !== 'Admin' && $_SESSION['nivelUsuario'] !== 'Suporte') {
    die("Acesso negado.");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("ID inválido.");
}

$sql = "SELECT tag, modelo FROM ativos WHERE id_asset = '$id'";
$res = mysqli_query($conn, $sql);
$ativo = mysqli_fetch_assoc($res);
if (!$ativo) {
    die("Ativo não encontrado.");
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$qr_url = $protocol . $_SERVER['HTTP_HOST'] . "/v.php?id=" . $id;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>ETIQUETA - <?php echo $ativo['tag']; ?></title>
    <style>
        /* Reset de Impressão */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        @page {
            size: auto; /* Deixa a impressora decidir ou usa 50mm 30mm se quiser forçar */
            margin: 0mm;
        }

        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Montserrat', 'Arial', sans-serif;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Container da etiqueta - Largura casada com o QR Code (100px) */
        .etiqueta {
            width: 100px;
            padding: 0;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .modelo {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #444;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-bottom: 5px;
        }

        #qrcode {
            background-color: #fff;
            padding: 0;
            display: block;
            width: 100px; /* Alinhamento forçado */
            height: 100px;
        }

        #qrcode img {
            width: 100% !important;
            height: auto !important;
        }

        .tag {
            font-size: 15px; /* Tamanho reduzido para evitar quebra */
            font-weight: 900;
            color: #000;
            margin-top: 2px;
            letter-spacing: -0.5px; /* Ajuste para preencher lateralmente sem quebrar */
            width: 100px;
            text-align: center;
            line-height: 1;
            white-space: nowrap; /* Impede quebra de linha */
        }

        .branding {
            font-size: 5px;
            color: #999;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media print {
            .no-print { display: none; }
            body { background: #fff; }
        }
    </style>
</head>
<body>

    <div class="etiqueta">
        <div class="modelo"><?php echo htmlspecialchars($ativo['modelo']); ?></div>
        <div id="qrcode"></div>
        <div class="tag"><?php echo htmlspecialchars($ativo['tag']); ?></div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        window.onload = function() {
            const qrContainer = document.getElementById("qrcode");
            new QRCode(qrContainer, {
                text: "<?php echo $qr_url; ?>",
                width: 100,
                height: 100,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M
            });

            // Aguarda um pouco para renderizar o QR e dispara
            setTimeout(() => {
                window.print();
                // Opcional: fechar a janela se for aberta via popup, mas se for via iframe, não precisa.
            }, 500);
        };
    </script>
</body>
</html>
