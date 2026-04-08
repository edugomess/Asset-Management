<?php
/**
 * RELATÓRIO DE LEILÕES REALIZADOS: relatorio_leiloes_realizados.php
 * Lista histórico de ativos que já foram a leilão.
 */
require_once 'ReportGenerator.php';
include_once 'conexao.php';

$columns = [
    ['header' => 'Data Leilão', 'width' => 25, 'align' => 'C', 'field' => 'data_venda', 'format' => 'date'],
    ['header' => 'Tag', 'width' => 20, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Modelo', 'width' => 50, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Categoria', 'width' => 30, 'align' => 'L', 'field' => 'categoria'],
    ['header' => 'Centro de Custo', 'width' => 35, 'align' => 'L', 'field' => 'centroDeCusto'],
    ['header' => 'Valor Original', 'width' => 38, 'align' => 'R', 'field' => 'valor', 'format' => 'money']
];

$pdf = new ReportGenerator('Relatório de Leilões Realizados (Histórico)', $columns, $conn, 'P');
$sql = "SELECT * FROM venda WHERE status = 'Leiloado' ORDER BY data_venda DESC";
$pdf->generate($sql, 'data_venda');
?>
