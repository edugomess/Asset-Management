<?php
require 'c:/xampp/htdocs/conexao.php';

$sql = "ALTER TABLE configuracoes_alertas 
        ADD COLUMN email_recebe_chamados TINYINT(1) DEFAULT 0,
        ADD COLUMN email_recebe_manutencao TINYINT(1) DEFAULT 0";

if (mysqli_query($conn, $sql)) {
    echo "Columns added successfully";
} else {
    echo "Error: " . mysqli_error($conn);
}
