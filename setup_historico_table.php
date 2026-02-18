<?php
include 'conexao.php';

$sql = "CREATE TABLE IF NOT EXISTS historico_ativos (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ativo_id INT(11) NOT NULL,
    usuario_id INT(11) NULL COMMENT 'Usuário que realizou a ação',
    acao VARCHAR(50) NOT NULL COMMENT 'Tipo de ação (Criação, Edição, Atribuição, etc)',
    detalhes TEXT NULL COMMENT 'Detalhes da mudança',
    data_evento DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ativo_id) REFERENCES ativos(id_asset) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuarios) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabela 'historico_ativos' criada com sucesso ou já existe.";
}
else {
    echo "Erro ao criar tabela: " . $conn->error;
}

$conn->close();
?>
