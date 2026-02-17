<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Modelo', 'width' => 50, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Valor', 'width' => 40, 'align' => 'R', 'field' => 'valor', 'format' => 'money'],
    ['header' => 'Centro Custo', 'width' => 50, 'align' => 'L', 'field' => 'centroDeCusto']
];

$pdf = new ReportGenerator('Relatório de Ativos de Alto Valor (> R$ 5k)', $columns, $conn);
$sql = "SELECT modelo, tag, valor, centroDeCusto FROM ativos WHERE valor > 5000 ORDER BY valor DESC";
$pdf->generate($sql);
?>
