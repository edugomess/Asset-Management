<?php
include 'conexao.php';

// Configuração
$telefone_destino = "+5511968435543"; // Número do usuário
$sla_dias = 7; // SLA em dias
$aviso_dias = 6; // Avisar quando atingir X dias
$apikey = "CHANGE_ME"; // A API Key será necessária aqui (instruções abaixo)

// Função para enviar mensagem via CallMeBot (API Gratuita para uso pessoal)
function enviarWhatsApp($telefone, $mensagem, $apikey)
{
    if ($apikey == "CHANGE_ME") {
        return "Erro: API Key não configurada.";
    }
    $mensagem_encoded = urlencode($mensagem);
    $url = "https://api.callmebot.com/whatsapp.php?phone=" . str_replace("+", "", $telefone) . "&text=" . $mensagem_encoded . "&apikey=" . $apikey;

    $context = stream_context_create([
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: PHPScript"
        ]
    ]);

    $response = file_get_contents($url, false, $context);
    return $response;
}

echo "<h3>Verificando chamados próximos ao vencimento do SLA...</h3>";

// Selecionar chamados abertos próximos do SLA (ex: >= 6 dias) que AINDA NÃO foram notificados
$sql = "SELECT c.id, c.titulo, c.data_abertura, DATEDIFF(NOW(), c.data_abertura) as dias_aberto 
        FROM chamados c 
        WHERE c.status NOT IN ('Resolvido', 'Fechado', 'Cancelado') 
        AND DATEDIFF(NOW(), c.data_abertura) >= $aviso_dias
        AND c.id NOT IN (SELECT chamado_id FROM notificacoes WHERE tipo = 'SLA_Vencendo')";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $msg = "⚠️ *Alerta de SLA* ⚠️\n\nO chamado *#{$row['id']} - {$row['titulo']}* está aberto há *{$row['dias_aberto']} dias*.\nPrazo SLA: $sla_dias dias.\nPor favor, verifique!";

        echo "<p>Enviando alerta para o chamado #{$row['id']}...</p>";

        // Tentar enviar
        // NOTA: Para funcionar, você precisa pegar a API Key. 
        // Envie "I allow callmebot" para +34 644 19 86 51 no WhatsApp para pegar a key.

        if ($apikey !== "CHANGE_ME") {
            $resp = enviarWhatsApp($telefone_destino, $msg, $apikey);
            if (strpos($resp, 'Message queued') !== false || strpos($resp, 'Success') !== false) {
                // Registrar notificação no banco para não enviar de novo
                $stmt = $conn->prepare("INSERT INTO notificacoes (chamado_id, tipo, destinatario) VALUES (?, 'SLA_Vencendo', ?)");
                $stmt->bind_param("is", $row['id'], $telefone_destino);
                $stmt->execute();
                echo "<p style='color:green'>Sucesso! Mensagem enviada e registrada.</p>";
            }
            else {
                echo "<p style='color:red'>Erro ao enviar: $resp</p>";
            }
        }
        else {
            echo "<p style='color:orange'>Simulação: Mensagem seria '{$msg}'. <br>Configure a API Key no arquivo para enviar de verdade.</p>";
        }

    }
}
else {
    echo "<p>Nenhum chamado crítico pendente de notificação.</p>";
}

$conn->close();
?>
