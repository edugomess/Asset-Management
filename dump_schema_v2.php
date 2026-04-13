<?php
include 'conexao.php';
$tables = ['manutencao', 'atribuicoes', 'atribuicoes_licencas', 'historico_ativos', 'ativos', 'venda'];
foreach ($tables as $t) {
    echo "TABLE: $t\n";
    $res = $conn->query("SHOW COLUMNS FROM $t");
    if ($res) {
        while($row = $res->fetch_assoc()) {
            echo "  " . $row['Field'] . "\n";
        }
    } else {
        echo "  ERROR: " . $conn->error . "\n";
    }
    echo "\n";
}
?>
