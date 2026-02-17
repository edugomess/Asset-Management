<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Nome', 'width' => 50, 'align' => 'L', 'field' => 'nome'],
    ['header' => 'Qtd Ativos', 'width' => 30, 'align' => 'C', 'field' => 'qtd_ativos'],
    ['header' => 'Valor Total', 'width' => 40, 'align' => 'R', 'field' => 'valor_total', 'format' => 'money']
];

$pdf = new ReportGenerator('Relatório de Usuários com Ativos', $columns, $conn);
$sql = "SELECT u.nome, COUNT(a.id_asset) as qtd_ativos, SUM(a.valor) as valor_total 
        FROM usuarios u 
        JOIN ativos a ON u.id_usuarios = a.assigned_to 
        GROUP BY u.id_usuarios 
        ORDER BY qtd_ativos DESC";
$pdf->generate($sql);
?>
