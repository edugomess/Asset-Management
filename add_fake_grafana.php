<?php
require 'conexao.php';

$nome = "Data Insights Corp";
$sistema_tipo = "grafana";
$url = "http://grafana.datacorp.local/api/alerts";
$user = "viewer_bot";
$pass = "grafana123";
$email_destinatario = "infra@datacorp.com";
$gemini_api_key = ""; // Usar a padrão

$stmt = $conn->prepare("INSERT INTO zabbix_empresas (nome, sistema_tipo, url, user, pass, email_destinatario, gemini_api_key) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $nome, $sistema_tipo, $url, $user, $pass, $email_destinatario, $gemini_api_key);

if ($stmt->execute()) {
    echo "Empresa Grafana fictícia cadastrada com sucesso!\n";
} else {
    echo "Erro ao cadastrar: " . $conn->error . "\n";
}

$stmt->close();
$conn->close();
?>
