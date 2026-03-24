<?php
/**
 * RELATÓRIO DE CHAMADOS POR TÉCNICO: relatorio_chamados_tecnico.php
 * Avalia a distribuição de carga de trabalho entre os membros da equipe de TI.
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'Técnico', 'width' => 80, 'align' => 'L', 'field' => 'nome_tecnico'],
    ['header' => 'Chamados Atribuídos', 'width' => 60, 'align' => 'C', 'field' => 'qtd']
];

$pdf = new ReportGenerator('Relatório de Chamados por Técnico', $columns, $conn);
$sql = "SELECT CONCAT(u.nome, ' ', u.sobrenome) as nome_tecnico, COUNT(*) as qtd 
        FROM chamados c 
        JOIN usuarios u ON c.responsavel_id = u.id_usuarios 
        GROUP BY u.nome, u.sobrenome 
        ORDER BY qtd DESC";
$pdf->generate($sql);
