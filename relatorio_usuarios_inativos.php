<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Nome', 'width' => 60, 'align' => 'L', 'field' => 'nome'],
    ['header' => 'Email', 'width' => 60, 'align' => 'L', 'field' => 'email'],
    ['header' => 'Função', 'width' => 40, 'align' => 'L', 'field' => 'funcao'],
    ['header' => 'Status', 'width' => 30, 'align' => 'C', 'field' => 'status']
];

$pdf = new ReportGenerator('Relatório de Usuários Inativos', $columns, $conn);
$sql = "SELECT nome, email, funcao, status FROM usuarios WHERE status = 'Inativo' ORDER BY nome";
$pdf->generate($sql);
?>
