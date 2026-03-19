<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['idioma'])) {
    // If not set in session, try to fetch from DB if connection exists
    global $conn;
    if (isset($conn)) {
        $res = $conn->query("SELECT idioma FROM configuracoes_alertas LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $_SESSION['idioma'] = $row['idioma'];
        } else {
            $_SESSION['idioma'] = 'pt-BR'; // default fallback
        }
    } else {
        $_SESSION['idioma'] = 'pt-BR';
    }
}

// Load language dictionary
global $lang_data;
$lang_file = __DIR__ . '/lang/' . $_SESSION['idioma'] . '.php';

if (file_exists($lang_file)) {
    $lang_data = include $lang_file;
} else {
    $lang_data = include __DIR__ . '/lang/pt-BR.php'; // fallback
}

// Global translation helper function
if (!function_exists('__')) {
    function __($key) {
        global $lang_data;
        return $lang_data[$key] ?? $key;
    }
}
?>
