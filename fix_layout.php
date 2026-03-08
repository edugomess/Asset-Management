<?php
$file = 'configuracoes.php';
$content = file_get_contents($file);
$pattern = '/<\/div><!-- End of col-xl-6 \(Email\) -->\s+<\/div><!-- End of row \(WhatsApp\/Email\) -->/';
$replacement = "</div> <!-- End of destinatariosList -->
                                             </div> <!-- End of Email card-body -->
                                         </div> <!-- End of Email card shadow-sm -->
                                     </div> <!-- End of Email col-xl-6 -->
                                 </div> <!-- End of WhatsApp/Email row -->";
$newContent = preg_replace($pattern, $replacement, $content, 1);
if ($newContent !== null && $newContent !== $content) {
    file_put_contents($file, $newContent);
    echo "SUCCESS: Layout updated.";
} else {
    echo "ERROR: Pattern not found or no change.";
}
?>