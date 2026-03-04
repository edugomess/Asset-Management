<?php
require_once 'conexao.php';
$email = 'tester@asset.mgt';
$res = $conn->query("SELECT id_usuarios, email, nivelUsuario, status FROM usuarios WHERE email = '$email'");
if ($row = $res->fetch_assoc()) {
    echo "Usuário encontrado no banco:\n";
    print_r($row);
} else {
    echo "Usuário NÃO encontrado no banco.\n";

    echo "\nListando últimos 5 usuários registrados:\n";
    $list = $conn->query("SELECT id_usuarios, email FROM usuarios ORDER BY id_usuarios DESC LIMIT 5");
    while ($r = $list->fetch_assoc()) {
        print_r($r);
    }
}
mysqli_close($conn);
?>