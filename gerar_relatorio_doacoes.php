<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Modelo', 'width' => 45, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 25, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Host Name', 'width' => 35, 'align' => 'L', 'field' => 'hostName'],
    ['header' => 'Doado Para', 'width' => 45, 'align' => 'L', 'field' => 'recebedor'],
    ['header' => 'Centro de Custo', 'width' => 40, 'align' => 'L', 'field' => 'centroDeCusto'],
    ['header' => 'Data', 'width' => 30, 'align' => 'C', 'field' => 'data_venda', 'format' => 'date'],
    ['header' => 'Descrição', 'width' => 50, 'align' => 'L', 'field' => 'descricao']
];

// Pass 'L' for Landscape orientation
$pdf = new ReportGenerator('Relatório de Ativos Doados/Vendidos', $columns, $conn, 'L');

// Updated SQL to include recipient name and use data_venda
$sql = "SELECT v.modelo, v.tag, v.hostName, v.data_venda, v.descricao, v.centroDeCusto, 
        TRIM(CONCAT(IFNULL(u.nome,''), ' ', IFNULL(u.sobrenome,''))) as recebedor 
        FROM venda v 
        LEFT JOIN usuarios u ON v.assigned_to = u.id_usuarios 
        ORDER BY v.data_venda DESC";

$pdf->generate($sql);
?>
