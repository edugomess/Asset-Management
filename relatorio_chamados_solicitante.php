<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Solicitante', 'width' => 80, 'align' => 'L', 'field' => 'nome_solicitante'],
    ['header' => 'Chamados Abertos', 'width' => 60, 'align' => 'C', 'field' => 'qtd']
];

$pdf = new ReportGenerator('Relatório de Chamados por Solicitante', $columns, $conn);
$sql = "SELECT u.nome as nome_solicitante, COUNT(*) as qtd 
        FROM chamados c 
        JOIN usuarios u ON c.usuario_id = u.id_usuarios 
        GROUP BY u.nome 
        ORDER BY qtd DESC";
$pdf->generate($sql);
?>
