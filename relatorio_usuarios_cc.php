<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Centro de Custo', 'width' => 45, 'align' => 'L', 'field' => 'centroDeCusto'],
    ['header' => 'Nome', 'width' => 50, 'align' => 'L', 'field' => 'nome_completo'],
    ['header' => 'Setor', 'width' => 35, 'align' => 'L', 'field' => 'setor'],
    ['header' => 'Email', 'width' => 45, 'align' => 'L', 'field' => 'email'],
    ['header' => 'Função', 'width' => 23, 'align' => 'L', 'field' => 'funcao']
];

$pdf = new ReportGenerator('Relatório de Usuários por Centro de Custo', $columns, $conn);
$sql = "SELECT centroDeCusto, CONCAT(nome, ' ', sobrenome) as nome_completo, setor, email, funcao FROM usuarios ORDER BY centroDeCusto, nome";
$pdf->generate($sql);
?>
