<?php
require 'conexao.php';
require_once 'funcoes_email.php';

$destinatario = 'edugomess@icloud.com';
$nomeEmpresa = 'Empresa de Teste (Zabbix Analytics)';

$htmlTeste = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; color: #333; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { color: #3b82f6; }
        .alert-card { border-left: 4px solid #ef4444; background: #fef2f2; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class='container'>
        <h2>✅ Teste de Conexão SMTP Bem-Sucedido!</h2>
        <p>Olá, este é um e-mail de teste gerado pelo sistema <strong>Zabbix Analytics</strong> para confirmar que a sua configuração do PHPMailer está funcionando perfeitamente.</p>
        
        <div class='alert-card'>
            <h3>Isso é uma simulação de Alerta</h3>
            <p>Quando o gerador rodar oficialmente, aqui aparecerão os 10 problemas mais recorrentes da semana e as sugestões de mitigação geradas pela IA do Google Gemini.</p>
        </div>
        
        <p>Se você recebeu isso, a infraestrutura de disparo está pronta para produção!</p>
    </div>
</body>
</html>
";

echo "Iniciando disparo de e-mail de teste para: {$destinatario}...\n";

if (enviarRelatorioZabbix($destinatario, $htmlTeste, $nomeEmpresa)) {
    echo "SUCESSO! O e-mail foi enviado e deve chegar em instantes.\n";
} else {
    echo "ERRO: O envio falhou. Verifique as credenciais no config_notificacoes.php.\n";
}
?>
