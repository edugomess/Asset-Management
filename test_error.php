<?php
require 'conexao.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn->query("UPDATE configuracoes_depreciacao SET elegivel_leilao=0");
    echo "OK elegivel_leilao";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
