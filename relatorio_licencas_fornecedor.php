<?php
/**
 * RELATÓRIO DE LICENÇAS POR FORNECEDOR: relatorio_licencas_fornecedor.php
 * Agrupa as licenças por fornecedor, exibindo subtotais e contagem de seats.
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
        $this->Cell(0, 10, utf8_to_iso88591(mb_strtoupper('Relatório de Licenças por Fornecedor')), 0, 1, 'C');
        
        $this->Line(6, 23, 204, 23);
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_to_iso88591('Página ') . $this->PageNo(), 0, 0, 'C');
    }

    function ChapterTitle($label)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(0, 10, utf8_to_iso88591("Fornecedor: $label"), 0, 1, 'L', true);
        $this->Ln(4);
    }
}

ob_start();
$pdf = new PDF();
$pdf->SetMargins(6, 6, 6);
$pdf->AddPage();

// Get unique Suppliers from licenses
$sql_for = "SELECT DISTINCT fornecedor FROM licencas WHERE fornecedor IS NOT NULL AND fornecedor != '' ORDER BY fornecedor";
$result_for = $conn->query($sql_for);

if ($result_for && $result_for->num_rows > 0) {
    while ($for_row = $result_for->fetch_assoc()) {
        $current_for = $for_row['fornecedor'];

        $pdf->ChapterTitle($current_for);

        // Table Header
        $pdf->SetFillColor(44, 64, 74);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(68, 7, utf8_to_iso88591('Software'), 1, 0, 'L', true);
        $pdf->Cell(30, 7, utf8_to_iso88591('Seats'), 1, 0, 'C', true);
        $pdf->Cell(30, 7, utf8_to_iso88591('Valor Un.'), 1, 0, 'R', true);
        $pdf->Cell(35, 7, utf8_to_iso88591('Vencimento'), 1, 0, 'C', true);
        $pdf->Cell(35, 7, utf8_to_iso88591('Total (R$)'), 1, 0, 'R', true);
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);

        // Get licenses for this Supplier
        $sql_li = "SELECT * FROM licencas WHERE fornecedor = '$current_for'";
        $result_li = $conn->query($sql_li);

        $pdf->SetFont('Arial', '', 8);
        $count = 0;
        $supplier_total = 0;

        while ($row = $result_li->fetch_assoc()) {
            $pdf->Cell(68, 7, utf8_to_iso88591(substr($row['software'], 0, 35)), 1);
            $pdf->Cell(30, 7, $row['quantidade_total'], 1, 0, 'C');
            $pdf->Cell(30, 7, number_format($row['valor_unitario'], 2, ',', '.'), 1, 0, 'R');
            
            $exp = !empty($row['data_expiracao']) ? date('d/m/Y', strtotime($row['data_expiracao'])) : 'Vitalícia';
            $pdf->Cell(35, 7, utf8_to_iso88591($exp), 1, 0, 'C');

            $row_total = $row['quantidade_total'] * $row['valor_unitario'];
            $supplier_total += $row_total;
            $pdf->Cell(35, 7, number_format($row_total, 2, ',', '.'), 1, 0, 'R');

            $pdf->Ln();
            $count++;
        }
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(163, 7, utf8_to_iso88591("Investimento com este fornecedor:"), 1, 0, 'R');
        $pdf->Cell(35, 7, number_format($supplier_total, 2, ',', '.'), 1, 1, 'R');
        $pdf->Ln(5);
    }
} else {
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, utf8_to_iso88591('Nenhuma licença com fornecedor encontrada.'), 0, 1);
}

$conn->close();
ob_end_clean();
$pdf->Output();
?>
