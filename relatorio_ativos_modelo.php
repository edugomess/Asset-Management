<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Modelo', 'width' => 60, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Fabricante', 'width' => 40, 'align' => 'L', 'field' => 'fabricante'],
    ['header' => 'Qtd', 'width' => 20, 'align' => 'C', 'field' => 'qtd'],
    ['header' => 'Valor Total', 'width' => 40, 'align' => 'R', 'field' => 'valor_total', 'format' => 'money']
];

$pdf = new ReportGenerator('Relatório de Ativos por Modelo', $columns, $conn);
$sql = "SELECT modelo, fabricante, COUNT(*) as qtd, SUM(valor) as valor_total FROM ativos GROUP BY modelo, fabricante ORDER BY qtd DESC";
$pdf->generate($sql);
?>
