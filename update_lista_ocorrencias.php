<?php
require 'conexao.php';

$sql = "ALTER TABLE zabbix_historico_alertas ADD COLUMN lista_ocorrencias TEXT AFTER ultima_ocorrencia;";
if ($conn->query($sql) === TRUE) {
    echo "Coluna lista_ocorrencias adicionada com sucesso!\n";
} else {
    echo "Erro ou coluna já existe: " . $conn->error . "\n";
}

// Atualizar o registro fictício do "12 vezes" para ter um JSON de exemplo
$fakeDates = [];
for($i=0; $i<12; $i++) {
    $fakeDates[] = date('Y-m-d H:i:s', strtotime('-' . rand(1, 120) . ' hours'));
}
rsort($fakeDates); // Do mais recente para o mais antigo
$jsonFake = json_encode($fakeDates);

$conn->query("UPDATE zabbix_historico_alertas SET lista_ocorrencias = '$jsonFake' WHERE recorrencia = 12 LIMIT 1");
echo "Exemplo populado no banco.";

$conn->close();
?>
