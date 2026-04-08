<?php
require 'conexao.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $sql = "UPDATE ativos 
            SET tier='Infraestrutura', 
                dataAtivacao = DATE_SUB(NOW(), INTERVAL 6 YEAR)
            LIMIT 3";
    $conn->query($sql);
    echo "OK - Ativos atualizados";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
