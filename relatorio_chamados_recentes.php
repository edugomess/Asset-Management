<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'ID', 'width' => 15, 'align' => 'C', 'field' => 'id'],
    ['header' => 'Título', 'width' => 60, 'align' => 'L', 'field' => 'titulo'],
    ['header' => 'Data Abertura', 'width' => 40, 'align' => 'C', 'field' => 'data_abertura', 'format' => 'date'],
    ['header' => 'Status', 'width' => 35, 'align' => 'C', 'field' => 'status'],
    ['header' => 'Solicitante', 'width' => 40, 'align' => 'L', 'field' => 'nome_solicitante']
];

$pdf = new ReportGenerator('Relatório de Chamados Recentes (30 dias)', $columns, $conn);
$date_limit = date('Y-m-d', strtotime('-30 days'));
$sql = "SELECT c.id, c.titulo, c.data_abertura, c.status, u.nome as nome_solicitante 
        FROM chamados c 
        LEFT JOIN usuarios u ON c.usuario_id = u.id_usuarios 
        WHERE c.data_abertura >= '$date_limit' 
        ORDER BY c.data_abertura DESC";
$pdf->generate($sql);
