<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Software', 'width' => 70, 'align' => 'L', 'field' => 'software'],
    ['header' => 'Total Seats', 'width' => 40, 'align' => 'C', 'field' => 'quantidade_total'],
    ['header' => 'Em Uso', 'width' => 40, 'align' => 'C', 'field' => 'quantidade_uso'],
    ['header' => '% Ocupação', 'width' => 40, 'align' => 'C', 'field' => 'ocupacao']
];

$pdf = new ReportGenerator('Ocupação de Seats por Licença', $columns, $conn, 'P');

$sql = "SELECT software, quantidade_total, quantidade_uso, 
               CONCAT(ROUND((quantidade_uso / NULLIF(quantidade_total, 0)) * 100, 1), '%') as ocupacao
        FROM licencas 
        ORDER BY (quantidade_uso / NULLIF(quantidade_total, 0)) DESC";

$pdf->generate($sql);
?>