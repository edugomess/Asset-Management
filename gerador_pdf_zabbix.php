<?php
require 'conexao.php';
require 'fpdf.php';

// Filtros recebidos via GET
$empresa_id = isset($_GET['empresa']) ? (int) $_GET['empresa'] : 0;
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');

if ($empresa_id === 0) {
    die("Empresa inválida.");
}

// Buscar nome da empresa
$stmtEmp = $conn->prepare("SELECT nome FROM zabbix_empresas WHERE id = ?");
$stmtEmp->bind_param("i", $empresa_id);
$stmtEmp->execute();
$resEmp = $stmtEmp->get_result();
$empresa = $resEmp->fetch_assoc();
$stmtEmp->close();

if (!$empresa) {
    die("Empresa não encontrada.");
}

// Buscar histórico
$historico = [];
$sqlTable = "SELECT id, data_relatorio, alerta_nome, recorrencia, ai_sugestao, severidade, host_envolvido, ultima_ocorrencia, comentario_usuario, falso_positivo 
             FROM zabbix_historico_alertas 
             WHERE empresa_id = ? AND data_relatorio >= ? AND data_relatorio <= ?
             ORDER BY data_relatorio DESC, recorrencia DESC 
             LIMIT 100";
$stmt2 = $conn->prepare($sqlTable);
$stmt2->bind_param("iss", $empresa_id, $data_inicio, $data_fim);
$stmt2->execute();
$resTable = $stmt2->get_result();
while ($row = $resTable->fetch_assoc()) {
    $historico[] = $row;
}
$stmt2->close();

// Configuração FPDF Customizada
class PDF extends FPDF
{
    function Header()
    {
        // Título e logo fictício (usaremos texto para manter simples sem dependência de imagem local)
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(59, 130, 246); // Azul
        $this->Cell(0, 10, utf8_decode('Relatório de Analytics e Monitoramento'), 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, utf8_decode('Gerado automaticamente via Plataforma Unificada & Google Gemini'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . ' / {nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Dados do Relatório (Cabeçalho Secundário)
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(40, 8, 'Empresa:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, utf8_decode($empresa['nome']), 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 8, utf8_decode('Período:'), 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, date('d/m/Y', strtotime($data_inicio)) . ' a ' . date('d/m/Y', strtotime($data_fim)), 0, 1);
$pdf->Ln(5);

if (empty($historico)) {
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell(0, 10, 'Nenhum alerta registrado neste periodo.', 0, 1, 'C');
} else {
    foreach ($historico as $index => $item) {
        // Título do Alerta
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(240, 240, 240);
        $titulo = "#" . ($index + 1) . " - " . utf8_decode($item['alerta_nome']);
        if ($item['falso_positivo']) {
            $titulo .= " [FALSO POSITIVO]";
        }
        $pdf->Cell(0, 8, $titulo, 1, 1, 'L', true);
        
        // Metadados
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(30, 6, utf8_decode('Últ. Ocorrência:'), 'L', 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(50, 6, date('d/m/Y H:i', strtotime($item['ultima_ocorrencia'])), 0, 0);
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(30, 6, utf8_decode('Ocorrências:'), 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 6, $item['recorrencia'] . ' vezes', 'R', 1);
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(30, 6, 'Host:', 'L', 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(50, 6, utf8_decode($item['host_envolvido']), 0, 0);
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(30, 6, 'Severidade:', 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 6, utf8_decode($item['severidade']), 'R', 1);
        
        // Linha do tempo (Timestamps)
        if (!empty($item['lista_ocorrencias'])) {
            $timestamps = json_decode($item['lista_ocorrencias'], true);
            if (is_array($timestamps) && count($timestamps) > 0) {
                $timeStrings = array_map(function($ts) {
                    return date('d/m H:i', strtotime($ts));
                }, $timestamps);
                $linhaTempo = implode(', ', $timeStrings);
                
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetTextColor(80, 80, 80);
                $pdf->Cell(30, 6, utf8_decode('Histórico:'), 'L', 0);
                $pdf->SetFont('Arial', '', 8);
                $pdf->MultiCell(0, 6, $linhaTempo, 'R');
                $pdf->SetTextColor(0, 0, 0);
            }
        }
        
        // Dica da IA
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(15, 81, 50); // Verde escuro para a IA
        $pdf->Cell(0, 6, utf8_decode('Análise da IA (Google Gemini):'), 'LR', 1);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        
        // Remover tags HTML e converter asteriscos markdown
        $sugestao = strip_tags($item['ai_sugestao']);
        $sugestao = str_replace('**', '', $sugestao);
        $pdf->MultiCell(0, 5, utf8_decode($sugestao), 'LR');
        
        // Comentário (se houver)
        if (!empty($item['comentario_usuario'])) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 6, utf8_decode('Comentário da Equipe Técnica:'), 'LR', 1);
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->MultiCell(0, 5, utf8_decode($item['comentario_usuario']), 'LR');
            $pdf->SetTextColor(0, 0, 0);
        }

        // Borda inferior do bloco
        $pdf->Cell(0, 1, '', 'LBR', 1);
        $pdf->Ln(5);
    }
}

$pdf->Output('I', 'Relatorio_Monitoramento_' . $empresa['nome'] . '.pdf');
?>
