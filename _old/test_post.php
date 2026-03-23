<?php
require 'c:/xampp/htdocs/conexao.php';
$_SERVER["REQUEST_METHOD"] = "POST";
$_POST['config_alertas'] = 1;
// deliberately leave $_POST['whatsapp_ativo'] unset to simulate unchecking the box
// $_POST['chamados_ativo'] is left unset too
// Let's include the same logic
$chamados = isset($_POST['chamados_ativo']) ? 1 : 0;
$manutencao = isset($_POST['manutencao_ativo']) ? 1 : 0;
$whatsapp = isset($_POST['whatsapp_ativo']) ? 1 : 0;
$email = isset($_POST['email_ativo']) ? 1 : 0;

$sql = "UPDATE configuracoes_alertas SET 
        chamados_ativo = $chamados,
        manutencao_ativo = $manutencao,
        whatsapp_ativo = $whatsapp,
        email_ativo = $email
        WHERE id = 1";
mysqli_query($conn, $sql);

$res = mysqli_query($conn, "SELECT * FROM configuracoes_alertas LIMIT 1");
print_r(mysqli_fetch_assoc($res));
