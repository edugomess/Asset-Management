<?php
require('fpdf/fpdf.php');
require_once 'ReportGenerator.php';
include 'conexao.php';

class PDF extends FPDF
{
    function Header()
    {
        $logo = ReportGenerator::getLogoPath($GLOBALS['conn']);
        $this->Image($logo, 6, 6, 12);
        
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(44, 64, 74);
        $this->Cell(15);
        $this->Cell(100, 10, 'Asset MGT', 0, 0, 'L');
        $this->Ln(-2);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $titulo = "Inventario de Smartphones e Tablets";
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', mb_strtoupper($titulo)), 0, 1, 'C');
        
        $this->Line(6, 23, 204, 23);
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->SetMargins(6, 6, 6);
$pdf->AliasNbPages();
$pdf->AddPage();

// Cabeçalho da Tabela
$pdf->SetFillColor(44, 64, 74);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(20, 10, 'Tag', 1, 0, 'C', true);
$pdf->Cell(55, 10, 'Dispositivo', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'IMEI', 1, 0, 'C', true);
$pdf->Cell(45, 10, 'SIM Card', 1, 0, 'C', true);
$pdf->Cell(28, 10, 'Status', 1, 1, 'C', true);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 8);

$sql = "SELECT tag, modelo, imei, sim_card, status 
        FROM ativos 
        WHERE categoria IN ('Smartphone', 'Tablet', 'Celular', 'Mobile')
        OR imei IS NOT NULL
        ORDER BY modelo";

$res = mysqli_query($conn, $sql);

if ($res && mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $pdf->Cell(20, 10, $row['tag'], 1, 0, 'C');
        $pdf->Cell(55, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', mb_strimwidth($row['modelo'], 0, 35, '...')), 1);
        $pdf->Cell(50, 10, $row['imei'] ?? '-', 1, 0, 'C');
        $pdf->Cell(45, 10, $row['sim_card'] ?? '-', 1, 0, 'C');
        $pdf->Cell(28, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $row['status']), 1, 1, 'C');
    }
} else {
    $pdf->Cell(198, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Nenhum dispositivo movel encontrado.'), 1, 1, 'C');
}

$pdf->Output();
?>
