<?php
/**
 * RELATÓRIO DE USUÁRIOS: relatorio_usuario.php
 * Cadastro geral de colaboradores com acesso ao sistema ou ativos vinculados.
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'Nome', 'width' => 40, 'align' => 'L', 'field' => 'nome'],
    ['header' => 'CC', 'width' => 30, 'align' => 'L', 'field' => 'centroDeCusto'],
    ['header' => 'Setor', 'width' => 30, 'align' => 'L', 'field' => 'setor'],
    ['header' => 'Função', 'width' => 35, 'align' => 'L', 'field' => 'funcao'],
    ['header' => 'Unidade', 'width' => 25, 'align' => 'L', 'field' => 'unidade'],
    ['header' => 'Email', 'width' => 38, 'align' => 'L', 'field' => 'email']
];

$pdf = new ReportGenerator('Relatório de Usuários (Lista Geral)', $columns, $conn);
$sql = "SELECT CONCAT(nome, ' ', sobrenome) as nome, centroDeCusto, setor, funcao, unidade, email FROM usuarios ORDER BY nome, sobrenome";
$pdf->generate($sql);
?>