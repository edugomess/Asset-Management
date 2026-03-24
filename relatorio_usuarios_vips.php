<?php
require('ReportGenerator.php');

$columns = [
    ['header' => 'Nome (Setor)', 'width' => 85, 'align' => 'L', 'field' => 'nome'],
    ['header' => 'Função', 'width' => 50, 'align' => 'L', 'field' => 'funcao'],
    ['header' => 'Email', 'width' => 60, 'align' => 'L', 'field' => 'email']
];

$pdf = new ReportGenerator('Relatório de Usuários VIPs', $columns, $conn);
// Assuming VIPs are Directors or Managers
$sql = "SELECT CONCAT(nome, ' ', sobrenome, ' (', COALESCE(setor, '-'), ')') as nome, funcao, email FROM usuarios WHERE funcao LIKE '%Diretor%' OR funcao LIKE '%Gerente%' ORDER BY nome";
$pdf->generate($sql);
?>
