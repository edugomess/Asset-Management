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
        $this->Cell(0, 10, utf8_to_iso88591('Relatório Mensal de Chamados'), 0, 1, 'C');
        $this->Ln(5);
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, utf8_to_iso88591('Gerado em: ' . date('d/m/Y H:i:s')), 0, 1, 'C');
        $this->Ln(10);

        // Table Header
        $this->SetFillColor(200, 220, 255);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(60, 10, utf8_to_iso88591('Mês/Ano'), 1, 0, 'C', true);
        $this->Cell(40, 10, utf8_to_iso88591('Total Abertos'), 1, 0, 'C', true);
        $this->Cell(40, 10, utf8_to_iso88591('Total Fechados'), 1, 0, 'C', true);
        $this->Cell(50, 10, utf8_to_iso88591('Taxa de Resolução'), 1, 0, 'C', true);
        $this->Ln();
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_to_iso88591('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

// SQL to get counts grouped by month
// We need to parse data_abertura. Format in DB is usually YYYY-MM-DD
// Status 'Fechado' or 'Resolvido' counts as closed.

$sql = "
    SELECT 
        DATE_FORMAT(data_abertura, '%Y-%m') as mes_ano,
        COUNT(*) as total_chamados,
        SUM(CASE WHEN status IN ('Resolvido', 'Fechado', 'Cancelado') THEN 1 ELSE 0 END) as total_fechados
    FROM chamados
    GROUP BY mes_ano
    ORDER BY mes_ano DESC
";

$result = $conn->query($sql);

ob_start();
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mes_ano = $row['mes_ano'];
        $total = $row['total_chamados'];
        $fechados = $row['total_fechados'];

        // Formatting Month/Year (e.g., 2023-10 to Out/2023)
        $dateObj = DateTime::createFromFormat('!Y-m', $mes_ano);
        $mes_formatado = $dateObj->format('m/Y');

        $taxa = 0;
        if ($total > 0) {
            $taxa = ($fechados / $total) * 100;
        }

        $pdf->Cell(60, 10, $mes_formatado, 1, 0, 'C');
        $pdf->Cell(40, 10, $total, 1, 0, 'C');
        $pdf->Cell(40, 10, $fechados, 1, 0, 'C');
        $pdf->Cell(50, 10, number_format($taxa, 1) . '%', 1, 0, 'C');
        $pdf->Ln();
    }
}
else {
    $pdf->Cell(0, 10, utf8_to_iso88591('Nenhum registro de chamados encontrado.'), 1, 1, 'C');
}

$conn->close();
ob_end_clean();
$pdf->Output();
