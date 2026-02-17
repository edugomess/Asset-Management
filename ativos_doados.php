<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Modelo', 'width' => 60, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Host Name', 'width' => 40, 'align' => 'L', 'field' => 'hostName'],
    ['header' => 'Data', 'width' => 40, 'align' => 'C', 'field' => 'dataAtivacao', 'format' => 'date'], // recycling dataAtivacao as 'data da acao' based on insertion logic
    ['header' => 'Descrição', 'width' => 20, 'align' => 'L', 'field' => 'descricao']
];

$pdf = new ReportGenerator('Relatório de Ativos Doados/Vendidos', $columns, $conn);
$sql = "SELECT modelo, tag, hostName, dataAtivacao, descricao FROM venda ORDER BY dataAtivacao DESC";
$pdf->generate($sql);
?>
