<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Modelo', 'width' => 45, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 25, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Host Name', 'width' => 35, 'align' => 'L', 'field' => 'hostName'],
    ['header' => 'Centro de Custo', 'width' => 40, 'align' => 'L', 'field' => 'centroDeCusto'],
    ['header' => 'Data Leilão', 'width' => 30, 'align' => 'C', 'field' => 'data_venda', 'format' => 'date'],
    ['header' => 'Descrição', 'width' => 50, 'align' => 'L', 'field' => 'descricao'],
    ['header' => 'Lance (Sugerido)', 'width' => 40, 'align' => 'R', 'field' => 'lance', 'format' => 'currency']
];

// Pass 'L' for Landscape orientation
$pdf = new ReportGenerator('Relatório de Ativos Leiloados', $columns, $conn, 'L');

// Updated SQL to filter only Leiloados and calculate lance_sugerido
$sql = "SELECT v.modelo, v.tag, v.hostName, v.data_venda, v.descricao, v.centroDeCusto, 
        TRIM(CONCAT(IFNULL(u.nome,''), ' ', IFNULL(u.sobrenome,''))) as recebedor,
        (IFNULL(v.valor, 0) * 0.10) as lance
        FROM venda v 
        LEFT JOIN usuarios u ON v.assigned_to = u.id_usuarios 
        WHERE v.status = 'Leiloado'
        ORDER BY v.data_venda DESC";

$pdf->generate($sql);
?>
