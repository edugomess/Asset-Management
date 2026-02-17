<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Modelo', 'width' => 50, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Data Ativação', 'width' => 40, 'align' => 'C', 'field' => 'dataAtivacao', 'format' => 'date'],
    ['header' => 'Valor', 'width' => 30, 'align' => 'R', 'field' => 'valor', 'format' => 'money']
];

$pdf = new ReportGenerator('Relatório de Ativos Recentes (< 1 ano)', $columns, $conn);
$date_limit = date('Y-m-d', strtotime('-1 year'));
$sql = "SELECT modelo, tag, dataAtivacao, valor FROM ativos WHERE dataAtivacao >= '$date_limit' ORDER BY dataAtivacao DESC";
$pdf->generate($sql);
?>
