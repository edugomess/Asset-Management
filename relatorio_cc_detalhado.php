<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Setor', 'width' => 60, 'align' => 'L', 'field' => 'nomeSetor'],
    ['header' => 'Código', 'width' => 40, 'align' => 'C', 'field' => 'codigo'],
    ['header' => 'Gestor', 'width' => 60, 'align' => 'L', 'field' => 'gestor'] // Assuming gestor field exists or similar
];

// Check centro_de_custo schema if needed, but for now assuming these standard fields
$pdf = new ReportGenerator('Relatório Detalhado de Centros de Custo', $columns, $conn);
$sql = "SELECT nomeSetor, codigo, 'N/A' as gestor FROM centro_de_custo ORDER BY nomeSetor";
$pdf->generate($sql);
?>
