<?php
/**
 * Script de otimização do banco de dados
 * Adiciona índices nas colunas mais buscadas para melhorar performance.
 * Execute uma única vez: http://localhost/optimize_db.php
 */
include 'conexao.php';

echo "<h2>Otimização do Banco de Dados</h2>";
echo "<pre>";

$indexes = [
    // Tabela ativos
    ["ativos", "idx_ativos_status", "status"],
    ["ativos", "idx_ativos_categoria", "categoria"],
    ["ativos", "idx_ativos_assigned_to", "assigned_to"],
    ["ativos", "idx_ativos_modelo", "modelo"],
    ["ativos", "idx_ativos_tag", "tag"],
    ["ativos", "idx_ativos_hostName", "hostName"],
    ["ativos", "idx_ativos_dataAtivacao", "dataAtivacao"],

    // Tabela chamados
    ["chamados", "idx_chamados_status", "status"],
    ["chamados", "idx_chamados_categoria", "categoria"],
    ["chamados", "idx_chamados_data_abertura", "data_abertura"],
    ["chamados", "idx_chamados_data_fechamento", "data_fechamento"],
    ["chamados", "idx_chamados_usuario_id", "usuario_id"],
    ["chamados", "idx_chamados_responsavel_id", "responsavel_id"],
    ["chamados", "idx_chamados_titulo", "titulo"],

    // Tabela usuarios
    ["usuarios", "idx_usuarios_nome", "nome"],
    ["usuarios", "idx_usuarios_sobrenome", "sobrenome"],
    ["usuarios", "idx_usuarios_email", "email"],
    ["usuarios", "idx_usuarios_usuarioAD", "usuarioAD"],
    ["usuarios", "idx_usuarios_status", "status"],

    // Tabela centro_de_custo
    ["centro_de_custo", "idx_cc_nomeSetor", "nomeSetor"],
    ["centro_de_custo", "idx_cc_codigo", "codigo"],

    // Tabela fornecedor
    ["fornecedor", "idx_forn_nomeEmpresa", "nomeEmpresa"],
    ["fornecedor", "idx_forn_email", "email"],
    ["fornecedor", "idx_forn_cnpj", "cnpj"],
];

$success = 0;
$skipped = 0;
$errors = 0;

foreach ($indexes as $idx) {
    $table = $idx[0];
    $name = $idx[1];
    $column = $idx[2];

    // Verificar se o índice já existe
    $check = mysqli_query($conn, "SHOW INDEX FROM `$table` WHERE Key_name = '$name'");
    if ($check && mysqli_num_rows($check) > 0) {
        echo "⏭️  Índice $name já existe em $table.$column\n";
        $skipped++;
        continue;
    }

    $sql = "ALTER TABLE `$table` ADD INDEX `$name` (`$column`)";
    if (mysqli_query($conn, $sql)) {
        echo "✅ Índice $name criado em $table.$column\n";
        $success++;
    }
    else {
        echo "❌ Erro ao criar $name em $table.$column: " . mysqli_error($conn) . "\n";
        $errors++;
    }
}

// Otimizar tabelas (reorganizar dados e reconstruir índices)
echo "\n--- Otimizando tabelas ---\n";
$tables = ['ativos', 'chamados', 'usuarios', 'centro_de_custo', 'fornecedor'];
foreach ($tables as $table) {
    $result = mysqli_query($conn, "OPTIMIZE TABLE `$table`");
    if ($result) {
        echo "✅ Tabela $table otimizada\n";
    }
    else {
        echo "❌ Erro ao otimizar $table: " . mysqli_error($conn) . "\n";
    }
}

echo "\n=== RESUMO ===\n";
echo "✅ Índices criados: $success\n";
echo "⏭️  Índices já existentes: $skipped\n";
echo "❌ Erros: $errors\n";
echo "</pre>";

echo "<p><strong>Otimização concluída!</strong> Você pode deletar este arquivo após executá-lo.</p>";

mysqli_close($conn);
?>
