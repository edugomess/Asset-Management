<?php
include 'conexao.php';
$res = mysqli_query($conn, 'SELECT id, whatsapp_ativo, cat_incidente, cat_mudanca, cat_requisicao FROM configuracoes_alertas');
echo "All rows in configuracoes_alertas:\n";
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
