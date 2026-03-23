<?php
/**
 * RELATÓRIO DE APROVAÇÕES DE GESTORES: relatorio_chamados_aprovacoes.php
 * Rastreia chamados que exigem autorização (Requisição/Mudança) e seu status.
 */
require_once 'ReportGenerator.php';

$columns = [
    ['header' => 'ID', 'width' => 15, 'align' => 'C', 'field' => 'id'],
    ['header' => 'Título', 'width' => 68, 'align' => 'L', 'field' => 'titulo'],
    ['header' => 'Categoria', 'width' => 25, 'align' => 'C', 'field' => 'categoria'],
    ['header' => 'Solicitante', 'width' => 35, 'align' => 'L', 'field' => 'solicitante'],
    ['header' => 'Gestor', 'width' => 35, 'align' => 'L', 'field' => 'gestor'],
    ['header' => 'Status Aprovação', 'width' => 20, 'align' => 'C', 'field' => 'status_aprov']
];

$sql = "SELECT 
            c.id, 
            c.titulo, 
            c.categoria, 
            CONCAT(u.nome, ' ', u.sobrenome) as solicitante, 
            CONCAT(g.nome, ' ', g.sobrenome) as gestor, 
            IF(c.aprovado_gestor=1, 'Aprovado', 'Pendente') as status_aprov 
        FROM chamados c
        LEFT JOIN usuarios u ON c.usuario_id = u.id_usuarios
        LEFT JOIN usuarios g ON c.id_gestor_aprovador = g.id_usuarios
        WHERE c.categoria IN ('Requisição', 'Mudança')
        ORDER BY c.id DESC";

$pdf = new ReportGenerator('Relatório de Aprovações de Gestores', $columns, $conn);
$pdf->generate($sql);
