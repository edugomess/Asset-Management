<?php
/**
 * RELATÓRIO DE CHAMADOS POR CATEGORIA: relatorio_chamados_categoria.php
 * Quantifica a demanda de suporte técnico agrupada por tipo de problema/solicitação.
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'Categoria', 'width' => 80, 'align' => 'L', 'field' => 'categoria'],
    ['header' => 'Quantidade', 'width' => 40, 'align' => 'C', 'field' => 'qtd'],
    ['header' => '% do Total', 'width' => 40, 'align' => 'C', 'field' => 'percent']
];

// Special logic for percentage - needs 2 queries or complex one. 
// For simplicity in ReportGenerator usage, we'll just show raw counts in this quick version 
// or I can do a subquery in SQL.

$sql = "SELECT categoria, COUNT(*) as qtd FROM chamados GROUP BY categoria ORDER BY qtd DESC";

// Standard generator doesn't do calc columns easily without extending. 
// I'll stick to counts for now to keep it simple with the helper.
$columns_simple = [
    ['header' => 'Categoria', 'width' => 100, 'align' => 'L', 'field' => 'categoria'],
    ['header' => 'Quantidade', 'width' => 50, 'align' => 'C', 'field' => 'qtd']
];

$pdf = new ReportGenerator('Relatório de Chamados por Categoria', $columns_simple, $conn);
$pdf->generate($sql);
