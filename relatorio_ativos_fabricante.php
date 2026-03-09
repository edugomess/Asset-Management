<?php
/**
 * RELATÓRIO DE ATIVOS POR FABRICANTE: relatorio_ativos_fabricante.php
 * Lista os equipamentos agrupados por marca/fabricante para análise de padronização.
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'Fabricante', 'width' => 40, 'align' => 'L', 'field' => 'fabricante'],
    ['header' => 'Modelo', 'width' => 50, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Categoria', 'width' => 40, 'align' => 'L', 'field' => 'categoria'],
    ['header' => 'Valor', 'width' => 30, 'align' => 'R', 'field' => 'valor', 'format' => 'money']
];

$pdf = new ReportGenerator('Relatório de Ativos por Fabricante', $columns, $conn);
$sql = "SELECT fabricante, modelo, tag, categoria, valor FROM ativos ORDER BY fabricante, modelo";
$pdf->generate($sql);
?>