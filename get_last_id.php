<?php
require 'conexao.php';
$res = $conn->query('SELECT id FROM chamados ORDER BY id DESC LIMIT 1');
if ($row = $res->fetch_assoc()) {
    echo $row['id'];
}
?>