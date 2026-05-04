<?php
include 'conexao.php';
$res = $conn->query("SHOW TABLES");
$tables = [];
$corrupted = [];
$ok = [];

// Fallback manual table list if SHOW TABLES fails
$manual_tables = ['alertas_usuarios', 'ativos', 'atribuicoes', 'atribuicoes_licencas', 'base_conhecimento', 'categoria', 'categoria_doacao', 'centro_de_custo', 'chamados', 'chat_grupo_membros', 'chat_grupos', 'chat_mensagens', 'configuracoes_alertas', 'configuracoes_depreciacao', 'configuracoes_sla', 'configuracoes_smtp', 'destinatarios_alertas', 'fornecedor', 'historico_ativos', 'licencas', 'locais', 'lotes_leilao', 'manutencao', 'sugestoes_prevencao', 'unidade', 'usuarios', 'venda'];

foreach ($manual_tables as $t) {
    try {
        if (!$conn->query("SELECT 1 FROM `$t` LIMIT 1")) {
            $corrupted[] = $t;
        } else {
            $ok[] = $t;
        }
    } catch(Exception $e) {
        $corrupted[] = $t;
    }
}
echo "CORRUPTED:\n" . implode("\n", $corrupted) . "\n\n";
echo "OK:\n" . implode("\n", $ok) . "\n";
?>
