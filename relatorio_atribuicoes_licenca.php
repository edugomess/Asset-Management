<?php
require('ReportGenerator.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("ID de licença inválido.");
}

// Buscar nome do software para o título
$sql_info = "SELECT software FROM licencas WHERE id_licenca = $id";
$res_info = mysqli_query($conn, $sql_info);
$row_info = mysqli_fetch_assoc($res_info);
$software = $row_info ? $row_info['software'] : 'Desconhecido';

$columns = [
    ['header' => 'Usuário', 'width' => 70, 'align' => 'L', 'field' => 'usuario'],
    ['header' => 'Email', 'width' => 80, 'align' => 'L', 'field' => 'email'],
    ['header' => 'Data de Atribuição', 'width' => 40, 'align' => 'C', 'field' => 'data_formatada']
];

// Instanciar o gerador com o título personalizado
$pdf = new ReportGenerator("Relatório de Atribuições: $software", $columns, $conn, 'P');

// Query para buscar os usuários atribuídos
$sql = "SELECT CONCAT(u.nome, ' ', u.sobrenome) as usuario, u.email, 
               DATE_FORMAT(al.data_atribuicao, '%d/%m/%Y %H:%i') as data_formatada
        FROM atribuicoes_licencas al 
        JOIN usuarios u ON al.id_usuario = u.id_usuarios 
        WHERE al.id_licenca = $id 
        ORDER BY u.nome ASC";

$pdf->generate($sql);
?>