<?php
require('fpdf/fpdf.php');
include 'conexao.php';

class PDF extends FPDF
{
    function Header()
    {
        $this->Image('dashboard/images/favicon.png', 10, 6, 15);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 10, 'Asset MGT', 0, 0, 'C');
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Resumo Estatístico de Manutenções'), 0, 1, 'C');
        $this->Ln(10);
    }
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Stats
$q_active = $conn->query("SELECT COUNT(*) as c FROM manutencao WHERE status_manutencao = 'Em Manutenção'")->fetch_assoc()['c'];
$q_done = $conn->query("SELECT COUNT(*) as c FROM manutencao WHERE status_manutencao = 'Concluído'")->fetch_assoc()['c'];
$q_avg = $conn->query("SELECT AVG(DATEDIFF(data_fim, data_inicio)) as a FROM manutencao WHERE status_manutencao = 'Concluído'")->fetch_assoc()['a'];

$pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Manutenções em Aberto:'), 1);
$pdf->Cell(50, 10, $q_active, 1, 1, 'R');

$pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Manutenções Concluídas:'), 1);
$pdf->Cell(50, 10, $q_done, 1, 1, 'R');

$pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Tempo Médio de Reparo (Dias):'), 1);
$pdf->Cell(50, 10, number_format($q_avg, 1, ',', '.'), 1, 1, 'R');

$pdf->Output();
?>