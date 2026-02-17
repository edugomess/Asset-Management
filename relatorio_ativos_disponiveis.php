<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Modelo', 'width' => 50, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Localização (C.C.)', 'width' => 50, 'align' => 'L', 'field' => 'centroDeCusto']
];

$pdf = new ReportGenerator('Relatório de Ativos Disponíveis', $columns, $conn);
$sql = "SELECT modelo, tag, centroDeCusto FROM ativos WHERE status = 'Ativo' AND (assigned_to IS NULL OR assigned_to = 0 OR assigned_to = '')";
$pdf->generate($sql);
?>
