<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Nome', 'width' => 60, 'align' => 'L', 'field' => 'nome_completo'],
    ['header' => 'Setor', 'width' => 40, 'align' => 'L', 'field' => 'setor'],
    ['header' => 'CC', 'width' => 25, 'align' => 'L', 'field' => 'centroDeCusto'],
    ['header' => 'Qtd Ativos', 'width' => 25, 'align' => 'C', 'field' => 'qtd_ativos'],
    ['header' => 'Valor Total', 'width' => 35, 'align' => 'R', 'field' => 'valor_total', 'format' => 'money']
];

$pdf = new ReportGenerator('Relatório de Usuários com Ativos', $columns, $conn);
$sql = "SELECT CONCAT(u.nome, ' ', u.sobrenome) as nome_completo, u.setor, u.centroDeCusto, COUNT(a.id_asset) as qtd_ativos, SUM(a.valor) as valor_total 
        FROM usuarios u 
        JOIN ativos a ON u.id_usuarios = a.assigned_to 
        GROUP BY u.id_usuarios 
        ORDER BY qtd_ativos DESC";
$pdf->generate($sql);
?>
