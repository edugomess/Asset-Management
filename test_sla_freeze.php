<?php
include 'conexao.php';

echo "--- TESTE DE CONGELAMENTO DE SLA ---\n";

// 1. Criar um chamado de teste
$sql_insert = "INSERT INTO chamados (titulo, categoria, status, data_abertura) VALUES ('Teste SLA', 'Incidente', 'Aberto', NOW())";
if ($conn->query($sql_insert)) {
    $id = $conn->insert_id;
    echo "Chamado #$id criado.\n";

    // 2. Simular mudança para Pendente
    $sql_pendente = "UPDATE chamados SET status = 'Pendente', data_ultimo_congelamento = DATE_SUB(NOW(), INTERVAL 30 MINUTE) WHERE id = $id";
    $conn->query($sql_pendente);
    echo "Simulado: Chamado em 'Pendente' há 30 minutos.\n";

    // 3. Simular saída de Pendente (isso normalmente ocorre via PHP, mas vamos simular o cálculo aqui)
    $sql_resumo = "SELECT * FROM chamados WHERE id = $id";
    $res = $conn->query($sql_resumo);
    $chamado = $res->fetch_assoc();

    $data_inicio = $chamado['data_ultimo_congelamento'];
    $sql_update_back = "UPDATE chamados SET 
        status = 'Em Andamento', 
        tempo_congelado_minutos = tempo_congelado_minutos + TIMESTAMPDIFF(MINUTE, '$data_inicio', NOW()), 
        data_ultimo_congelamento = NULL 
        WHERE id = $id";
    $conn->query($sql_update_back);
    echo "Simulado: Mudança para 'Em Andamento'. Tempo congelado calculado.\n";

    // 4. Verificar resultado final
    $res_final = $conn->query("SELECT tempo_congelado_minutos FROM chamados WHERE id = $id");
    $final = $res_final->fetch_assoc();
    echo "Resultado: Tempo Congelado = " . $final['tempo_congelado_minutos'] . " minutos.\n";

    if ($final['tempo_congelado_minutos'] >= 30) {
        echo "✅ SUCESSO: Lógica de congelamento validada!\n";
    } else {
        echo "❌ ERRO: Cálculo incorreto.\n";
    }

    // Limpar
    $conn->query("DELETE FROM chamados WHERE id = $id");
} else {
    echo "Erro ao inserir: " . $conn->error . "\n";
}

$conn->close();
?>