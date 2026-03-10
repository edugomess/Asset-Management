<?php
session_start();
$_SESSION['id_usuarios'] = 1;
$_SESSION['emailUsuario'] = 'admin@admin.com';
$_SESSION['nivelUsuario'] = 'Admin';
$_SESSION['nomeUsuario'] = 'Admin Test';
session_write_close();

$html = file_get_contents('http://localhost/configuracoes.php', false, stream_context_create([
    'http' => [
        'header' => "Cookie: PHPSESSID=" . session_id() . "\r\n"
    ]
]));

$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

$toggles = ['chamados_ativo', 'manutencao_ativo', 'whatsapp_ativo', 'email_ativo'];

foreach ($toggles as $name) {
    $nodes = $xpath->query("//input[@name='$name']");
    if ($nodes->length > 0) {
        $node = $nodes->item(0);
        $isChecked = $node->hasAttribute('checked') ? 'YES' : 'NO';
        echo "$name -> Checked: $isChecked\n";
    } else {
        echo "$name -> Not found\n";
    }
}
