<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Centro de Custo', 'width' => 60, 'align' => 'L', 'field' => 'centroDeCusto'],
    ['header' => 'Nome', 'width' => 50, 'align' => 'L', 'field' => 'nome'],
    ['header' => 'Email', 'width' => 50, 'align' => 'L', 'field' => 'email'],
    ['header' => 'Função', 'width' => 30, 'align' => 'L', 'field' => 'funcao']
];

$pdf = new ReportGenerator('Relatório de Usuários por Centro de Custo', $columns, $conn);
$sql = "SELECT centroDeCusto, nome, email, funcao FROM usuarios ORDER BY centroDeCusto, nome";
$pdf->generate($sql);
?>
