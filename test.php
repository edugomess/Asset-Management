<?php
require 'conexao.php';
$res = mysqli_query($conn, 'SELECT * FROM configuracoes_alertas LIMIT 1');
print_r(mysqli_fetch_assoc($res));
