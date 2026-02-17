<?php
require('fpdf/fpdf.php');
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
        // Logo
        $this->Image('dashboard/images/favicon.png', 10, 6, 15);

        // System Name
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80); // Move to right
        $this->Cell(30, 10, 'Asset MGT', 0, 0, 'C');
        $this->Ln(5);

        // Report Title
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_to_iso88591('Relatório Financeiro de Ativos'), 0, 1, 'C');
        $this->Ln(5);
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, utf8_to_iso88591('Gerado em: ' . date('d/m/Y H:i:s')), 0, 1, 'C');
        $this->Ln(10);

        // Table Header
        $this->SetFillColor(200, 255, 200);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(60, 10, utf8_to_iso88591('Categoria do Ativo'), 1, 0, 'L', true);
        $this->Cell(30, 10, utf8_to_iso88591('Qtd.'), 1, 0, 'C', true);
        $this->Cell(50, 10, utf8_to_iso88591('Valor Total (R$)'), 1, 0, 'R', true);
        $this->Cell(50, 10, utf8_to_iso88591('Valor Médio (R$)'), 1, 0, 'R', true);
        $this->Ln();
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

        $pdf->Cell(60, 10, utf8_to_iso88591(substr($cat, 0, 30)), 1, 0, 'L');
        $pdf->Cell(30, 10, $qtd, 1, 0, 'C');
        $pdf->Cell(50, 10, number_format($total, 2, ',', '.'), 1, 0, 'R');
        $pdf->Cell(50, 10, number_format($avg, 2, ',', '.'), 1, 0, 'R');
        $pdf->Ln();
    }

    // Grand Total Row
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 10, utf8_to_iso88591('TOTAIS GERAIS'), 1, 0, 'L');
    $pdf->Cell(30, 10, $grand_total_qtd, 1, 0, 'C');
    $pdf->Cell(50, 10, number_format($grand_total_val, 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(50, 10, '-', 1, 0, 'R');
    $pdf->Ln();


}
else {
    $pdf->Cell(0, 10, utf8_to_iso88591('Nenhum dado financeiro encontrado.'), 1, 1, 'C');
}

$conn->close();
ob_end_clean();
$pdf->Output();
?>
