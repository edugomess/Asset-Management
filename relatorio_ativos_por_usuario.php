<?php
/**
 * RELATÓRIO DE ATIVOS POR USUÁRIO: relatorio_ativos_por_usuario.php
 * Lista todos os equipamentos atribuídos a colaboradores, ordenados alfabeticamente.
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'Usuário', 'width' => 40, 'align' => 'L', 'field' => 'nome_usuario'],
    ['header' => 'Modelo', 'width' => 50, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag']
];

$pdf = new ReportGenerator('Relatório de Ativos por Usuário', $columns, $conn);
$sql = "SELECT CONCAT(u.nome, ' ', u.sobrenome) as nome_usuario, a.modelo, a.tag 
        FROM ativos a 
        JOIN usuarios u ON a.assigned_to = u.id_usuarios 
        ORDER BY u.nome, u.sobrenome";
$pdf->generate($sql);
?>