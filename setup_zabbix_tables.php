<?php
require 'conexao.php';

$sqlEmpresas = "
CREATE TABLE IF NOT EXISTS `zabbix_empresas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `user` varchar(100) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `email_destinatario` varchar(150) NOT NULL,
  `gemini_api_key` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$sqlHistorico = "
CREATE TABLE IF NOT EXISTS `zabbix_historico_alertas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  `data_relatorio` date NOT NULL,
  `alerta_nome` varchar(255) NOT NULL,
  `recorrencia` int(11) NOT NULL,
  `ai_sugestao` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`empresa_id`) REFERENCES `zabbix_empresas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($sqlEmpresas) === TRUE) {
    echo "Tabela 'zabbix_empresas' criada com sucesso.\n";
} else {
    echo "Erro ao criar tabela zabbix_empresas: " . $conn->error . "\n";
}

if ($conn->query($sqlHistorico) === TRUE) {
    echo "Tabela 'zabbix_historico_alertas' criada com sucesso.\n";
} else {
    echo "Erro ao criar tabela zabbix_historico_alertas: " . $conn->error . "\n";
}

$conn->close();
?>
