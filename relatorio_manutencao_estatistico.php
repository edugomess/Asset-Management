<?php
require('fpdf/fpdf.php');
require_once 'ReportGenerator.php';
include 'conexao.php';

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
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Gerado em: ' . date('d/m/Y H:i:s')), 0, 1, 'R');
        
        $this->Ln(-2);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', mb_strtoupper('Resumo Estatístico de Manutenções')), 0, 1, 'C');
        
        $this->Line(6, 23, 204, 23);
        $this->Ln(5);
    }
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->SetMargins(6, 6, 6);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Stats
$q_active = $conn->query("SELECT COUNT(*) as c FROM manutencao WHERE status_manutencao = 'Em Manutenção'")->fetch_assoc()['c'];
$q_done = $conn->query("SELECT COUNT(*) as c FROM manutencao WHERE status_manutencao = 'Concluído'")->fetch_assoc()['c'];
$q_avg = $conn->query("SELECT AVG(DATEDIFF(data_fim, data_inicio)) as a FROM manutencao WHERE status_manutencao = 'Concluído'")->fetch_assoc()['a'];

$pdf->Cell(108, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Manutenções em Aberto:'), 1);
$pdf->Cell(50, 10, $q_active, 1, 1, 'R');

$pdf->Cell(108, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Manutenções Concluídas:'), 1);
$pdf->Cell(50, 10, $q_done, 1, 1, 'R');

$pdf->Cell(108, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Tempo Médio de Reparo (Dias):'), 1);
$pdf->Cell(50, 10, number_format($q_avg, 1, ',', '.'), 1, 1, 'R');

$pdf->Output();
?>