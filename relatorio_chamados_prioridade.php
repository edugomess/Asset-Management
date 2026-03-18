<?php
/**
 * RELATÓRIO DE CHAMADOS POR PRIORIDADE: relatorio_chamados_prioridade.php
 * Mostra a distribuição dos chamados por níveis de criticidade.
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'Prioridade', 'width' => 95, 'align' => 'L', 'field' => 'prioridade'],
    ['header' => 'Quantidade', 'width' => 95, 'align' => 'C', 'field' => 'qtd']
];

$sql = "SELECT prioridade, COUNT(*) as qtd FROM chamados GROUP BY prioridade ORDER BY 
        CASE 
            WHEN prioridade = 'Alta' THEN 1 
            WHEN prioridade = 'Média' THEN 2 
            WHEN prioridade = 'Baixa' THEN 3 
            ELSE 4 
        END";

$pdf = new ReportGenerator('Relatório de Chamados por Prioridade', $columns, $conn);
$pdf->generate($sql);
?>
