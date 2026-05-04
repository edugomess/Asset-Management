<?php
include 'conexao.php';

$conn->query("SET FOREIGN_KEY_CHECKS = 0");

$conn->query("DROP TABLE IF EXISTS ativos");
$conn->query("DROP TABLE IF EXISTS centro_de_custo");
$conn->query("DROP TABLE IF EXISTS fornecedor");
$conn->query("DROP TABLE IF EXISTS venda");

$sql_ativos = "CREATE TABLE ativos (
    id_asset INT AUTO_INCREMENT PRIMARY KEY,
    categoria VARCHAR(255) DEFAULT NULL,
    fabricante VARCHAR(255) DEFAULT NULL,
    modelo VARCHAR(255) DEFAULT NULL,
    tag VARCHAR(255) DEFAULT NULL,
    numero_serie VARCHAR(255) DEFAULT NULL,
    hostName VARCHAR(255) DEFAULT NULL,
    valor VARCHAR(50) DEFAULT NULL,
    macAdress VARCHAR(255) DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'Disponível',
    centroDeCusto VARCHAR(255) DEFAULT NULL,
    setor VARCHAR(255) DEFAULT NULL,
    fornecedor VARCHAR(255) DEFAULT NULL,
    descricao TEXT DEFAULT NULL,
    imagem VARCHAR(255) DEFAULT '/assets/img/no-image.png',
    dataAtivacao DATE DEFAULT NULL,
    memoria VARCHAR(255) DEFAULT NULL,
    processador VARCHAR(255) DEFAULT NULL,
    armazenamento VARCHAR(255) DEFAULT NULL,
    tipo_armazenamento VARCHAR(255) DEFAULT NULL,
    numero_nota_fiscal VARCHAR(44) DEFAULT NULL,
    anexo_nota_fiscal VARCHAR(255) DEFAULT NULL,
    tier VARCHAR(255) DEFAULT NULL,
    gpu VARCHAR(255) DEFAULT NULL,
    polegadas VARCHAR(255) DEFAULT NULL,
    is_scanner VARCHAR(10) DEFAULT NULL,
    imei VARCHAR(255) DEFAULT NULL,
    sim_card VARCHAR(255) DEFAULT NULL,
    assigned_type VARCHAR(50) DEFAULT 'Usuario',
    assigned_to INT DEFAULT NULL,
    id_local INT DEFAULT NULL,
    parent_asset_id INT DEFAULT NULL
)";

$sql_cc = "CREATE TABLE centro_de_custo (
    id_centro_de_custo INT AUTO_INCREMENT PRIMARY KEY,
    nomeSetor VARCHAR(255) DEFAULT NULL,
    codigo VARCHAR(255) DEFAULT NULL,
    ramal VARCHAR(255) DEFAULT NULL,
    unidade VARCHAR(255) DEFAULT NULL,
    emailGestor VARCHAR(255) DEFAULT NULL,
    gestor VARCHAR(255) DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'Ativo',
    descricao TEXT DEFAULT NULL
)";

$sql_forn = "CREATE TABLE fornecedor (
    id_fornecedor INT AUTO_INCREMENT PRIMARY KEY,
    nomeEmpresa VARCHAR(255) DEFAULT NULL,
    cnpj VARCHAR(50) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    telefone VARCHAR(50) DEFAULT NULL,
    servico VARCHAR(255) DEFAULT NULL,
    site VARCHAR(255) DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'Ativo',
    imagem VARCHAR(255) DEFAULT '/assets/img/no-image.png'
)";

$sql_venda = "CREATE TABLE venda (
    id_venda INT AUTO_INCREMENT PRIMARY KEY,
    categoria VARCHAR(255) DEFAULT NULL,
    fabricante VARCHAR(255) DEFAULT NULL,
    modelo VARCHAR(255) DEFAULT NULL,
    tag VARCHAR(255) DEFAULT NULL,
    hostName VARCHAR(255) DEFAULT NULL,
    valor VARCHAR(50) DEFAULT NULL,
    macAdress VARCHAR(255) DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'Doado',
    dataAtivacao DATE DEFAULT NULL,
    centroDeCusto VARCHAR(255) DEFAULT NULL,
    descricao TEXT DEFAULT NULL,
    id_lote INT DEFAULT NULL
)";

if(!$conn->query($sql_ativos)) echo "Erro ativos: " . $conn->error . "\n";
if(!$conn->query($sql_cc)) echo "Erro cc: " . $conn->error . "\n";
if(!$conn->query($sql_forn)) echo "Erro forn: " . $conn->error . "\n";
if(!$conn->query($sql_venda)) echo "Erro venda: " . $conn->error . "\n";

$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "Tabelas recriadas.\n";
?>
