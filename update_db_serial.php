<?php
include 'conexao.php';

$sqls = [
    "ALTER TABLE ativos ADD COLUMN numero_serie VARCHAR(100) AFTER tag"
];

foreach ($sqls as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Sucesso: $sql\n";
    } else {
        echo "Erro: " . mysqli_error($conn) . "\n";
    }
}
?>
