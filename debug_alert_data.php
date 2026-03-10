<?php
require_once 'conexao.php';
$res = $conn->query("SELECT * FROM configuracoes_alertas");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Erro: " . $conn->error;
}
$conn->close();
