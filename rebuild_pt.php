<?php
$en = include 'lang/en-US.php';
$pt = [];
foreach ($en as $key => $value) {
    $pt[$key] = $key;
}

$content = "<?php\nreturn [\n";
foreach ($pt as $key => $value) {
    // Escape single quotes in key and value
    $k = str_replace("'", "\'", $key);
    $v = str_replace("'", "\'", $value);
    $content .= "    '$k' => '$v',\n";
}
$content .= "];\n";

file_put_contents('lang/pt-BR.php', $content);
echo "Reconstruído pt-BR.php com sucesso baseado em en-US.php.\n";
?>
