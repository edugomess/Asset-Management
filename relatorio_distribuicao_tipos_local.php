<?php
/**
 * RELATÓRIO DE DISTRIBUIÇÃO POR TIPO DE LOCAL: relatorio_distribuicao_tipos_local.php
 * Análise de onde os ativos estão concentrados por categoria de infraestrutura.
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'Tipo de Localização', 'width' => 120, 'align' => 'L', 'field' => 'tipo_local'],
    ['header' => 'Qtde Ativos', 'width' => 75, 'align' => 'C', 'field' => 'total']
];

$sql = "SELECT l.tipo_local, COUNT(a.id_asset) as total 
        FROM ativos a 
        JOIN locais l ON a.id_local = l.id_local 
        GROUP BY l.tipo_local 
        ORDER BY total DESC";

$pdf = new ReportGenerator('Distribuição de Ativos por Tipo de Local', $columns, $conn);
$pdf->generate($sql);
?>
