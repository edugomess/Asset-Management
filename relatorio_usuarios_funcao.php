<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Função', 'width' => 100, 'align' => 'L', 'field' => 'funcao'],
    ['header' => 'Quantidade', 'width' => 50, 'align' => 'C', 'field' => 'qtd']
];

$pdf = new ReportGenerator('Relatório de Usuários por Função', $columns, $conn);
$sql = "SELECT funcao, COUNT(*) as qtd FROM usuarios GROUP BY funcao ORDER BY qtd DESC";
$pdf->generate($sql);
?>
