<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Centro de Custo', 'width' => 60, 'align' => 'L', 'field' => 'nomeSetor'],
    ['header' => 'Software', 'width' => 60, 'align' => 'L', 'field' => 'software'],
    ['header' => 'Fabricante', 'width' => 40, 'align' => 'L', 'field' => 'fabricante'],
    ['header' => 'Status', 'width' => 30, 'align' => 'C', 'field' => 'status']
];

$pdf = new ReportGenerator('Licenças por Centro de Custo', $columns, $conn, 'P');

$sql = "SELECT cc.nomeSetor, l.software, l.fabricante, l.status 
        FROM licencas l
        LEFT JOIN centro_de_custo cc ON l.id_centro_custo = cc.id_centro_de_custo
        ORDER BY cc.nomeSetor ASC, l.software ASC";

$pdf->generate($sql);
?>