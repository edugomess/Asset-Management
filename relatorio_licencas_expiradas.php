<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Software', 'width' => 60, 'align' => 'L', 'field' => 'software'],
    ['header' => 'Chave', 'width' => 60, 'align' => 'L', 'field' => 'chave'],
    ['header' => 'Expiração', 'width' => 35, 'align' => 'C', 'field' => 'data_expiracao', 'format' => 'date'],
    ['header' => 'Status', 'width' => 35, 'align' => 'C', 'field' => 'status']
];

$pdf = new ReportGenerator('Licenças Expiradas ou Próximas ao Vencimento', $columns, $conn, 'P');
// Seleciona as expiradas ou que vencem nos próximos 60 dias
$sql = "SELECT software, chave, data_expiracao, status 
        FROM licencas 
        WHERE status = 'Expirada' 
           OR (data_expiracao IS NOT NULL AND data_expiracao <= DATE_ADD(CURDATE(), INTERVAL 60 DAY))
        ORDER BY data_expiracao ASC";
$pdf->generate($sql);
?>