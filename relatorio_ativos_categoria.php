<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Categoria', 'width' => 60, 'align' => 'L', 'field' => 'categoria'],
    ['header' => 'Qtd', 'width' => 30, 'align' => 'C', 'field' => 'qtd'],
    ['header' => 'Valor Total', 'width' => 50, 'align' => 'R', 'field' => 'valor_total', 'format' => 'money']
];

$pdf = new ReportGenerator('Relatório de Ativos por Categoria', $columns, $conn);
$sql = "SELECT categoria, COUNT(*) as qtd, SUM(valor) as valor_total FROM ativos GROUP BY categoria ORDER BY qtd DESC";
$pdf->generate($sql);
?>
