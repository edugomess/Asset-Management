<?php
include 'conexao.php';

echo "Iniciando migração para Chat em Grupo...\n";

// 1. Criar tabela de grupos
$sql1 = "CREATE TABLE IF NOT EXISTS chat_grupos (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nome_grupo VARCHAR(255) NOT NULL,
    admin_id INT NOT NULL,
    foto_grupo VARCHAR(255) DEFAULT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_DATE(),
    FOREIGN KEY (admin_id) REFERENCES usuarios(id_usuarios) ON DELETE CASCADE
)";
if (mysqli_query($conn, $sql1)) {
    echo "Tabela 'chat_grupos' ok.\n";
} else {
    echo "Erro ao criar 'chat_grupos': " . mysqli_error($conn) . "\n";
}

// 2. Criar tabela de membros do grupo
$sql2 = "CREATE TABLE IF NOT EXISTS chat_grupo_membros (
    id_grupo INT NOT NULL,
    usuario_id INT NOT NULL,
    last_read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_adesao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_grupo, usuario_id),
    FOREIGN KEY (id_grupo) REFERENCES chat_grupos(id_grupo) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuarios) ON DELETE CASCADE
)";
if (mysqli_query($conn, $sql2)) {
    echo "Tabela 'chat_grupo_membros' ok.\n";
} else {
    echo "Erro ao criar 'chat_grupo_membros': " . mysqli_error($conn) . "\n";
}

// 3. Adicionar coluna grupo_id em chat_mensagens
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM chat_mensagens LIKE 'grupo_id'");
if (mysqli_num_rows($check_col) == 0) {
    $sql3 = "ALTER TABLE chat_mensagens ADD COLUMN grupo_id INT DEFAULT NULL AFTER destinatario_id, 
             ADD CONSTRAINT fk_chat_grupo FOREIGN KEY (grupo_id) REFERENCES chat_grupos(id_grupo) ON DELETE CASCADE";
    if (mysqli_query($conn, $sql3)) {
        echo "Coluna 'grupo_id' adicionada em 'chat_mensagens'.\n";
    } else {
        echo "Erro ao alterar 'chat_mensagens': " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Coluna 'grupo_id' já existe em 'chat_mensagens'.\n";
}

echo "Migração concluída com sucesso.\n";
mysqli_close($conn);
?>
