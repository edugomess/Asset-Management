<?php
require 'c:/xampp/htdocs/conexao.php';
$res = mysqli_query($conn, 'SELECT id, whatsapp_ativo, chamados_ativo, manutencao_ativo FROM configuracoes_alertas');
while ($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
