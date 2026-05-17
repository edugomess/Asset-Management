<?php
require 'conexao.php';

$nome = "Blue Chip Bank";
$sistema_tipo = "dynatrace";
$url = "https://abc12345.live.dynatrace.com/api/v2/problems";
$user = "api_token_user";
$pass = "dt0c01.STX_TOKEN_EXAMPLE"; // Token de exemplo
$email_destinatario = "noc@bluechipbank.com";
$gemini_api_key = ""; 

$stmt = $conn->prepare("INSERT INTO zabbix_empresas (nome, sistema_tipo, url, user, pass, email_destinatario, gemini_api_key) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $nome, $sistema_tipo, $url, $user, $pass, $email_destinatario, $gemini_api_key);

if ($stmt->execute()) {
    echo "Empresa Dynatrace fictícia cadastrada com sucesso!\n";
} else {
    echo "Erro ao cadastrar: " . $conn->error . "\n";
}

$stmt->close();
$conn->close();
?>
