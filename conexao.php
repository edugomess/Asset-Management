<?php
$servername = "localhost";
$username = "root"; // Usuário padrão do phpMyAdmin
$password = ""; // Sem senha
$dbname = "db_asset_mgt";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
//echo "Conexão bem-sucedida!";
?>
