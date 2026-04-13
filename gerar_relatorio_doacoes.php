<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Categoria', 'width' => 20, 'align' => 'L', 'field' => 'categoria'],
    ['header' => 'Marca', 'width' => 20, 'align' => 'L', 'field' => 'fabricante'],
    ['header' => 'Modelo', 'width' => 35, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 18, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Série', 'width' => 25, 'align' => 'L', 'field' => 'numero_serie'],
    ['header' => 'Processador', 'width' => 30, 'align' => 'L', 'field' => 'processador'],
    ['header' => 'Mem.', 'width' => 12, 'align' => 'L', 'field' => 'memoria'],
    ['header' => 'Storage', 'width' => 15, 'align' => 'L', 'field' => 'armazenamento'],
    ['header' => 'Doado Para', 'width' => 35, 'align' => 'L', 'field' => 'recebedor'],
    ['header' => 'Data', 'width' => 25, 'align' => 'C', 'field' => 'data_venda', 'format' => 'date'],
    ['header' => 'Descrição', 'width' => 50, 'align' => 'L', 'field' => 'descricao']
];

// Pass 'L' for Landscape orientation
$pdf = new ReportGenerator('Relatório de Ativos Doados', $columns, $conn, 'L');

// Updated SQL to include all requested fields
$sql = "SELECT v.categoria, v.fabricante, v.modelo, v.tag, v.numero_serie, v.processador, v.memoria, v.armazenamento, 
        v.data_venda, v.descricao, 
        TRIM(CONCAT(IFNULL(u.nome,''), ' ', IFNULL(u.sobrenome,''))) as recebedor 
        FROM venda v 
        LEFT JOIN usuarios u ON v.assigned_to = u.id_usuarios 
        WHERE v.status = 'Doado'
        ORDER BY v.data_venda DESC";

$pdf->generate($sql);
?>
