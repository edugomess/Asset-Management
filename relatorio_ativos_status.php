<?php
/**
 * RELATÓRIO DE ATIVOS POR STATUS: relatorio_ativos_status.php
 * Agrupamento de equipamentos por estado operacional (Em uso, Disponível, Manutenção, etc).
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
        $this->Cell(0, 10, utf8_to_iso88591(mb_strtoupper('Relatório de Ativos por Status')), 0, 1, 'C');
        
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
        $this->SetFillColor(200, 220, 255);
        $this->Cell(0, 10, utf8_to_iso88591("Status: $label"), 0, 1, 'L', true);
        $this->Ln(4);
    }
}

ob_start();
$pdf = new PDF();
$pdf->SetMargins(6, 6, 6);
$pdf->AddPage();

// Get unique statuses
$sql_status = "SELECT DISTINCT status FROM ativos ORDER BY status";
$result_status = $conn->query($sql_status);

if ($result_status->num_rows > 0) {
    while ($status_row = $result_status->fetch_assoc()) {
        $current_status = $status_row['status'];

        $pdf->ChapterTitle($current_status);

        // Table Header
        $pdf->SetFillColor(44, 64, 74);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(44, 7, utf8_to_iso88591('Modelo'), 1, 0, 'L', true);
        $pdf->Cell(30, 7, utf8_to_iso88591('Tag'), 1, 0, 'L', true);
        $pdf->Cell(30, 7, utf8_to_iso88591('Hostname'), 1, 0, 'L', true);
        $pdf->Cell(50, 7, utf8_to_iso88591('Centro de Custo'), 1, 0, 'L', true);
        $pdf->Cell(44, 7, utf8_to_iso88591('Usuário'), 1, 0, 'L', true);
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);

        // Get assets for this status
        $sql_assets = "SELECT * FROM ativos WHERE status = '$current_status'";
        $result_assets = $conn->query($sql_assets);

        $pdf->SetFont('Arial', '', 8);
        $count = 0;

        while ($row = $result_assets->fetch_assoc()) {
            $pdf->Cell(44, 7, utf8_to_iso88591(substr($row['modelo'], 0, 22)), 1);
            $pdf->Cell(30, 7, utf8_to_iso88591($row['tag']), 1);
            $pdf->Cell(30, 7, utf8_to_iso88591($row['hostName']), 1);
            $pdf->Cell(50, 7, utf8_to_iso88591(substr($row['centroDeCusto'], 0, 28)), 1);

            // Fetch user name if assigned
            $user_name = "-";
            if (!empty($row['assigned_to'])) {
                $uid = $row['assigned_to'];
                $u_res = $conn->query("SELECT nome FROM usuarios WHERE id_usuarios = $uid");
                if ($u_res && $u_row = $u_res->fetch_assoc()) {
                    $user_name = $u_row['nome'];
                }
            }
            $pdf->Cell(40, 7, utf8_to_iso88591(substr($user_name, 0, 22)), 1);

            $pdf->Ln();
            $count++;
        }
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell(0, 10, utf8_to_iso88591("Total de ativos neste status: $count"), 0, 1);
        $pdf->Ln(5);
    }
} else {
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, utf8_to_iso88591('Nenhum status encontrado.'), 0, 1);
}

$conn->close();
ob_end_clean();
$pdf->Output();
?>