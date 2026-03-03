<?php
require_once 'c:\xampp\htdocs\conexao.php';

if (!$conn) {
    die("Falha na conexão: " . mysqli_connect_error());
}

$sql = "CREATE TABLE IF NOT EXISTS configuracoes_alertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    whatsapp_ativo TINYINT(1) DEFAULT 1,
    email_ativo TINYINT(1) DEFAULT 1
)";

if (mysqli_query($conn, $sql)) {
    echo "Tabela configuracoes_alertas verificada/criada com sucesso!\n";

    $check = mysqli_query($conn, "SELECT id FROM configuracoes_alertas LIMIT 1");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO configuracoes_alertas (whatsapp_ativo, email_ativo) VALUES (1, 1)");
        echo "Dados iniciais inseridos.\n";
    } else {
        echo "Dados já existem.\n";
    }
} else {
    echo "Erro ao criar tabela: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>