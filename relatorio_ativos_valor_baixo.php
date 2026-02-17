<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Modelo', 'width' => 50, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Valor', 'width' => 40, 'align' => 'R', 'field' => 'valor', 'format' => 'money'],
    ['header' => 'Status', 'width' => 40, 'align' => 'C', 'field' => 'status']
];

$pdf = new ReportGenerator('Relatório de Ativos de Baixo Valor (< R$ 1k)', $columns, $conn);
$sql = "SELECT modelo, tag, valor, status FROM ativos WHERE valor < 1000 ORDER BY valor ASC";
$pdf->generate($sql);
?>
