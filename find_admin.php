<?php
require_once 'conexao.php';
$res = $conn->query("SELECT email, senha, nivelUsuario FROM usuarios WHERE nivelUsuario = 'Admin' LIMIT 1");
if ($row = $res->fetch_assoc()) {
    print_r($row);
} else {
    echo "Nenhum admin encontrado";
}
