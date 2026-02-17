<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Nome do Setor', 'width' => 70, 'align' => 'L', 'field' => 'nomeSetor'],
    ['header' => 'Código', 'width' => 30, 'align' => 'C', 'field' => 'codigo'],
    ['header' => 'Gestor', 'width' => 50, 'align' => 'L', 'field' => 'gestor'],
    ['header' => 'Status', 'width' => 30, 'align' => 'C', 'field' => 'status']
];

$pdf = new ReportGenerator('Relatório de Centros de Custo (Lista)', $columns, $conn);
$sql = "SELECT nomeSetor, codigo, gestor, status FROM centro_de_custo ORDER BY nomeSetor";
$pdf->generate($sql);
?>
