<?php
/**
 * CREDENCIAIS DE API: credentials.php
 * Armazena chaves de acesso externas e segredos do sistema.
 * RECOMENDAÇÃO: Adicionar este arquivo ao .gitignore para segurança.
 */

// Chave para análise estratégica de IA (Google Gemini)
define('GEMINI_API_KEY', 'AIzaSyCp_7hYlmPTM8PUyJ6FYYRkJ8zsR9OuTKQ');

// Token para fallback via GitHub Models (Azure AI Inference)
define('GITHUB_TOKEN', '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');

/**
 * Reservado para futuras integrações:
 * define('WHATSAPP_API_TOKEN', '...');
 * define('SMTP_PASSWORD', '...');
 */
?>