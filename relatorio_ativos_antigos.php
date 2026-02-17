<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Modelo', 'width' => 50, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Data Ativação', 'width' => 40, 'align' => 'C', 'field' => 'dataAtivacao', 'format' => 'date'],
    ['header' => 'Status', 'width' => 30, 'align' => 'C', 'field' => 'status'],
    ['header' => 'Centro Custo', 'width' => 40, 'align' => 'L', 'field' => 'centroDeCusto']
];

$pdf = new ReportGenerator('Relatório de Ativos Antigos (> 3 anos)', $columns, $conn);
// 3 years ago
$date_limit = date('Y-m-d', strtotime('-3 years'));
$sql = "SELECT modelo, tag, dataAtivacao, status, centroDeCusto FROM ativos WHERE dataAtivacao <= '$date_limit' ORDER BY dataAtivacao ASC";
$pdf->generate($sql);
?>
