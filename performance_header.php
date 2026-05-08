<?php
/**
 * performance_header.php
 * Start this at the very top of each page.
 */
if (!in_array('ob_gzhandler', ob_list_handlers())) {
    ob_start("ob_gzhandler");
}

function renderPerformanceHints() {
    echo '
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://use.fontawesome.com">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css">
    <link rel="stylesheet" href="/assets/css/responsive_fix.css">
    ';
}

function startNProgress() {
    echo '
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
    <script>
        NProgress.configure({ showSpinner: true, trickleSpeed: 200 });
        NProgress.start();
    </script>
    ';
}
