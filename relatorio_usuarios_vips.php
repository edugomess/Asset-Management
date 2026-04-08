<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Nome', 'width' => 70, 'align' => 'L', 'field' => 'nome_completo'],
    ['header' => 'Setor', 'width' => 50, 'align' => 'L', 'field' => 'setor'],
    ['header' => 'Função', 'width' => 45, 'align' => 'L', 'field' => 'funcao'],
    ['header' => 'Email', 'width' => 33, 'align' => 'L', 'field' => 'email']
];

$pdf = new ReportGenerator('Relatório de Usuários VIPs', $columns, $conn);
// Assuming VIPs are Directors or Managers
$sql = "SELECT CONCAT(nome, ' ', sobrenome) as nome_completo, setor, funcao, email FROM usuarios WHERE funcao LIKE '%Diretor%' OR funcao LIKE '%Gerente%' ORDER BY nome";
$pdf->generate($sql);
?>
