<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Software', 'width' => 50, 'align' => 'L', 'field' => 'software'],
    ['header' => 'Fabricante', 'width' => 40, 'align' => 'L', 'field' => 'fabricante'],
    ['header' => 'Tipo', 'width' => 30, 'align' => 'L', 'field' => 'tipo'],
    ['header' => 'Total', 'width' => 15, 'align' => 'C', 'field' => 'quantidade_total'],
    ['header' => 'Uso', 'width' => 15, 'align' => 'C', 'field' => 'quantidade_uso'],
    ['header' => 'Expiração', 'width' => 25, 'align' => 'C', 'field' => 'data_expiracao', 'format' => 'date'],
    ['header' => 'Status', 'width' => 20, 'align' => 'C', 'field' => 'status']
];

$pdf = new ReportGenerator('Relatório Geral de Licenças', $columns, $conn, 'P');
$sql = "SELECT software, fabricante, tipo, quantidade_total, quantidade_uso, data_expiracao, status FROM licencas ORDER BY software";
$pdf->generate($sql);
?>