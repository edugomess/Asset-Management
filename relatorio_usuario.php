<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Nome', 'width' => 50, 'align' => 'L', 'field' => 'nome'],
    ['header' => 'Setor/C.C.', 'width' => 40, 'align' => 'L', 'field' => 'centroDeCusto'],
    ['header' => 'Função', 'width' => 40, 'align' => 'L', 'field' => 'funcao'],
    ['header' => 'Unidade', 'width' => 30, 'align' => 'L', 'field' => 'unidade'],
    ['header' => 'Email', 'width' => 30, 'align' => 'L', 'field' => 'email']
];

$pdf = new ReportGenerator('Relatório de Usuários (Lista Geral)', $columns, $conn);
$sql = "SELECT nome, centroDeCusto, funcao, unidade, email FROM usuarios ORDER BY nome";
$pdf->generate($sql);
?>
