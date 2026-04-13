<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Handle language switch via URL parameter
if (isset($_GET['lang'])) {
    $requested_lang = $_GET['lang'];
    // Validate allowed languages
    if (in_array($requested_lang, ['pt-BR', 'en-US'])) {
        $_SESSION['idioma'] = $requested_lang;
    }
}

// 2. Fallback to Database default if not in session
if (!isset($_SESSION['idioma'])) {
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

// Global date localization helper
if (!function_exists('__date')) {
    function __date($date, $includeTime = false) {
        if (empty($date)) return '-';
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        $idioma = $_SESSION['idioma'] ?? 'pt-BR';
        
        if ($idioma === 'en-US') {
            $format = $includeTime ? 'm/d/Y H:i' : 'm/d/Y';
        } else {
            $format = $includeTime ? 'd/m/Y H:i' : 'd/m/Y';
        }
        return date($format, $timestamp);
    }
}

// Global currency localization helper
if (!function_exists('__currency')) {
    function __currency($value) {
        $idioma = $_SESSION['idioma'] ?? 'pt-BR';
        if ($idioma === 'en-US') {
            return '$ ' . number_format($value, 2, '.', ',');
        } else {
            return 'R$ ' . number_format($value, 2, ',', '.');
        }
    }
}

