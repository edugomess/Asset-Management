<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Nome', 'width' => 60, 'align' => 'L', 'field' => 'nome_completo'],
    ['header' => 'Setor', 'width' => 45, 'align' => 'L', 'field' => 'setor'],
    ['header' => 'Email', 'width' => 45, 'align' => 'L', 'field' => 'email'],
    ['header' => 'CC', 'width' => 35, 'align' => 'L', 'field' => 'centroDeCusto']
];

$pdf = new ReportGenerator('Relatório de Usuários Sem Ativos', $columns, $conn);
$sql = "SELECT CONCAT(u.nome, ' ', u.sobrenome) as nome_completo, u.setor, u.email, u.centroDeCusto 
        FROM usuarios u 
        LEFT JOIN ativos a ON u.id_usuarios = a.assigned_to 
        WHERE a.id_asset IS NULL 
        ORDER BY u.nome";
$pdf->generate($sql);
?>
