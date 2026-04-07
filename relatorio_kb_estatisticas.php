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
        $titulo = "Relatorio de Utilidade - Base de Conhecimento";
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
$pdf->Cell(120, 10, 'Artigo', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Categoria', 1, 0, 'C', true);
$pdf->Cell(19, 10, 'Util', 1, 0, 'C', true);
$pdf->Cell(19, 10, 'Nao', 1, 1, 'C', true);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 9);

$sql = "SELECT titulo, categoria, votos_positivos, votos_negativos 
        FROM base_conhecimento 
        ORDER BY votos_positivos DESC, votos_negativos ASC";

$res = mysqli_query($conn, $sql);

if ($res && mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $titulo = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', mb_strimwidth($row['titulo'], 0, 60, '...'));
        $cat = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $row['categoria']);
        
        $pdf->Cell(120, 10, $titulo, 1);
        $pdf->Cell(40, 10, $cat, 1, 0, 'C');
        $pdf->Cell(19, 10, $row['votos_positivos'], 1, 0, 'C');
        $pdf->Cell(19, 10, $row['votos_negativos'], 1, 1, 'C');
    }
} else {
    $pdf->Cell(198, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Nenhum artigo encontrado.'), 1, 1, 'C');
}

$pdf->Output();
?>
