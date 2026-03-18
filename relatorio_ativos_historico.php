<?php
/**
 * RELATÓRIO DE HISTÓRICO DE ATIVOS: relatorio_ativos_historico.php
 * Trilha de auditoria das movimentações e alterações de ativos.
 */
require('ReportGenerator.php');

$columns = [
    ['header' => 'Data', 'width' => 45, 'align' => 'C', 'field' => 'data_evento', 'format' => 'date'],
    ['header' => 'Tag/Patrimônio', 'width' => 45, 'align' => 'L', 'field' => 'tag'],
    ['header' => 'Responsável', 'width' => 60, 'align' => 'L', 'field' => 'usuario'],
    ['header' => 'Ação', 'width' => 50, 'align' => 'L', 'field' => 'acao'],
    ['header' => 'Detalhes', 'width' => 77, 'align' => 'L', 'field' => 'detalhes']
];

$sql = "SELECT h.data_evento, a.tag, u.nome as usuario, h.acao, h.detalhes 
        FROM historico_ativos h 
        JOIN ativos a ON h.ativo_id = a.id_asset 
        JOIN usuarios u ON h.usuario_id = u.id_usuarios 
        ORDER BY h.data_evento DESC 
        LIMIT 100";

$pdf = new ReportGenerator('Relatório de Auditoria (Histórico Recente)', $columns, $conn, 'L');
$pdf->generate($sql);
?>
