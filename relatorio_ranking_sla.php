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
        $titulo = "Ranking de SLA - " . $meses[$mes] . " / " . $ano;
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
$pdf->Cell(80, 10, 'Tecnico', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Resolvidos', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'No Prazo', 1, 0, 'C', true);
$pdf->Cell(30, 10, '% SLA', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);

$sql_ranking = "SELECT 
    r.nome, r.sobrenome,
    COUNT(*) as total,
    SUM(CASE WHEN (TIMESTAMPDIFF(MINUTE, c.data_abertura, c.data_fechamento) - COALESCE(c.tempo_congelado_minutos, 0)) <= 
        CASE 
            WHEN s.tempo_sla_minutos IS NOT NULL THEN s.tempo_sla_minutos
            WHEN c.categoria = 'Incidente' THEN 360
            WHEN c.categoria = 'Mudança' THEN 1440
            WHEN c.categoria = 'Requisição' THEN 2880
            ELSE 1440 
        END THEN 1 ELSE 0 END) as met_sla
FROM chamados c
JOIN usuarios r ON c.responsavel_id = r.id_usuarios
LEFT JOIN configuracoes_sla s ON c.categoria = s.categoria
WHERE c.status IN ('Resolvido', 'Fechado', 'Cancelado')
AND MONTH(c.data_fechamento) = $mes 
AND YEAR(c.data_fechamento) = $ano
GROUP BY r.id_usuarios
ORDER BY (SUM(CASE WHEN (TIMESTAMPDIFF(MINUTE, c.data_abertura, c.data_fechamento) - COALESCE(c.tempo_congelado_minutos, 0)) <= 
    CASE 
        WHEN s.tempo_sla_minutos IS NOT NULL THEN s.tempo_sla_minutos
        WHEN c.categoria = 'Incidente' THEN 360
        WHEN c.categoria = 'Mudança' THEN 1440
        WHEN c.categoria = 'Requisição' THEN 2880
        ELSE 1440 
    END THEN 1 ELSE 0 END) / COUNT(*)) DESC";

$res = mysqli_query($conn, $sql_ranking);

if (mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $nome = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $row['nome'] . ' ' . $row['sobrenome']);
        $total = $row['total'];
        $met = $row['met_sla'];
        $pct = round(($met / $total) * 100) . '%';

        $pdf->Cell(80, 10, $nome, 1);
        $pdf->Cell(40, 10, $total, 1, 0, 'C');
        $pdf->Cell(40, 10, $met, 1, 0, 'C');
        $pdf->Cell(30, 10, $pct, 1, 1, 'C');
    }
} else {
    $pdf->Cell(190, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Nenhum dado encontrado para este periodo.'), 1, 1, 'C');
}

$pdf->Output();
?>