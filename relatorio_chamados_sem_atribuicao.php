<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'ID', 'width' => 20, 'align' => 'C', 'field' => 'id_chamado'],
    ['header' => 'Título', 'width' => 80, 'align' => 'L', 'field' => 'titulo'],
    ['header' => 'Data Abertura', 'width' => 40, 'align' => 'C', 'field' => 'data_abertura', 'format' => 'date'],
    ['header' => 'Categoria', 'width' => 40, 'align' => 'C', 'field' => 'categoria']
];

$pdf = new ReportGenerator('Relatório de Chamados Sem Atribuição', $columns, $conn);
$sql = "SELECT id_chamado, titulo, data_abertura, categoria 
        FROM chamados 
        WHERE responsavel_id IS NULL OR responsavel_id = 0 
        ORDER BY data_abertura ASC";
$pdf->generate($sql);
?>
