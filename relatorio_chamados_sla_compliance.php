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
        $titulo = "Relatorio de Compliance de SLA";
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
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 10, 'ID', 1, 0, 'C', true);
$pdf->Cell(80, 10, 'Titulo', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'SLA Previsto', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Tempo Real', 1, 0, 'C', true);
$pdf->Cell(33, 10, 'Status', 1, 1, 'C', true);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 8);

$sql = "SELECT c.id, c.titulo, c.data_abertura, c.data_fechamento, c.prioridade, c.categoria, 
        COALESCE(c.tempo_congelado_minutos, 0) as congelado,
        (CASE WHEN s.tempo_sla_minutos IS NOT NULL THEN s.tempo_sla_minutos WHEN c.categoria = 'Incidente' THEN 360 WHEN c.categoria = 'Mudança' THEN 1440 WHEN c.categoria = 'Requisição' THEN 2880 ELSE 1440 END * 
         CASE WHEN c.prioridade = 'P1' THEN 1/6.0 WHEN c.prioridade = 'P2' OR c.prioridade = 'Alta' THEN 1/3.0 WHEN c.prioridade = 'P3' OR c.prioridade = 'Média' THEN 2/3.0 ELSE 1.0 END) as sla_minutos
        FROM chamados c
        LEFT JOIN configuracoes_sla s ON c.categoria = s.categoria
        WHERE c.status IN ('Resolvido', 'Fechado', 'Cancelado')
        ORDER BY c.data_fechamento DESC LIMIT 50";

$res = mysqli_query($conn, $sql);

if ($res && mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $tempo_real = (strtotime($row['data_fechamento']) - strtotime($row['data_abertura'])) / 60 - $row['congelado'];
        $sla_status = ($tempo_real <= $row['sla_minutos']) ? 'Dentro do Prazo' : 'Vencido';
        
        $pdf->Cell(15, 8, $row['id'], 1, 0, 'C');
        $pdf->Cell(80, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', mb_strimwidth($row['titulo'], 0, 45, '...')), 1);
        $pdf->Cell(35, 8, round($row['sla_minutos'] / 60, 1) . ' h', 1, 0, 'C');
        $pdf->Cell(35, 8, round($tempo_real / 60, 1) . ' h', 1, 0, 'C');
        
        if ($sla_status == 'Dentro do Prazo') {
            $pdf->SetFillColor(220, 255, 220);
        } else {
            $pdf->SetFillColor(255, 220, 220);
        }
        $pdf->Cell(33, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $sla_status), 1, 1, 'C', true);
        $pdf->SetFillColor(255, 255, 255);
    }
} else {
    $pdf->Cell(198, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Nenhum chamado finalizado encontrado.'), 1, 1, 'C');
}

$pdf->Output();
?>
