<?php
require_once 'c:\xampp\htdocs\conexao.php';

// 1. Adicionar coluna email_destino se não existir
$res = mysqli_query($conn, "SHOW COLUMNS FROM configuracoes_alertas LIKE 'email_destino'");
if (mysqli_num_rows($res) == 0) {
    mysqli_query($conn, "ALTER TABLE configuracoes_alertas ADD COLUMN email_destino VARCHAR(255) DEFAULT 'test-1ax74w9bd@srv1.mail-tester.com'");
    echo "Coluna 'email_destino' adicionada.\n";
} else {
    echo "Coluna 'email_destino' já existe.\n";
}

// 2. Listar emails para login de teste
$res_users = mysqli_query($conn, "SELECT email FROM usuarios LIMIT 5");
echo "Usuários para teste:\n";
while ($row = mysqli_fetch_assoc($res_users)) {
    echo "- " . $row['email'] . "\n";
}

mysqli_close($conn);
?>