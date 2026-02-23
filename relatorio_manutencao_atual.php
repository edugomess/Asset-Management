<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Ativo (Modelo)', 'width' => 60, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Início', 'width' => 35, 'align' => 'C', 'field' => 'data_inicio', 'format' => 'date'],
    ['header' => 'Observações', 'width' => 65, 'align' => 'L', 'field' => 'observacoes']
];

$pdf = new ReportGenerator('Relatório de Manutenções Ativas', $columns, $conn, 'P');
$sql = "SELECT a.modelo, a.tag, m.data_inicio, m.observacoes 
        FROM manutencao m 
        JOIN ativos a ON m.id_asset = a.id_asset 
        WHERE m.status_manutencao = 'Em Manutenção' 
        ORDER BY m.data_inicio DESC";
$pdf->generate($sql);
?>