<?php
include 'conexao.php';

$sql = "ALTER TABLE chamados ADD COLUMN responsavel_id INT(11) NULL AFTER usuario_id";

if ($conn->query($sql) === TRUE) {
    echo "Coluna 'responsavel_id' adicionada com sucesso!";
}
else {
    echo "Erro ao adicionar coluna (ou ela já existe): " . $conn->error;
}

$sql_fk = "ALTER TABLE chamados ADD CONSTRAINT fk_responsavel FOREIGN KEY (responsavel_id) REFERENCES usuarios(id_usuarios) ON DELETE SET NULL";

if ($conn->query($sql_fk) === TRUE) {
    echo "<br>Chave estrangeira adicionada com sucesso!";
}
else {
    echo "<br>Erro ao adicionar chave estrangeira: " . $conn->error;
}

$conn->close();
?>
