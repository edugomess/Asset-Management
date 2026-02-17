<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'ID', 'width' => 15, 'align' => 'C', 'field' => 'id_chamado'],
    ['header' => 'Título', 'width' => 60, 'align' => 'L', 'field' => 'titulo'],
    ['header' => 'Data Abertura', 'width' => 40, 'align' => 'C', 'field' => 'data_abertura', 'format' => 'date'],
    ['header' => 'Categoria', 'width' => 40, 'align' => 'L', 'field' => 'categoria'],
    ['header' => 'Solicitante', 'width' => 35, 'align' => 'L', 'field' => 'nome_solicitante']
];

$pdf = new ReportGenerator('Relatório de Chamados Abertos', $columns, $conn);
$sql = "SELECT c.id_chamado, c.titulo, c.data_abertura, c.categoria, u.nome as nome_solicitante 
        FROM chamados c 
        LEFT JOIN usuarios u ON c.usuario_id = u.id_usuarios 
        WHERE c.status = 'Aberto' 
        ORDER BY c.data_abertura ASC";
$pdf->generate($sql);
?>
