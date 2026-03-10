<?php
require 'c:/xampp/htdocs/conexao.php';
$result_alert = mysqli_query($conn, 'SELECT * FROM configuracoes_alertas LIMIT 1');
$alert_config = mysqli_fetch_assoc($result_alert);

var_dump($alert_config);
echo "\nChamados Ativo Check: ";
var_dump((($alert_config['chamados_ativo'] ?? 1) == 1));

echo "\nWhatsApp Ativo Check: ";
var_dump((($alert_config['whatsapp_ativo'] ?? 1) == 1));
