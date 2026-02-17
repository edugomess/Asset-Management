<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Usuário', 'width' => 40, 'align' => 'L', 'field' => 'nome_usuario'],
    ['header' => 'Modelo', 'width' => 50, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag']
];

$pdf = new ReportGenerator('Relatório de Ativos por Usuário', $columns, $conn);
$sql = "SELECT u.nome as nome_usuario, a.modelo, a.tag 
        FROM ativos a 
        JOIN usuarios u ON a.assigned_to = u.id_usuarios 
        ORDER BY u.nome";
$pdf->generate($sql);
?>
