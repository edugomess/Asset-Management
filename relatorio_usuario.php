<?php
/**
 * RELATÓRIO DE USUÁRIOS: relatorio_usuario.php
 * Cadastro geral de colaboradores com acesso ao sistema ou ativos vinculados.
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'Nome', 'width' => 50, 'align' => 'L', 'field' => 'nome_completo'],
    ['header' => 'Setor', 'width' => 35, 'align' => 'L', 'field' => 'setor'],
    ['header' => 'CC', 'width' => 20, 'align' => 'L', 'field' => 'centroDeCusto'],
    ['header' => 'Função', 'width' => 35, 'align' => 'L', 'field' => 'funcao'],
    ['header' => 'Unidade', 'width' => 25, 'align' => 'L', 'field' => 'unidade'],
    ['header' => 'Email', 'width' => 33, 'align' => 'L', 'field' => 'email']
];

$pdf = new ReportGenerator('Relatório de Usuários (Lista Geral)', $columns, $conn);
$sql = "SELECT CONCAT(nome, ' ', sobrenome) as nome_completo, setor, centroDeCusto, funcao, unidade, email FROM usuarios ORDER BY nome, sobrenome";
$pdf->generate($sql);
?>