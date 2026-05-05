<?php
$f = 'c:/xampp/htdocs/relatorios.php';
$c = file_get_contents($f);
$c = preg_replace('/<option value="([^"]+)"(?: data-periodo="true")?>/i', '<option value="$1" data-periodo="true">', $c);
file_put_contents($f, $c);
echo "Done";
?>
