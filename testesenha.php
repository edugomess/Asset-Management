<?php
$senha = '3176'; // Substitua pela senha que você cadastrou
$senhaHash = sha1($senha);
echo "Hash da senha: " . $senhaHash;
?>