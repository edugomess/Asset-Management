<?php
/**
 * RELATÓRIO FINANCEIRO: relatorio_financeiro.php
 * Gera uma análise de custos detalhada por categoria de ativo.
 * Utiliza FPDF diretamente para customizações complexas de layout.
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
        $this->SetTextColor(44, 64, 74); // Cor primária do dashboard
        $this->Cell(15); // Espaço para o logo
        $this->Cell(100, 10, 'Asset MGT', 0, 0, 'L');
        
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, utf8_to_iso88591('Gerado em: ' . date('d/m/Y H:i:s')), 0, 1, 'R');
        
        $this->Ln(-2);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, utf8_to_iso88591(mb_strtoupper('Relatório Financeiro de Ativos')), 0, 1, 'C');
        
        $this->Line(6, 23, 204, 23); // Linha horizontal moderna
        $this->Ln(5);

        // Table Header
        $this->SetFillColor(44, 64, 74); // Cor padrão das tabelas
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(68, 10, utf8_to_iso88591('Categoria do Ativo'), 1, 0, 'L', true);
        $this->Cell(30, 10, utf8_to_iso88591('Qtd.'), 1, 0, 'C', true);
        $this->Cell(50, 10, utf8_to_iso88591('Valor Total (R$)'), 1, 0, 'R', true);
        $this->Cell(50, 10, utf8_to_iso88591('Valor Médio (R$)'), 1, 0, 'R', true);
        $this->Ln();
        $this->SetTextColor(0, 0, 0);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_to_iso88591('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

// SQL to aggregate value by category
$sql = "
    SELECT 
        categoria,
        COUNT(*) as quantidade,
        SUM(valor) as valor_total,
        AVG(valor) as valor_medio
    FROM ativos
    WHERE categoria IS NOT NULL AND categoria != ''
    GROUP BY categoria
    ORDER BY valor_total DESC
";

$result = $conn->query($sql);

ob_start();
$pdf = new PDF();
$pdf->SetMargins(6, 6, 6);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

$grand_total_qtd = 0;
$grand_total_val = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cat = $row['categoria'];
        $qtd = $row['quantidade'];
        $total = $row['valor_total'];
        $avg = $row['valor_medio'];

        $grand_total_qtd += $qtd;
        $grand_total_val += $total;

        $pdf->Cell(68, 10, utf8_to_iso88591(substr($cat, 0, 30)), 1, 0, 'L');
        $pdf->Cell(30, 10, $qtd, 1, 0, 'C');
        $pdf->Cell(50, 10, number_format($total, 2, ',', '.'), 1, 0, 'R');
        $pdf->Cell(50, 10, number_format($avg, 2, ',', '.'), 1, 0, 'R');
        $pdf->Ln();
    }

    // Grand Total Row
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(68, 10, utf8_to_iso88591('TOTAIS GERAIS'), 1, 0, 'L');
    $pdf->Cell(30, 10, $grand_total_qtd, 1, 0, 'C');
    $pdf->Cell(50, 10, number_format($grand_total_val, 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(50, 10, '-', 1, 0, 'R');
    $pdf->Ln();


} else {
    $pdf->Cell(0, 10, utf8_to_iso88591('Nenhum dado financeiro encontrado.'), 1, 1, 'C');
}

$conn->close();
ob_end_clean();
$pdf->Output();
