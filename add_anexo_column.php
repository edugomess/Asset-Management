<?php
include 'conexao.php';

// Adicionar coluna anexo na tabela chamados
$sql = "ALTER TABLE chamados ADD COLUMN IF NOT EXISTS anexo VARCHAR(255) DEFAULT NULL";
if (mysqli_query($conn, $sql)) {
    echo "Coluna 'anexo' adicionada com sucesso!\n";
}
else {
    echo "Erro ou coluna já existe: " . mysqli_error($conn) . "\n";
}

// Criar pasta de uploads
$upload_dir = __DIR__ . '/uploads/chamados';
if (!is_dir($upload_dir)) {
    if (mkdir($upload_dir, 0777, true)) {
        echo "Pasta uploads/chamados criada!\n";
    }
    else {
        echo "Erro ao criar pasta.\n";
    }
}
else {
    echo "Pasta uploads/chamados já existe.\n";
}

echo "Pronto!\n";
mysqli_close($conn);
?>
