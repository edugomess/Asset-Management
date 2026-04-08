<?php
require 'conexao.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $taxa = 10;
    $taxa_t1 = 10;
    $taxa_t2 = 10;
    $taxa_t3 = 10;
    $taxa_t4 = 10;
    $taxa_inf = 50;
    $periodo_anos = 0;
    $periodo_meses = 3;
    $elegivel = 1;
    $doacao_anos = 0;
    $doacao_meses = 5;
    $dest_t1 = 'Doação';
    $dest_t2 = 'Doação';
    $dest_t3 = 'Doação';
    $dest_t4 = 'Doação';
    $dest_inf = 'Leilão';
    $elegivel_leilao = 1;

    $row_dep = ['id' => 1];

    $sql_dep = "UPDATE configuracoes_depreciacao SET 
        taxa_depreciacao = $taxa, 
        taxa_tier1 = $taxa_t1,
        taxa_tier2 = $taxa_t2,
        taxa_tier3 = $taxa_t3,
        taxa_tier4 = $taxa_t4,
        taxa_infraestrutura = $taxa_inf,
        periodo_anos = $periodo_anos, 
        periodo_meses = $periodo_meses, 
        elegivel_doacao = $elegivel, 
        tempo_doacao_anos = $doacao_anos, 
        tempo_doacao_meses = $doacao_meses,
        destinacao_tier1 = '$dest_t1',
        destinacao_tier2 = '$dest_t2',
        destinacao_tier3 = '$dest_t3',
        destinacao_tier4 = '$dest_t4',
        destinacao_infraestrutura = '$dest_inf',
        elegivel_leilao = $elegivel_leilao
        WHERE id = " . $row_dep['id'];

    $conn->query($sql_dep);
    echo "OK";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
