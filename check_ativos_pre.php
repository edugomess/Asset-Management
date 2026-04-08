<?php
require 'conexao.php';
$tags = ['TAG-000085', 'TAG-000086', 'TAG-000087'];
$tags_str = "'" . implode("', '", $tags) . "'";
$res = $conn->query("SELECT tag, status, modelo FROM ativos WHERE tag IN ($tags_str)");
if ($res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        echo $row['tag'] . ": " . $row['modelo'] . " | " . $row['status'] . "\n";
    }
} else {
    echo "Nenhum dos ativos (NT7000, ACPro, G3) está na tabela de ATIVOS.\n";
}
?>
