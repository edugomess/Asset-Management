<?php
require('fpdf/fpdf.php');
include 'conexao.php';

class PDF extends FPDF
{
    function Header()
    {
        // Logo
        $this->Image('dashboard/images/favicon.png', 10, 6, 15);

        // System Name
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80); // Move to right
        $this->Cell(30, 10, 'Asset MGT', 0, 0, 'C');
        $this->Ln(5);

        // Report Title
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Relatório Resumo Geral do Sistema'), 0, 1, 'C');
        $this->Ln(10);
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Counts
$q_users = $conn->query("SELECT COUNT(*) as c FROM usuarios")->fetch_assoc()['c'];
$q_assets = $conn->query("SELECT COUNT(*) as c FROM ativos")->fetch_assoc()['c'];
$q_maint = $conn->query("SELECT COUNT(*) as c FROM manutencao WHERE status_manutencao = 'Em Manutenção'")->fetch_assoc()['c'];
$q_tickets = $conn->query("SELECT COUNT(*) as c FROM chamados")->fetch_assoc()['c'];
$q_suppliers = $conn->query("SELECT COUNT(*) as c FROM fornecedor")->fetch_assoc()['c'];
$q_cc = $conn->query("SELECT COUNT(*) as c FROM centro_de_custo")->fetch_assoc()['c'];
$q_lic = $conn->query("SELECT COUNT(*) as c FROM licencas")->fetch_assoc()['c'];
$val_assets = $conn->query("SELECT SUM(valor) as v FROM ativos")->fetch_assoc()['v'];
$val_lic = $conn->query("SELECT SUM(quantidade_total * valor_unitario) as v FROM licencas")->fetch_assoc()['v'];

$pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Total de Usuários:'), 1);
$pdf->Cell(50, 10, $q_users, 1, 1, 'R');

$pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Total de Ativos:'), 1);
$pdf->Cell(50, 10, $q_assets, 1, 1, 'R');

$pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Valor Total do Patrimônio (Ativos):'), 1);
$pdf->Cell(50, 10, 'R$ ' . number_format($val_assets, 2, ',', '.'), 1, 1, 'R');

$pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Ativos em Manutenção:'), 1);
$pdf->Cell(50, 10, $q_maint, 1, 1, 'R');

$pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Total de Licenças (Seats):'), 1);
$pdf->Cell(50, 10, $q_lic, 1, 1, 'R');

$pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Valor Total de Licenças:'), 1);
$pdf->Cell(50, 10, 'R$ ' . number_format($val_lic, 2, ',', '.'), 1, 1, 'R');

$pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Total de Chamados:'), 1);
$pdf->Cell(50, 10, $q_tickets, 1, 1, 'R');

$pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Total de Fornecedores:'), 1);
$pdf->Cell(50, 10, $q_suppliers, 1, 1, 'R');

$pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Total de Centros de Custo:'), 1);
$pdf->Cell(50, 10, $q_cc, 1, 1, 'R');

$pdf->Output();
?>