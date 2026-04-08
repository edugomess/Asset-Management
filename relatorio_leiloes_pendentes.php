<?php
/**
 * RELATÓRIO DE LEILÕES PENDENTES: relatorio_leiloes_pendentes.php
 * Lista ativos 100% depreciados e designados para leilão.
 */
require_once 'ReportGenerator.php';
include_once 'conexao.php';

// Busca configurações de depreciação globais
$result_dep = mysqli_query($conn, "SELECT * FROM configuracoes_depreciacao LIMIT 1");
$dep_config = mysqli_fetch_assoc($result_dep);

if (!$dep_config || !intval($dep_config['elegivel_leilao'])) {
    die("Relatório indisponível: Política de leilão desativada nas configurações.");
}

$columns = [
    ['header' => 'Tag', 'width' => 20, 'align' => 'C', 'field' => 'tag'],
    ['header' => 'Modelo', 'width' => 45, 'align' => 'L', 'field' => 'modelo'],
    ['header' => 'Categoria', 'width' => 25, 'align' => 'L', 'field' => 'categoria'],
    ['header' => 'CC', 'width' => 25, 'align' => 'L', 'field' => 'centroDeCusto'],
    ['header' => 'Data Ativ.', 'width' => 25, 'align' => 'C', 'field' => 'dataAtivacao', 'format' => 'date'],
    ['header' => 'Vlr. Orig.', 'width' => 28, 'align' => 'R', 'field' => 'valor', 'format' => 'money'],
    ['header' => 'Lance Sug.', 'width' => 30, 'align' => 'R', 'field' => 'lance_sugerido', 'format' => 'money']
];

$pdf = new ReportGenerator('Relatório de Ativos Elegíveis para Leilão', $columns, $conn, 'P');

// Precisamos filtrar em PHP para manter a lógica de depreciação complexa
$sql = "SELECT a.* FROM ativos a";
$res = mysqli_query($conn, $sql);

$periodo_meses_depreciacao = (intval($dep_config['periodo_anos']) * 12) + intval($dep_config['periodo_meses']);
$mapDestinacoes = [
    'Tier 1' => $dep_config['destinacao_tier1'],
    'Tier 2' => $dep_config['destinacao_tier2'],
    'Tier 3' => $dep_config['destinacao_tier3'],
    'Tier 4' => $dep_config['destinacao_tier4'],
    'Infraestrutura' => $dep_config['destinacao_infraestrutura']
];

$eligible_data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $tier = $row['tier'] ?? '';
    if (($mapDestinacoes[$tier] ?? '') !== 'Leilão') continue;

    $v_original = floatval($row['valor']);
    $dataAtivacao = $row['dataAtivacao'];
    if (empty($dataAtivacao) || $v_original <= 0) continue;

    $dat_atv = new DateTime($dataAtivacao);
    $dat_cur = new DateTime();
    $meses_atv = ($dat_atv->diff($dat_cur)->y * 12) + $dat_atv->diff($dat_cur)->m;

    $taxa_pct = floatval($dep_config['taxa_depreciacao']);
    if ($tier === 'Tier 1') {
        $taxa_pct = floatval($dep_config['taxa_tier1']);
    } elseif ($tier === 'Tier 2') {
        $taxa_pct = floatval($dep_config['taxa_tier2']);
    } elseif ($tier === 'Tier 3') {
        $taxa_pct = floatval($dep_config['taxa_tier3']);
    } elseif ($tier === 'Tier 4') {
        $taxa_pct = floatval($dep_config['taxa_tier4']);
    } elseif ($tier === 'Infraestrutura') {
        $taxa_pct = floatval($dep_config['taxa_infraestrutura']);
    }

    $periodos_comp = floor($meses_atv / $periodo_meses_depreciacao);
    $dep_total = min($v_original, $v_original * ($taxa_pct / 100) * $periodos_comp);
    
    if (($dep_total / $v_original) >= 1.0) {
        $row['lance_sugerido'] = $v_original * 0.10;
        $eligible_data[] = $row;
    }
}

// O ReportGenerator original só aceita SQL. Vamos adaptar para aceitar dados ou criar um novo método temporário.
// Como não posso alterar o ReportGenerator facilmente sem quebrar outros, vou simular o comportamento.
$pdf->AddPage();
$pdf->SetFont('Arial', '', 8);

if (!empty($eligible_data)) {
    foreach ($eligible_data as $row) {
        foreach ($columns as $col) {
            $val = $row[$col['field']] ?? '';
            if (isset($col['format'])) {
                if ($col['format'] == 'money') $val = 'R$ ' . number_format($val, 2, ',', '.');
                elseif ($col['format'] == 'date') $val = date('d/m/Y', strtotime($val));
            }
            $pdf->Cell($col['width'], 7, $pdf->utf8_to_iso88591($val), 1, 0, $col['align']);
        }
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 10, $pdf->utf8_to_iso88591('Nenhum ativo elegível encontrado.'), 1, 1, 'C');
}

$pdf->Output();
?>
