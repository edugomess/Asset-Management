<?php
/**
 * RELATÓRIO DE INCIDENTES POR ATIVO: relatorio_ativos_incidentes.php
 * Quantifica a quantidade de chamados vinculados a cada equipamento.
 * Útil para identificar ativos problemáticos ou que demandam substituição.
 */
require_once 'ReportGenerator.php';

$columns = [
    ['header' => 'Tag', 'width' => 30, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Modelo', 'width' => 68, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Fabricante', 'width' => 40, 'align' => 'L', 'field' => 'fabricante'],
    ['header' => 'Categoria', 'width' => 40, 'align' => 'C', 'field' => 'categoria'],
    ['header' => 'Qtd. Chamados', 'width' => 20, 'align' => 'C', 'field' => 'qtd']
];

// SQL que agrupa chamados por ativo, considerando tanto o id_asset direto quanto a service_tag
$sql = "SELECT 
            a.tag, 
            a.modelo, 
            a.fabricante, 
            a.categoria,
            COUNT(c.id) as qtd 
        FROM ativos a
        INNER JOIN chamados c ON (c.id_asset = a.id_asset OR (c.service_tag = a.tag AND c.service_tag != ''))
        GROUP BY a.id_asset
        ORDER BY qtd DESC";

$pdf = new ReportGenerator('Relatório de Incidentes por Ativo', $columns, $conn);
$pdf->generate($sql);
