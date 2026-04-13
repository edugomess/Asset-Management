<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../config_notificacoes.php';
require_once __DIR__ . '/../funcoes_whatsapp.php';

echo "--- Iniciando Teste de WhatsApp ---\n";
echo "Phone: " . WHATSAPP_PHONE . "\n";
echo "API Key: " . WHATSAPP_API_KEY . "\n";
echo "Ticket ID: 128\n";
echo "-----------------------------------\n\n";

// Para forçar o teste, podemos ignorar os filtros globais se quisermos, 
// mas é melhor testar o fluxo real.
// Vou apenas chamar a função real.

try {
    echo "Tentando enviar...\n";
    $result = enviarWhatsAppNovoChamado(128);
    
    if ($result) {
        echo "\n[OK] Funcao executada. Verifique se a mensagem chegou.\n";
    } else {
        echo "\n[AVISO] A funcao retornou false. Verifique se os filtros de prioridade/categoria estao ativos para este chamado.\n";
    }
} catch (Exception $e) {
    echo "\n[ERRO] Falha na execucao: " . $e->getMessage() . "\n";
}
