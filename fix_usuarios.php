<?php
include 'conexao.php';

// Tenta dropar a tabela (pode dar erro se estiver corrompida, mas tentamos)
$conn->query("DROP TABLE IF EXISTS usuarios");

$sql = "CREATE TABLE usuarios (
    id_usuarios INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255),
    sobrenome VARCHAR(255),
    usuarioAD VARCHAR(255),
    funcao VARCHAR(255),
    dataNascimento DATE,
    email VARCHAR(255),
    cpf VARCHAR(20),
    centroDeCusto VARCHAR(255),
    setor VARCHAR(255),
    matricula VARCHAR(255),
    telefone VARCHAR(50),
    tipoContrato VARCHAR(255),
    senha VARCHAR(255),
    nivelUsuario ENUM('Admin', 'Suporte', 'Usuário') DEFAULT 'Usuário',
    unidade VARCHAR(255),
    status VARCHAR(50) DEFAULT 'Ativo',
    foto_perfil VARCHAR(255) DEFAULT '/assets/img/no-image.png',
    chat_status ENUM('Disponível', 'Ausente', 'Ocupado', 'Offline') DEFAULT 'Disponível',
    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "Tabela usuarios recriada com sucesso.\n";
} else {
    echo "Erro ao recriar tabela: " . $conn->error . "\n";
}

// Inserir Admin
$senhaAdmin = sha1('admin');
$sql_insert = "INSERT INTO usuarios (nome, sobrenome, email, senha, nivelUsuario, status, chat_status) 
               VALUES ('Admin', 'Sistema', 'admin@admin.com', '$senhaAdmin', 'Admin', 'Ativo', 'Disponível')";
$conn->query($sql_insert);

// Inserir Test User
$senhaTest = sha1('123456');
$sql_insert_test = "INSERT INTO usuarios (nome, sobrenome, usuarioAD, matricula, email, funcao, setor, centroDeCusto, unidade, nivelUsuario, tipoContrato, cpf, telefone, dataNascimento, senha, status) 
               VALUES ('Test', 'User', 'test.user', '12345', 'test@test.com', 'Dev', 'IT', 'CS', 'HQ', 'Usuário', 'CLT', '12345678901', '12345678', '1990-01-01', '$senhaTest', 'Ativo')";
$conn->query($sql_insert_test);

echo "Usuários padrão inseridos.\n";
?>
