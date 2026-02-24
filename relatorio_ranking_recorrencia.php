<?php
require('fpdf/fpdf.php');
include 'conexao.php';

$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');
$ano = isset($_GET['ano']) ? intval($_GET['ano']) : date('Y');

$meses = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

class PDF extends FPDF
{
    function Header()
    {
        global $mes, $ano, $meses;
        $this->Image('dashboard/images/favicon.png', 10, 6, 15);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 10, 'Asset MGT', 0, 0, 'C');
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 14);
        $titulo = "Ranking de Recorrencia - " . $meses[$mes] . " / " . $ano;
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $titulo), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 10);

// Cabeçalho da Tabela
$pdf->SetFillColor(234, 236, 244);
$pdf->Cell(150, 10, 'Problema / Titulo', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Frequencia', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);

$sql_recorrencia = "SELECT titulo, COUNT(*) as total 
                    FROM chamados 
                    WHERE MONTH(data_abertura) = $mes 
                    AND YEAR(data_abertura) = $ano
                    GROUP BY titulo 
                    ORDER BY total DESC LIMIT 10";

$res = mysqli_query($conn, $sql_recorrencia);

if (mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $titulo = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $row['titulo']);
        $total = $row['total'];

        $pdf->Cell(150, 10, $titulo, 1);
        $pdf->Cell(40, 10, $total, 1, 1, 'C');
    }
} else {
    $pdf->Cell(190, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Nenhum dado encontrado para este periodo.'), 1, 1, 'C');
}

$pdf->Output();
?>