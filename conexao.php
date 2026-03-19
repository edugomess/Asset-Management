<?php
/**
 * CONFIGURAÇÃO DE BANCO DE DADOS: conexao.php
 * Estabelece a conexão MySQLi e define o charset UTF-8 para garantir a integridade dos caracteres.
 */
$host = "localhost";
$username = "root"; // Usuário padrão do phpMyAdmin (XAMPP)
$password = ""; // Senha padrão para ambientes locais de desenvolvimento
$dbname = "db_asset_mgt"; // Nome da base de dados do sistema

// INICIALIZAÇÃO: Cria o objeto de conexão
$conn = new mysqli($host, $username, $password, $dbname);

// VALIDAÇÃO: Interrompe a execução caso a conexão falhe
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// COMPATIBILIDADE: Garante que caracteres especiais e acentuação (UTF-8) funcionem corretamente
$conn->set_charset("utf8mb4");

// CARREGA SISTEMA DE IDIOMA
require_once __DIR__ . '/language.php';
?>