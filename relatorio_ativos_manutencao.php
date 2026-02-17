<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Modelo', 'width' => 50, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Status', 'width' => 40, 'align' => 'C', 'field' => 'status'],
    ['header' => 'Observação', 'width' => 70, 'align' => 'L', 'field' => 'obs'] // Assuming obs exists or we just show status
];

$pdf = new ReportGenerator('Relatório de Ativos em Manutenção', $columns, $conn);
$sql = "SELECT modelo, tag, status, '' as obs FROM ativos WHERE status LIKE '%Manutencao%'";
$pdf->generate($sql);
?>
