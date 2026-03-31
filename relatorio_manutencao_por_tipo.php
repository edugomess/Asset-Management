<?php require_once 'ReportGenerator.php';

$columns = [
    ['header' => 'Tipo', 'width' => 35, 'align' => 'L', 'field' => 'tipo_manutencao'],
    ['header' => 'Ativo (Tag)', 'width' => 45, 'align' => 'L', 'field' => 'ativo_display'],
    ['header' => 'Início', 'width' => 35, 'align' => 'C', 'field' => 'data_inicio', 'format' => 'date'],
    ['header' => 'Conclusão', 'width' => 35, 'align' => 'C', 'field' => 'data_fim', 'format' => 'date'],
    ['header' => 'Status', 'width' => 40, 'align' => 'C', 'field' => 'status_manutencao']
];

$sql = "SELECT m.tipo_manutencao, m.data_inicio, m.data_fim, m.status_manutencao, 
               CONCAT(a.tag, ' (', a.modelo, ')') as ativo_display 
        FROM manutencao m 
        JOIN ativos a ON m.id_asset = a.id_asset 
        ORDER BY m.tipo_manutencao ASC, m.data_inicio DESC";

$pdf = new ReportGenerator('Taxa de Ocupação por Local', $columns, $conn);
$pdf->generate($sql, 'm.data_inicio');
