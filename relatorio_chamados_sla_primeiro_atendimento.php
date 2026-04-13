<?php
/**
 * RELATÓRIO: SLA de Primeiro Atendimento
 * Exibe o tempo entre abertura e primeiro contato técnico para todos os chamados.
 * Suporta filtro de período via ?start=YYYY-MM-DD&end=YYYY-MM-DD
 */
ob_start(); // Captura qualquer output espúrio (avisos PHP) antes do PDF

require('fpdf/fpdf.php');
require_once 'ReportGenerator.php';
include 'conexao.php';

// ─── Filtros de período ───────────────────────────────────────────────────────
$start_raw = isset($_GET['start']) && !empty($_GET['start']) ? $_GET['start'] : date('Y-m-01'); // Primeiro dia do mês atual
$end_raw   = isset($_GET['end'])   && !empty($_GET['end'])   ? $_GET['end']   : date('Y-m-d');   // Hoje

// Validação básica das datas
$start = date('Y-m-d', strtotime($start_raw));
$end   = date('Y-m-d', strtotime($end_raw));
if ($end < $start) $end = $start;

$start_fmt = date('d/m/Y', strtotime($start));
$end_fmt   = date('d/m/Y', strtotime($end));
$periodo_label = "Periodo: $start_fmt a $end_fmt";

// ─── SLA alvo de primeiro atendimento ────────────────────────────────────────
$sla_pr_min = 10; // padrão
@$conn->query("ALTER TABLE configuracoes_sla ADD COLUMN IF NOT EXISTS sla_primeira_resposta_minutos INT NOT NULL DEFAULT 10");
$res_sla = $conn->query("SELECT sla_primeira_resposta_minutos FROM configuracoes_sla LIMIT 1");
if ($res_sla && $res_sla->num_rows > 0) {
    $row_sla = $res_sla->fetch_assoc();
    $sla_pr_min = (int)($row_sla['sla_primeira_resposta_minutos'] ?? 10) ?: 10;
}

// ─── Classe PDF ───────────────────────────────────────────────────────────────
class PDF extends FPDF
{
    function Header()
    {
        global $sla_pr_min, $periodo_label;
        $logo = ReportGenerator::getLogoPath($GLOBALS['conn']);
        $this->Image($logo, 6, 6, 12);

        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(44, 64, 74);
        $this->Cell(15);
        $this->Cell(100, 10, 'Asset MGT', 0, 0, 'L');
        $this->Ln(6);
        $this->SetFont('Arial', 'B', 11);
        $titulo = "Relatorio de SLA de Primeiro Atendimento";
        $this->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $titulo), 0, 1, 'C');
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $periodo_label), 0, 1, 'C');
        $meta_txt = "Meta de primeiro atendimento: $sla_pr_min minutos";
        $this->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $meta_txt), 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
        $this->Line(6, $this->GetY() + 2, 204, $this->GetY() + 2);
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->SetMargins(6, 6, 6);
$pdf->AliasNbPages();
$pdf->AddPage('L'); // Landscape

// ─── Cabeçalho da tabela ─────────────────────────────────────────────────────
$pdf->SetFillColor(44, 64, 74);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15,  9, 'ID',              1, 0, 'C', true);
$pdf->Cell(70,  9, 'Titulo',          1, 0, 'C', true);
$pdf->Cell(30,  9, 'Categoria',       1, 0, 'C', true);
$pdf->Cell(20,  9, 'Prioridade',      1, 0, 'C', true);
$pdf->Cell(32,  9, 'Aberto em',       1, 0, 'C', true);
$pdf->Cell(32,  9, '1a Resposta',     1, 0, 'C', true);
$pdf->Cell(22,  9, 'Tempo Resp.',     1, 0, 'C', true);
$pdf->Cell(22,  9, 'Meta (min)',      1, 0, 'C', true);
$pdf->Cell(28,  9, 'Status SLA',      1, 1, 'C', true);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 7.5);

// ─── Query com filtro de período ─────────────────────────────────────────────
$start_sql = mysqli_real_escape_string($conn, $start . ' 00:00:00');
$end_sql   = mysqli_real_escape_string($conn, $end   . ' 23:59:59');

$sql = "SELECT 
            c.id, c.titulo, c.categoria, c.prioridade,
            c.data_abertura, c.data_primeira_resposta, c.status
        FROM chamados c
        WHERE c.data_abertura BETWEEN '$start_sql' AND '$end_sql'
        ORDER BY c.data_abertura DESC
        LIMIT 1000";

$res = mysqli_query($conn, $sql);

$total    = 0;
$dentro   = 0;
$fora     = 0;
$sem_resp = 0;

if ($res && mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $titulo     = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', mb_strimwidth($row['titulo'], 0, 38, '...'));
        $categoria  = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $row['categoria']);
        $prioridade = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $row['prioridade'] ?: '-');
        $abertura   = date('d/m/Y H:i', strtotime($row['data_abertura']));
        $tempo_fmt  = '-';
        $resposta   = '-';
        $status_sla = '-';

        if (!empty($row['data_primeira_resposta'])) {
            $total++; 
            $ts_aber  = strtotime($row['data_abertura']);
            $ts_resp  = strtotime($row['data_primeira_resposta']);
            $diff_min = round(($ts_resp - $ts_aber) / 60);
            $resposta = date('d/m/Y H:i', $ts_resp);

            if ($diff_min >= 60) {
                $tempo_fmt = floor($diff_min / 60) . 'h ' . ($diff_min % 60) . 'min';
            } else {
                $tempo_fmt = $diff_min . ' min';
            }

            if ($diff_min <= $sla_pr_min) {
                $status_sla = 'Dentro Prazo';
                $pdf->SetFillColor(220, 255, 220); // Verde
                $dentro++;
            } else {
                $status_sla = 'Fora do Prazo';
                $pdf->SetFillColor(255, 220, 220); // Vermelho
                $fora++;
            }
        } else {
            // Sem resposta: verificar se ainda está aberto
            if (!in_array($row['status'], ['Resolvido', 'Fechado', 'Cancelado'])) {
                $total++;
                $sem_resp++;
                $status_sla = 'Sem Resposta';
                $pdf->SetFillColor(255, 252, 191); // Amarelo
            } else {
                continue; // Ignora se foi fechado sem resposta
            }
        }

        $pdf->Cell(15,  8, $row['id'],                                                      1, 0, 'C');
        $pdf->Cell(70,  8, $titulo,                                                         1, 0, 'L');
        $pdf->Cell(30,  8, $categoria,                                                      1, 0, 'C');
        $pdf->Cell(20,  8, $prioridade,                                                     1, 0, 'C');
        $pdf->Cell(32,  8, $abertura,                                                       1, 0, 'C');
        $pdf->Cell(32,  8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $resposta),              1, 0, 'C');
        $pdf->Cell(22,  8, $tempo_fmt,                                                      1, 0, 'C');
        $pdf->Cell(22,  8, "$sla_pr_min min",                                              1, 0, 'C');
        $pdf->Cell(28,  8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $status_sla),            1, 1, 'C', true);
        $pdf->SetFillColor(255, 255, 255);
    }

    // ─── Rodapé com resumo ────────────────────────────────────────────────────
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(234, 236, 244);

    $pct_dentro = $total > 0 ? round(($dentro / $total) * 100) : 0;
    $pct_fora   = $total > 0 ? round(($fora   / $total) * 100) : 0;
    $pct_sem    = $total > 0 ? round(($sem_resp / $total) * 100) : 0;

    $pdf->Cell(67, 9, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "Total Monitorados: $total"),              1, 0, 'C', true);
    $pdf->Cell(68, 9, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "Dentro: $dentro ({$pct_dentro}%)"),        1, 0, 'C', true);
    $pdf->Cell(68, 9, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "Fora: $fora ({$pct_fora}%)"),              1, 0, 'C', true);
    $pdf->Cell(68, 9, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "Sem Resposta: $sem_resp ({$pct_sem}%)"),   1, 1, 'C', true);

} else {
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Nenhum chamado encontrado para o periodo selecionado.'), 1, 1, 'C');
}

ob_end_clean(); // Descarta qualquer output espúrio antes de enviar o PDF
$pdf->Output();
?>
