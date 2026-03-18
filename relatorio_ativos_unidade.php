<?php
/**
 * RELATÓRIO DE ATIVOS POR UNIDADE: relatorio_ativos_unidade.php
 * Lista a distribuição geográfica/organizacional dos ativos baseada na unidade do usuário.
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'Unidade / Localidade', 'width' => 140, 'align' => 'L', 'field' => 'unidade'],
    ['header' => 'Total de Ativos', 'width' => 50, 'align' => 'C', 'field' => 'qtd']
];

$sql = "SELECT u.unidade, COUNT(a.id_asset) as qtd 
        FROM ativos a 
        JOIN usuarios u ON a.assigned_to = u.id_usuarios 
        GROUP BY u.unidade 
        ORDER BY qtd DESC";

$pdf = new ReportGenerator('Relatório de Ativos por Unidade', $columns, $conn);
$pdf->generate($sql);
?>
