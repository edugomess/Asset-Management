<?php
/**
 * RELATÓRIO DE CHAMADOS COM SLA VENCIDO: relatorio_chamados_sla_vencido.php
 * Destaca tickets em aberto por mais de 7 dias, auxiliando no controle de prazos.
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'ID', 'width' => 15, 'align' => 'C', 'field' => 'id'],
    ['header' => 'Título', 'width' => 70, 'align' => 'L', 'field' => 'titulo'],
    ['header' => 'Data Abertura', 'width' => 40, 'align' => 'C', 'field' => 'data_abertura', 'format' => 'date'],
    ['header' => 'Dias em Aberto', 'width' => 40, 'align' => 'C', 'field' => 'dias_aberto']
];

$pdf = new ReportGenerator('Relatório de Chamados com SLA Vencido (> 7 dias)', $columns, $conn);
// Assuming 7 days SLA for this report
$sql = "SELECT id, titulo, data_abertura, 
        (DATEDIFF(NOW(), data_abertura) - (COALESCE(tempo_congelado_minutos, 0) / 1440)) as dias_aberto 
        FROM chamados 
        WHERE status NOT IN ('Resolvido', 'Fechado', 'Cancelado') 
        AND (DATEDIFF(NOW(), data_abertura) - (COALESCE(tempo_congelado_minutos, 0) / 1440)) > 7 
        ORDER BY dias_aberto DESC";
$pdf->generate($sql);
