<?php
require_once 'conexao.php';

echo "--- GLOBAL ALERT CONFIG ---\n";
$res = $conn->query("SELECT * FROM configuracoes_alertas LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    print_r($row);
} else {
    echo "No global config found.\n";
}

echo "\n--- USERS IN alertas_usuarios ---\n";
$res = $conn->query("SELECT au.*, u.nome, u.email FROM alertas_usuarios au JOIN usuarios u ON au.usuario_id = u.id_usuarios");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No users configured in alertas_usuarios.\n";
}
?>