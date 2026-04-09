<?php
/**
 * MIGRAÇÃO: SLA de Primeiro Atendimento
 * Adiciona as colunas necessárias para rastrear o tempo de primeiro atendimento.
 * Execute este script UMA única vez no navegador.
 */
include_once 'conexao.php';

$erros   = [];
$sucessos = [];

// ─── 1. Coluna: chamados.data_primeira_resposta ───────────────────────────────
$r = $conn->query("SHOW COLUMNS FROM chamados LIKE 'data_primeira_resposta'");
if ($r && $r->num_rows === 0) {
    if ($conn->query("ALTER TABLE chamados ADD COLUMN data_primeira_resposta DATETIME NULL DEFAULT NULL COMMENT 'Timestamp do primeiro contato do técnico (atribuição ou comentário)'")) {
        $sucessos[] = "✔ Coluna <strong>chamados.data_primeira_resposta</strong> criada com sucesso.";
    } else {
        $erros[] = "✘ Erro ao criar chamados.data_primeira_resposta: " . $conn->error;
    }
} else {
    $sucessos[] = "ℹ Coluna <strong>chamados.data_primeira_resposta</strong> já existe — sem alteração.";
}

// ─── 2. Coluna: configuracoes_sla.sla_primeira_resposta_minutos ───────────────
$r2 = $conn->query("SHOW COLUMNS FROM configuracoes_sla LIKE 'sla_primeira_resposta_minutos'");
if ($r2 && $r2->num_rows === 0) {
    if ($conn->query("ALTER TABLE configuracoes_sla ADD COLUMN sla_primeira_resposta_minutos INT NOT NULL DEFAULT 10 COMMENT 'Tempo alvo global de primeiro atendimento em minutos (padrão: 10 min)'")) {
        $sucessos[] = "✔ Coluna <strong>configuracoes_sla.sla_primeira_resposta_minutos</strong> criada com sucesso (padrão: 10 minutos).";
    } else {
        $erros[] = "✘ Erro ao criar configuracoes_sla.sla_primeira_resposta_minutos: " . $conn->error;
    }
} else {
    $sucessos[] = "ℹ Coluna <strong>configuracoes_sla.sla_primeira_resposta_minutos</strong> já existe — sem alteração.";
}

// ─── 3. Garantir que a configuração exista nas linhas existentes ──────────────
$r3 = $conn->query("SELECT COUNT(*) as total FROM configuracoes_sla");
if ($r3) {
    $row3 = $r3->fetch_assoc();
    if ($row3['total'] == 0) {
        $conn->query("INSERT INTO configuracoes_sla (categoria, tempo_sla_minutos, sla_primeira_resposta_minutos) VALUES ('Incidente', 360, 10), ('Mudança', 1440, 10), ('Requisição', 2880, 10)");
        $sucessos[] = "✔ Registros iniciais de SLA inseridos com padrão de 10 minutos para primeiro atendimento.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Migração: SLA de Primeiro Atendimento</title>
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 700px;">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-database mr-2"></i> Migração: SLA de Primeiro Atendimento</h5>
        </div>
        <div class="card-body">
            <?php foreach ($sucessos as $s): ?>
                <div class="alert alert-success py-2"><?= $s ?></div>
            <?php endforeach; ?>
            <?php foreach ($erros as $e): ?>
                <div class="alert alert-danger py-2"><?= $e ?></div>
            <?php endforeach; ?>
            <?php if (empty($erros)): ?>
                <div class="alert alert-info mt-3">
                    <strong>✔ Migração concluída com sucesso!</strong><br>
                    Agora configure o tempo de primeiro atendimento em <a href="configuracoes.php">Configurações → SLA</a>.
                </div>
            <?php else: ?>
                <div class="alert alert-warning mt-3">
                    <strong>⚠ Migração concluída com erros.</strong> Verifique os erros acima antes de continuar.
                </div>
            <?php endif; ?>
            <a href="configuracoes.php" class="btn btn-primary mt-2">Ir para Configurações</a>
        </div>
    </div>
</div>
</body>
</html>
