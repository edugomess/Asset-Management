<?php
/**
 * RELATÓRIO DE CHAMADOS FECHADOS: relatorio_chamados_fechados.php
 * Lista todos os tickets com status de conclusão (Resolvido/Fechado).
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'ID', 'width' => 12, 'align' => 'C', 'field' => 'id'],
    ['header' => 'Título', 'width' => 75, 'align' => 'L', 'field' => 'titulo'],
    ['header' => 'Data Abertura', 'width' => 30, 'align' => 'C', 'field' => 'data_abertura', 'format' => 'date'],
    ['header' => 'Status', 'width' => 28, 'align' => 'C', 'field' => 'status'],
    ['header' => 'Resolvido Por (Setor)', 'width' => 70, 'align' => 'L', 'field' => 'nome_tecnico']
];

$pdf = new ReportGenerator('Relatório de Chamados Fechados', $columns, $conn);
$sql = "SELECT c.id, c.titulo, c.data_abertura, c.status, CONCAT(u.nome, ' ', u.sobrenome, ' (', COALESCE(u.setor, '-'), ')') as nome_tecnico 
        FROM chamados c 
        LEFT JOIN usuarios u ON c.responsavel_id = u.id_usuarios 
        WHERE c.status IN ('Resolvido', 'Fechado', 'Cancelado') 
        ORDER BY c.data_abertura DESC";
$pdf->generate($sql, 'c.data_abertura');
