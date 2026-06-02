<?php
$translated_json = file_get_contents('missing_translated.json');
$translations = json_decode($translated_json, true);

$content = file_get_contents('lang/en-US.php');

// Ensure we don't double inject
if (strpos($content, 'AUTO-TRANSLATED MISSING KEYS') !== false) {
    echo "Translations already injected!\n";
    exit;
}

$pos = strrpos($content, ']');
if ($pos !== false) {
    $before = rtrim(substr($content, 0, $pos), " \n\r\t,");
    $after = "\n];\n";
    
    $append_str = ",\n\n    // --- AUTO-TRANSLATED MISSING KEYS ---\n";
    foreach ($translations as $pt => $en) {
        $pt_esc = addcslashes($pt, "'\\");
        
        // Remove formatting issues from Deep Translator or bad API responses
        if (is_string($en)) {
            $en_esc = addcslashes(trim($en), "'\\");
        } else {
            $en_esc = addcslashes(trim($pt), "'\\");
        }
        
        $append_str .= "    '$pt_esc' => '$en_esc',\n";
    }
    
    $new_content = $before . $append_str . $after;
    file_put_contents('lang/en-US.php', $new_content);
    echo "Successfully injected " . count($translations) . " translations into lang/en-US.php\n";
} else {
    echo "Error: Could not find the closing bracket in en-US.php\n";
}
?>
