<?php
/**
 * performance_footer.php
 * Include this at the very bottom of each page.
 */
echo '
<script>
    if (typeof NProgress !== "undefined") {
        NProgress.done();
    }
</script>
';

// Flush the output buffer
if (ob_get_level() > 0) {
    ob_end_flush();
}
?>
