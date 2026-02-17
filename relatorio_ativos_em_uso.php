<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Modelo', 'width' => 40, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Usuário', 'width' => 40, 'align' => 'L', 'field' => 'nome_usuario'],
    ['header' => 'Centro Custo', 'width' => 40, 'align' => 'L', 'field' => 'centroDeCusto']
];

$pdf = new ReportGenerator('Relatório de Ativos em Uso', $columns, $conn);
// Join with usuarios to get name
$sql = "SELECT a.modelo, a.tag, a.centroDeCusto, u.nome as nome_usuario 
        FROM ativos a 
        LEFT JOIN usuarios u ON a.assigned_to = u.id_usuarios 
        WHERE a.assigned_to IS NOT NULL AND a.assigned_to != 0";
$pdf->generate($sql);
?>
