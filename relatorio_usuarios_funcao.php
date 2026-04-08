<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Setor', 'width' => 60, 'align' => 'L', 'field' => 'setor'],
    ['header' => 'Função', 'width' => 90, 'align' => 'L', 'field' => 'funcao'],
    ['header' => 'Quantidade', 'width' => 45, 'align' => 'C', 'field' => 'qtd']
];

$pdf = new ReportGenerator('Relatório de Usuários por Função', $columns, $conn);
$sql = "SELECT COALESCE(setor, 'Não Informado') as setor, funcao, COUNT(*) as qtd FROM usuarios GROUP BY setor, funcao ORDER BY setor, qtd DESC";
$pdf->generate($sql);
?>
