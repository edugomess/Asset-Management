<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Modelo', 'width' => 50, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Localização/Status', 'width' => 50, 'align' => 'L', 'field' => 'status']
];

$pdf = new ReportGenerator('Relatório de Ativos Disponíveis', $columns, $conn);
$sql = "SELECT modelo, tag, status FROM ativos WHERE status = 'Disponivel' OR status = 'Em Estoque'";
$pdf->generate($sql);
?>
