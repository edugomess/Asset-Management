<?php
$dir = new RecursiveDirectoryIterator(__DIR__);
$iterator = new RecursiveIteratorIterator($dir);
$found_keys = [];
$total_files = 0;

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;
    if (strpos($file->getPathname(), 'lang') !== false) continue;
    
    $content = file_get_contents($file->getPathname());
    $total_files++;
    
    // Look for __("text") or __('text')
    preg_match_all("/__\(['\"](.*?)['\"]\)/", $content, $matches);
    foreach ($matches[1] as $key) {
        $found_keys[$key] = true;
    }
}

$en = include __DIR__ . '/lang/en-US.php';
$pt = include __DIR__ . '/lang/pt-BR.php';

$missing_in_en = [];
foreach (array_keys($found_keys) as $k) {
    if (!isset($en[$k])) {
        $missing_in_en[] = $k;
    }
}

echo "Total PHP files scanned: $total_files\n";
echo "Total unique translation keys found in code: " . count($found_keys) . "\n";
echo "Keys used in code but missing in EN (" . count($missing_in_en) . "):\n";
file_put_contents('missing.json', json_encode($missing_in_en, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo 'Saved to missing.json';
?>
