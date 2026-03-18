<?php
/**
 * RELATÓRIO MENSAL DE CHAMADOS: relatorio_chamados_mensal.php
 * Análise histórica de volumetria de tickets e taxa de resolução por mês.
 * UtilizaF PDF diretamente para permitir cálculos de porcentagem no corpo do relatório.
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

if (isset($_GET['format']) && $_GET['format'] == 'xlsx') {
    $filename = "Relatorio_Mensal_Chamados_" . date('Y-m-d_H-i-s') . ".xls";
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=$filename");
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    echo "<table border='1'><thead><tr>
            <th style='background-color: #c8dcff;'>Mês/Ano</th>
            <th style='background-color: #c8dcff;'>Total Abertos</th>
            <th style='background-color: #c8dcff;'>Total Fechados</th>
            <th style='background-color: #c8dcff;'>Taxa de Resolução</th>
          </tr></thead><tbody>";

    $sql = "SELECT DATE_FORMAT(data_abertura, '%Y-%m') as mes_ano, COUNT(*) as total_chamados, SUM(CASE WHEN status IN ('Resolvido', 'Fechado', 'Cancelado') THEN 1 ELSE 0 END) as total_fechados FROM chamados GROUP BY mes_ano ORDER BY mes_ano DESC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dateObj = DateTime::createFromFormat('!Y-m', $row['mes_ano']);
            $mes_formatado = $dateObj->format('m/Y');
            $taxa = ($row['total_chamados'] > 0) ? ($row['total_fechados'] / $row['total_chamados']) * 100 : 0;
            echo "<tr><td>$mes_formatado</td><td>{$row['total_chamados']}</td><td>{$row['total_fechados']}</td><td>" . number_format($taxa, 1) . "%</td></tr>";
        }
    } else {
        echo "<tr><td colspan='4'>Nenhum registro encontrado.</td></tr>";
    }
    echo "</tbody></table>";
    exit;
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
} else {
    $pdf->Cell(0, 10, utf8_to_iso88591('Nenhum registro de chamados encontrado.'), 1, 1, 'C');
}

$conn->close();
ob_end_clean();
$pdf->Output();
