<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Categoria', 'width' => 30, 'align' => 'L', 'field' => 'categoria'],
    ['header' => 'Fabricante', 'width' => 30, 'align' => 'L', 'field' => 'fabricante'],
    ['header' => 'Modelo', 'width' => 45, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 25, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Hostname', 'width' => 30, 'align' => 'L', 'field' => 'hostName'],
    ['header' => 'Valor', 'width' => 25, 'align' => 'R', 'field' => 'valor', 'format' => 'money'],
    ['header' => 'Status', 'width' => 25, 'align' => 'C', 'field' => 'status'],
    ['header' => 'Centro Custo', 'width' => 40, 'align' => 'L', 'field' => 'centroDeCusto'],
    ['header' => 'Data Ativ.', 'width' => 25, 'align' => 'C', 'field' => 'dataAtivacao', 'format' => 'date']
];

// Landscape orientation ('L')
$pdf = new ReportGenerator('Relatório Geral de Ativos', $columns, $conn, 'L');
$sql = "SELECT categoria, fabricante, modelo, tag, hostName, valor, status, centroDeCusto, dataAtivacao FROM ativos ORDER BY categoria, modelo";
$pdf->generate($sql);
