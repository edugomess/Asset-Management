<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Ativo (Modelo)', 'width' => 45, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 25, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Início', 'width' => 25, 'align' => 'C', 'field' => 'data_inicio', 'format' => 'date'],
    ['header' => 'Conclusão', 'width' => 25, 'align' => 'C', 'field' => 'data_fim', 'format' => 'date'],
    ['header' => 'Observações', 'width' => 70, 'align' => 'L', 'field' => 'observacoes']
];

$pdf = new ReportGenerator('Histórico de Manutenções Concluídas', $columns, $conn, 'P');
$sql = "SELECT a.modelo, a.tag, m.data_inicio, m.data_fim, m.observacoes 
        FROM manutencao m 
        JOIN ativos a ON m.id_asset = a.id_asset 
        WHERE m.status_manutencao = 'Concluído' 
        ORDER BY m.data_fim DESC";
$pdf->generate($sql);
?>