<?php
/**
 * RELATÓRIO DE INVESTIMENTO POR FORNECEDOR: relatorio_compras_fornecedor.php
 * Consolida gastos com Ativos e Licenças para uma visão de compras por fornecedor.
 */
require('fpdf/fpdf.php');
require_once 'ReportGenerator.php';
include 'conexao.php';

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

function utf8_to_iso88591($text)
{
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
}

class PDF extends FPDF
{
    function Header()
    {
        // Logo dinâmico
        $logo = ReportGenerator::getLogoPath($GLOBALS['conn']);
        $this->Image($logo, 6, 6, 12);
        
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(44, 64, 74);
        $this->Cell(15);
        $this->Cell(100, 10, 'Asset MGT', 0, 0, 'L');
        
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, utf8_to_iso88591('Gerado em: ' . date('d/m/Y H:i:s')), 0, 1, 'R');
        
        $this->Ln(-2);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, utf8_to_iso88591(mb_strtoupper('Relatório de Investimento por Fornecedor (Consolidado)')), 0, 1, 'C');
        
        $this->Line(6, 23, 204, 23);
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_to_iso88591('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

ob_start();
$pdf = new PDF();
$pdf->SetMargins(6, 6, 6);
$pdf->AddPage();

// Table Header
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(44, 64, 74);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(88, 10, utf8_to_iso88591('Fornecedor'), 1, 0, 'L', true);
$pdf->Cell(35, 10, utf8_to_iso88591('Ativos (R$)'), 1, 0, 'R', true);
$pdf->Cell(35, 10, utf8_to_iso88591('Licenças (R$)'), 1, 0, 'R', true);
$pdf->Cell(40, 10, utf8_to_iso88591('Total (R$)'), 1, 1, 'R', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 10);

// Fetch all unique suppliers from both tables
$suppliers = [];

$res1 = $conn->query("SELECT DISTINCT fornecedor FROM ativos WHERE fornecedor IS NOT NULL AND fornecedor != ''");
while($r = $res1->fetch_assoc()) $suppliers[$r['fornecedor']] = true;

$res2 = $conn->query("SELECT DISTINCT fornecedor FROM licencas WHERE fornecedor IS NOT NULL AND fornecedor != ''");
while($r = $res2->fetch_assoc()) $suppliers[$r['fornecedor']] = true;

ksort($suppliers);

$grand_total_ativos = 0;
$grand_total_licencas = 0;

foreach ($suppliers as $name => $val) {
    // Total Assets for this supplier
    $res_a = $conn->query("SELECT SUM(valor) as total FROM ativos WHERE fornecedor = '$name'");
    $total_a = $res_a->fetch_assoc()['total'] ?? 0;

    // Total Licenses for this supplier
    $res_l = $conn->query("SELECT SUM(quantidade_total * valor_unitario) as total FROM licencas WHERE fornecedor = '$name'");
    $total_l = $res_l->fetch_assoc()['total'] ?? 0;

    $total_row = $total_a + $total_l;
    
    $grand_total_ativos += $total_a;
    $grand_total_licencas += $total_l;

    $pdf->Cell(88, 8, utf8_to_iso88591($name), 1);
    $pdf->Cell(35, 8, number_format($total_a, 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(35, 8, number_format($total_l, 2, ',', '.'), 1, 0, 'R');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 8, number_format($total_row, 2, ',', '.'), 1, 1, 'R');
    $pdf->SetFont('Arial', '', 10);
}

// Grand Totals
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(88, 10, utf8_to_iso88591('TOTAL GERAL'), 1, 0, 'L', true);
$pdf->Cell(35, 10, number_format($grand_total_ativos, 2, ',', '.'), 1, 0, 'R', true);
$pdf->Cell(35, 10, number_format($grand_total_licencas, 2, ',', '.'), 1, 0, 'R', true);
$pdf->Cell(40, 10, number_format($grand_total_ativos + $grand_total_licencas, 2, ',', '.'), 1, 1, 'R', true);

$conn->close();
ob_end_clean();
$pdf->Output();
?>
