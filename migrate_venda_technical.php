<?php
include 'conexao.php';

$columns = [
    'numero_serie VARCHAR(255) DEFAULT \'-\'',
    'processador VARCHAR(255) DEFAULT \'\'',
    'memoria VARCHAR(255) DEFAULT \'\'',
    'armazenamento VARCHAR(255) DEFAULT \'\'',
    'setor VARCHAR(255) DEFAULT \'-\''
];

foreach ($columns as $col) {
    $sql = "ALTER TABLE venda ADD COLUMN $col";
    if ($conn->query($sql) === TRUE) {
        echo "Column added successfully: $col\n";
    } else {
        echo "Error or column already exists: " . $conn->error . "\n";
    }
}

$conn->close();
?>
