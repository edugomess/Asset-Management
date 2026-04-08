<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Nome', 'width' => 60, 'align' => 'L', 'field' => 'nome_completo'],
    ['header' => 'Setor', 'width' => 45, 'align' => 'L', 'field' => 'setor'],
    ['header' => 'Email', 'width' => 50, 'align' => 'L', 'field' => 'email'],
    ['header' => 'Função', 'width' => 40, 'align' => 'L', 'field' => 'funcao']
];

$pdf = new ReportGenerator('Relatório de Usuários Inativos', $columns, $conn);
$sql = "SELECT CONCAT(nome, ' ', sobrenome) as nome_completo, setor, email, funcao FROM usuarios WHERE status = 'Inativo' ORDER BY nome, sobrenome";
$pdf->generate($sql);
?>
