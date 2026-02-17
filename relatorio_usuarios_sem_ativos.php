<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Nome', 'width' => 60, 'align' => 'L', 'field' => 'nome'],
    ['header' => 'Email', 'width' => 60, 'align' => 'L', 'field' => 'email'],
    ['header' => 'Centro de Custo', 'width' => 50, 'align' => 'L', 'field' => 'centroDeCusto']
];

$pdf = new ReportGenerator('Relatório de Usuários Sem Ativos', $columns, $conn);
// Users that do not appear in ativos.assigned_to
$sql = "SELECT u.nome, u.email, u.centroDeCusto 
        FROM usuarios u 
        LEFT JOIN ativos a ON u.id_usuarios = a.assigned_to 
        WHERE a.id_asset IS NULL 
        ORDER BY u.nome";
$pdf->generate($sql);
?>
