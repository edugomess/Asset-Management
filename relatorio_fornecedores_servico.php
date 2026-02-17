<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Serviço', 'width' => 80, 'align' => 'L', 'field' => 'servico'],
    ['header' => 'Quantidade', 'width' => 40, 'align' => 'C', 'field' => 'qtd']
];

$pdf = new ReportGenerator('Relatório de Fornecedores por Serviço', $columns, $conn);
$sql = "SELECT servico, COUNT(*) as qtd FROM fornecedor GROUP BY servico ORDER BY qtd DESC";
$pdf->generate($sql);
?>
