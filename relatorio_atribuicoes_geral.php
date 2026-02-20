<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Software', 'width' => 50, 'align' => 'L', 'field' => 'software'],
    ['header' => 'Usuário', 'width' => 55, 'align' => 'L', 'field' => 'usuario'],
    ['header' => 'Email', 'width' => 50, 'align' => 'L', 'field' => 'email'],
    ['header' => 'Data', 'width' => 35, 'align' => 'C', 'field' => 'data_formatada']
];

$pdf = new ReportGenerator('Relatório Geral de Atribuições de Licenças', $columns, $conn, 'P');

$sql = "SELECT l.software, CONCAT(u.nome, ' ', u.sobrenome) as usuario, u.email, 
               DATE_FORMAT(al.data_atribuicao, '%d/%m/%Y %H:%i') as data_formatada
        FROM atribuicoes_licencas al 
        JOIN licencas l ON al.id_licenca = l.id_licenca 
        JOIN usuarios u ON al.id_usuario = u.id_usuarios 
        ORDER BY l.software ASC, u.nome ASC";

$pdf->generate($sql);
?>