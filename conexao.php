<?php
/**
 * CONEXÃO COM O BANCO DE DATOS: conexao.php
 * Este arquivo estabelece o vínculo central com o MySQL utilizando a extensão MySQLi.
 */

$servername = "localhost";
$username = "root"; // Usuário padrão do phpMyAdmin (XAMPP)
$password = ""; // Senha padrão para ambientes locais de desenvolvimento
$dbname = "db_asset_mgt"; // Nome da base de dados do sistema

// INICIALIZAÇÃO: Cria o objeto de conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// VALIDAÇÃO: Interrompe a execução caso a conexão falhe
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// COMPATIBILIDADE: Garante que caracteres especiais e acentuação (UTF-8) funcionem corretamente
$conn->set_charset("utf8mb4");
?>