<?php
/**
 * RELATÓRIO DE CHAMADOS POR SOLICITANTE: relatorio_chamados_solicitante.php
 * Quantifica o volume de pedidos de suporte por colaborador para identificar perfis de uso.
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'Solicitante (Setor)', 'width' => 110, 'align' => 'L', 'field' => 'nome_solicitante'],
    ['header' => 'Chamados', 'width' => 45, 'align' => 'C', 'field' => 'qtd']
];

$pdf = new ReportGenerator('Relatório de Chamados por Solicitante', $columns, $conn);
$sql = "SELECT CONCAT(u.nome, ' ', u.sobrenome, ' (', COALESCE(u.setor, '-'), ')') as nome_solicitante, COUNT(*) as qtd 
        FROM chamados c 
        JOIN usuarios u ON c.usuario_id = u.id_usuarios 
        GROUP BY u.nome, u.sobrenome, u.setor 
        ORDER BY qtd DESC";
$pdf->generate($sql);
