<?php
/**
 * RELATÓRIO DE ATIVOS POR CENTRO DE CUSTO: relatorio_ativos_cc.php
 * Agrupa os ativos por unidade de negócio, exibindo subtotais e contagem por grupo.
 * UtilizaF PDF diretamente para permitir agrupamentos visuais complexos.
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
        $this->Cell(0, 10, utf8_to_iso88591(mb_strtoupper('Relatório de Ativos por Centro de Custo')), 0, 1, 'C');
        
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
        $this->Cell(0, 10, utf8_to_iso88591("Centro de Custo: $label"), 0, 1, 'L', true);
        $this->Ln(4);
    }
}

ob_start();
$pdf = new PDF();
$pdf->SetMargins(6, 6, 6);
$pdf->AddPage();

// Get unique Cost Centers
$sql_cc = "SELECT DISTINCT centroDeCusto FROM ativos WHERE centroDeCusto IS NOT NULL AND centroDeCusto != '' ORDER BY centroDeCusto";
$result_cc = $conn->query($sql_cc);

if ($result_cc->num_rows > 0) {
    while ($cc_row = $result_cc->fetch_assoc()) {
        $current_cc = $cc_row['centroDeCusto'];

        $pdf->ChapterTitle($current_cc);

        // Table Header
        $pdf->SetFillColor(44, 64, 74);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(58, 7, utf8_to_iso88591('Modelo'), 1, 0, 'L', true);
        $pdf->Cell(40, 7, utf8_to_iso88591('Tag'), 1, 0, 'L', true);
        $pdf->Cell(40, 7, utf8_to_iso88591('Status'), 1, 0, 'L', true);
        $pdf->Cell(30, 7, utf8_to_iso88591('Valor (R$)'), 1, 0, 'R', true);
        $pdf->Cell(30, 7, utf8_to_iso88591('Usuário'), 1, 0, 'L', true);
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);

        // Get assets for this CC
        $sql_assets = "SELECT * FROM ativos WHERE centroDeCusto = '$current_cc'";
        $result_assets = $conn->query($sql_assets);

        $pdf->SetFont('Arial', '', 8);
        $count = 0;
        $total_value = 0;

        while ($row = $result_assets->fetch_assoc()) {
            $pdf->Cell(58, 7, utf8_to_iso88591(substr($row['modelo'], 0, 28)), 1);
            $pdf->Cell(40, 7, utf8_to_iso88591($row['tag']), 1);
            $pdf->Cell(40, 7, utf8_to_iso88591($row['status']), 1);

            $val = $row['valor'];
            $total_value += $val;
            $pdf->Cell(30, 7, number_format($val, 2, ',', '.'), 1, 0, 'R');

            // Fetch user name if assigned
            $user_name = "-";
            if (!empty($row['assigned_to'])) {
                $uid = $row['assigned_to'];
                $u_res = $conn->query("SELECT nome FROM usuarios WHERE id_usuarios = $uid");
                if ($u_res && $u_row = $u_res->fetch_assoc()) {
                    $user_name = $u_row['nome'];
                }
            }
            $pdf->Cell(30, 7, utf8_to_iso88591(substr($user_name, 0, 15)), 1);

            $pdf->Ln();
            $count++;
        }
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(138, 7, utf8_to_iso88591("Total ($count ativos):"), 1, 0, 'R');
        $pdf->Cell(30, 7, number_format($total_value, 2, ',', '.'), 1, 1, 'R');
        $pdf->Ln(5);
    }
} else {
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, utf8_to_iso88591('Nenhum centro de custo encontrado.'), 0, 1);
}

$conn->close();
ob_end_clean();
$pdf->Output();
?>