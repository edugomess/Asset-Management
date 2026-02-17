<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Empresa', 'width' => 60, 'align' => 'L', 'field' => 'nomeEmpresa'],
    ['header' => 'Email', 'width' => 60, 'align' => 'L', 'field' => 'email'],
    ['header' => 'Serviços', 'width' => 60, 'align' => 'L', 'field' => 'servico']
];

$pdf = new ReportGenerator('Relatório de Lista de Fornecedores', $columns, $conn);
// Assuming table is fornecedor (singular) based on earlier context search_backend.php
$sql = "SELECT nomeEmpresa, email, servico FROM fornecedor ORDER BY nomeEmpresa";
$pdf->generate($sql);
?>
