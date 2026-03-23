<?php
include 'conexao.php';

$alert_config = ['whatsapp_ativo' => 1, 'email_ativo' => 1];
$result_alert = mysqli_query($conn, "SELECT * FROM configuracoes_alertas LIMIT 1");
if ($result_alert && mysqli_num_rows($result_alert) > 0) {
    $alert_config = mysqli_fetch_assoc($result_alert);
}

echo "Current Idioma in DB: '" . $alert_config['idioma'] . "'\n";

$is_pt = ($alert_config['idioma'] ?? 'pt-BR') == 'pt-BR';
$is_en = ($alert_config['idioma'] ?? 'pt-BR') == 'en-US';

echo "Is PT-BR? " . ($is_pt ? 'TRUE' : 'FALSE') . "\n";
echo "Is EN-US? " . ($is_en ? 'TRUE' : 'FALSE') . "\n";
?>
