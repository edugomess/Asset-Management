<?php
/**
 * RELATÓRIO DE CHAMADOS FECHADOS: relatorio_chamados_fechados.php
 * Lista todos os tickets com status de conclusão (Resolvido/Fechado).
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'ID', 'width' => 15, 'align' => 'C', 'field' => 'id'],
    ['header' => 'Título', 'width' => 60, 'align' => 'L', 'field' => 'titulo'],
    ['header' => 'Data Abertura', 'width' => 40, 'align' => 'C', 'field' => 'data_abertura', 'format' => 'date'],
    ['header' => 'Status', 'width' => 30, 'align' => 'C', 'field' => 'status'],
    ['header' => 'Resolvido Por', 'width' => 45, 'align' => 'L', 'field' => 'nome_tecnico']
];

$pdf = new ReportGenerator('Relatório de Chamados Fechados', $columns, $conn);
$sql = "SELECT c.id, c.titulo, c.data_abertura, c.status, u.nome as nome_tecnico 
        FROM chamados c 
        LEFT JOIN usuarios u ON c.responsavel_id = u.id_usuarios 
        WHERE c.status IN ('Resolvido', 'Fechado', 'Cancelado') 
        ORDER BY c.data_abertura DESC";
$pdf->generate($sql);
