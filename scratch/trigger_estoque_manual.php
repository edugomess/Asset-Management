<?php
require_once 'conexao.php';

echo "--- ANALISANDO ESTOQUE PARA DISPARO DE ALERTAS ---\n\n";

// 1. Buscar configurações
$conf = $conn->query("SELECT * FROM configuracoes_alertas WHERE id = 1")->fetch_assoc();
if (!$conf) die("Configurações não encontradas.\n");

// 2. Buscar itens disponíveis
$sql = "SELECT tier, categoria, COUNT(*) as qtd 
        FROM ativos 
        WHERE status = 'Disponível' 
        GROUP BY tier, categoria";
$res = $conn->query($sql);

$itensCriticos = [];

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $t = $row['tier'];
        $threshold = 3;
        
        if ($t == 'Tier 1') $threshold = (int)$conf['estoque_threshold_t1'];
        elseif ($t == 'Tier 2') $threshold = (int)$conf['estoque_threshold_t2'];
        elseif ($t == 'Tier 3') $threshold = (int)$conf['estoque_threshold_t3'];
        elseif ($t == 'Tier 4') $threshold = (int)$conf['estoque_threshold_t4'];
        elseif ($t == 'Infraestrutura') $threshold = (int)$conf['estoque_threshold_inf'];

        if ($row['qtd'] <= $threshold) {
             echo " - CRÍTICO: " . $row['categoria'] . " (" . ($row['tier'] ?: 'Geral') . ") - Qtd: " . $row['qtd'] . " (Limite: $threshold)\n";
             $itensCriticos[] = $row;
        }
    }
}

if (!empty($itensCriticos)) {
    echo "\nEnviando alertas para " . count($itensCriticos) . " itens...\n";
    
    // Chamando o processador de background via CLI para simular o fluxo real
    $json = json_encode($itensCriticos);
    // IMPORTANTE: Escapar aspas para o comando shell
    $jsonEscaped = addslashes($json);
    
    $cmd = "C:\\xampp\\php\\php.exe processar_alertas.php estoque \"$jsonEscaped\"";
    echo "Executando: $cmd\n";
    $result = shell_exec($cmd);
    echo "Saída do processador: " . ($result ?: "Vazia/Sucesso") . "\n";
    
    echo "\n[SUCESSO] Alertas disparados.";
} else {
    echo "\n[INFO] Nenhum item atingiu o limite crítico de estoque no momento.\n";
    echo "Deseja forçar um envio de teste? (Considere diminuir um limite em Configurações para testar o fluxo real).\n";
}
?>
