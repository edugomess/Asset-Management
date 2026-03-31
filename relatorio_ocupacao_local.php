<?php
/**
 * RELATÓRIO DE OCUPAÇÃO POR LOCAL: relatorio_ocupacao_local.php
 * Quantitativo de ativos por ponto físico ou lógico na infraestrutura.
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'Local / Unidade', 'width' => 100, 'align' => 'L', 'field' => 'nome_local'],
    ['header' => 'Tipo', 'width' => 50, 'align' => 'C', 'field' => 'tipo_local'],
    ['header' => 'Qtde Ativos', 'width' => 45, 'align' => 'C', 'field' => 'total']
];

$sql = "SELECT l.nome_local, l.tipo_local, COUNT(a.id_asset) as total 
        FROM locais l 
        LEFT JOIN ativos a ON l.id_local = a.id_local 
        GROUP BY l.id_local 
        ORDER BY total DESC, l.nome_local ASC";

$pdf = new ReportGenerator('Taxa de Ocupação por Local', $columns, $conn);
$pdf->generate($sql);
?>
