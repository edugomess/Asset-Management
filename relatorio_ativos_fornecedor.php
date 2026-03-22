<?php
/**
 * RELATÓRIO DE ATIVOS POR FORNECEDOR: relatorio_ativos_fornecedor.php
 * Agrupa os ativos por fornecedor, exibindo subtotais e contagem por grupo.
 */
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
        $this->Cell(0, 10, utf8_to_iso88591('Relatório de Ativos por Fornecedor'), 0, 1, 'C');
        $this->Ln(5);
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, utf8_to_iso88591('Gerado em: ' . date('d/m/Y H:i:s')), 0, 1, 'C');
        $this->Ln(10);
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
$pdf->AddPage();

// Get unique Suppliers
$sql_for = "SELECT DISTINCT fornecedor FROM ativos WHERE fornecedor IS NOT NULL AND fornecedor != '' ORDER BY fornecedor";
$result_for = $conn->query($sql_for);

if ($result_for && $result_for->num_rows > 0) {
    while ($for_row = $result_for->fetch_assoc()) {
        $current_for = $for_row['fornecedor'];

        $pdf->ChapterTitle($current_for);

        // Table Header
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(50, 7, utf8_to_iso88591('Modelo'), 1);
        $pdf->Cell(40, 7, utf8_to_iso88591('Tag'), 1);
        $pdf->Cell(40, 7, utf8_to_iso88591('Status'), 1);
        $pdf->Cell(30, 7, utf8_to_iso88591('Valor (R$)'), 1);
        $pdf->Cell(30, 7, utf8_to_iso88591('Setor (CC)'), 1);
        $pdf->Ln();

        // Get assets for this Supplier
        $sql_assets = "SELECT * FROM ativos WHERE fornecedor = '$current_for'";
        $result_assets = $conn->query($sql_assets);

        $pdf->SetFont('Arial', '', 8);
        $count = 0;
        $total_value = 0;

        while ($row = $result_assets->fetch_assoc()) {
            $pdf->Cell(50, 7, utf8_to_iso88591(substr($row['modelo'], 0, 28)), 1);
            $pdf->Cell(40, 7, utf8_to_iso88591($row['tag']), 1);
            $pdf->Cell(40, 7, utf8_to_iso88591($row['status']), 1);

            $val = $row['valor'];
            $total_value += $val;
            $pdf->Cell(30, 7, number_format($val, 2, ',', '.'), 1, 0, 'R');
            
            $cc = !empty($row['centroDeCusto']) ? $row['centroDeCusto'] : '-';
            $pdf->Cell(30, 7, utf8_to_iso88591(substr($cc, 0, 15)), 1);

            $pdf->Ln();
            $count++;
        }
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(130, 7, utf8_to_iso88591("Total ($count ativos):"), 1, 0, 'R');
        $pdf->Cell(30, 7, number_format($total_value, 2, ',', '.'), 1, 1, 'R');
        $pdf->Ln(5);
    }
} else {
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, utf8_to_iso88591('Nenhum ativo com fornecedor encontrado.'), 0, 1);
}

$conn->close();
ob_end_clean();
$pdf->Output();
?>
