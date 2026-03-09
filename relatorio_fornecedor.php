<?php
/**
 * RELATÓRIO DE FORNECEDORES: relatorio_fornecedor.php
 * Listagem analítica de todos os parceiros e prestadores de serviço.
 */
require('ReportGenerator.php');

// Definição das colunas do relatório
$columns = [
    ['header' => 'Empresa', 'width' => 60, 'align' => 'L', 'field' => 'nomeEmpresa'],
    ['header' => 'Serviço', 'width' => 60, 'align' => 'L', 'field' => 'servico'],
    ['header' => 'E-mail', 'width' => 70, 'align' => 'L', 'field' => 'email']
];

// Instanciação e geração do relatório
$pdf = new ReportGenerator('Relatório de Fornecedores', $columns, $conn);
$sql = "SELECT nomeEmpresa, servico, email FROM fornecedor ORDER BY nomeEmpresa";
$pdf->generate($sql);
?>

// Fecha a conexão com o banco de dados
$conn->close();

// Gera o PDF no navegador
$pdf->Output();
?>