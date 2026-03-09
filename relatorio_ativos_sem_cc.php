<?php
/**
 * RELATÓRIO DE ATIVOS SEM CENTRO DE CUSTO: relatorio_ativos_sem_cc.php
 * Identifica equipamentos que ainda não foram vinculados a uma unidade financeira.
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'Modelo', 'width' => 50, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Valor', 'width' => 30, 'align' => 'R', 'field' => 'valor', 'format' => 'money']
];

$pdf = new ReportGenerator('Relatório de Ativos sem Centro de Custo', $columns, $conn);
$sql = "SELECT modelo, tag, valor FROM ativos WHERE centroDeCusto IS NULL OR centroDeCusto = ''";
$pdf->generate($sql);
?>