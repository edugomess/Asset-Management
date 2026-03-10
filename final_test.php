<?php
require_once 'credentials.php';
require_once 'funcoes_ai.php';

$res = callAI("Teste rápido: Qual sua versão?");
echo "AI REPLY: " . ($res ? $res : "FAILED (Check logs)");
?>