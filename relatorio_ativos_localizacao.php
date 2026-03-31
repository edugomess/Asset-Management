<?php require_once 'ReportGenerator.php';

// Definição das colunas do relatório
$columns = [
    ['header' => 'TAG', 'width' => 25, 'align' => 'L', 'field' => 'tag'],
    ['header' => 'Modelo', 'width' => 45, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Fabricante', 'width' => 35, 'align' => 'L', 'field' => 'fabricante'],
    ['header' => 'Prédio / Unidade', 'width' => 40, 'align' => 'L', 'field' => 'predio_nome'],
    ['header' => 'Local / Sala', 'width' => 30, 'align' => 'L', 'field' => 'sala_nome'],
    ['header' => 'Status', 'width' => 23, 'align' => 'C', 'field' => 'status']
];

// Query SQL que reconstrói a hierarquia básica (Pai > Filho) para o relatório
$sql = "SELECT a.tag, a.modelo, a.fabricante, a.status, 
               COALESCE(p.nome_local, 'N/A') as predio_nome, 
               COALESCE(l.nome_local, 'Sem Local') as sala_nome
        FROM ativos a 
        LEFT JOIN locais l ON a.id_local = l.id_local
        LEFT JOIN locais p ON l.id_parent_local = p.id_local
        ORDER BY predio_nome, sala_nome, a.tag";

// Instancia o gerador e processa o relatório
$pdf = new ReportGenerator('Ativos por Localização (Prédio / Sala)', $columns, $conn);
$pdf->generate($sql, 'a.dataAtivacao');
