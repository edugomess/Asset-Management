<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Categoria', 'width' => 18, 'align' => 'L', 'field' => 'categoria'],
    ['header' => 'Marca', 'width' => 18, 'align' => 'L', 'field' => 'fabricante'],
    ['header' => 'Modelo', 'width' => 35, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 18, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Lote', 'width' => 35, 'align' => 'L', 'field' => 'lote_info'],
    ['header' => 'Série', 'width' => 24, 'align' => 'L', 'field' => 'numero_serie'],
    ['header' => 'Processador', 'width' => 30, 'align' => 'L', 'field' => 'processador'],
    ['header' => 'Mem.', 'width' => 12, 'align' => 'L', 'field' => 'memoria'],
    ['header' => 'Storage', 'width' => 15, 'align' => 'L', 'field' => 'armazenamento'],
    ['header' => 'Data', 'width' => 25, 'align' => 'C', 'field' => 'data_venda', 'format' => 'date'],
    ['header' => 'Lance (Sug.)', 'width' => 55, 'align' => 'R', 'field' => 'lance', 'format' => 'money']
];

// Pass 'L' for Landscape orientation
$pdf = new ReportGenerator('Relatório de Ativos Leiloados', $columns, $conn, 'L');

// Updated SQL to include all requested fields
$sql = "SELECT v.categoria, v.fabricante, v.modelo, v.tag, v.data_venda, v.numero_serie, v.processador, v.memoria, v.armazenamento, v.id_lote,
        CONCAT('#', v.id_lote, ' - ', l.nome_lote) as lote_info,
        (IFNULL(v.valor, 0) * 0.10) as lance 
        FROM venda v 
        LEFT JOIN lotes_leilao l ON v.id_lote = l.id_lote
        WHERE v.status = 'Leiloado' 
        ORDER BY v.data_venda DESC";

$pdf->generate($sql);
?>
