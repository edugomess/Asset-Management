<?php
include 'conexao.php';

echo "Semeando chamados...\n";

$users = [42, 48, 50, 51, 55];
$categorias = ['Incidente', 'Mudança', 'Requisição'];
$prioridades = ['P1', 'P2', 'P3', 'P4'];
$statuses = ['Aberto', 'Em andamento', 'Aguardando aprovação', 'Fechado'];

$servicos = [
    'Software' => ['Erro no Office 365', 'Instalação de Adobe Creative Cloud', 'Problema no Teams', 'Atualização de Sistema'],
    'Hardware' => ['Teclado com defeito', 'Monitor piscando', 'Notebook lento', 'Upgrade de memória'],
    'Acesso' => ['Redefinição de senha VPN', 'Acesso à pasta de rede', 'Novo acesso ao SAP'],
    'Outros' => ['Troca de mouse', 'Instalação de impressora', 'Dúvida sobre política de ativos']
];

$seed_data = [
    ['titulo' => 'Notebook não liga', 'cat' => 'Incidente', 'serv' => 'Hardware', 'prior' => 'P1'],
    ['titulo' => 'Acesso ao Dropbox solicitado', 'cat' => 'Requisição', 'serv' => 'Acesso', 'prior' => 'P3'],
    ['titulo' => 'Monitor secundário piscando', 'cat' => 'Incidente', 'serv' => 'Hardware', 'prior' => 'P2'],
    ['titulo' => 'Instalação de VS Code', 'cat' => 'Requisição', 'serv' => 'Software', 'prior' => 'P4'],
    ['titulo' => 'VPN desconectando', 'cat' => 'Incidente', 'serv' => 'Acesso', 'prior' => 'P2'],
    ['titulo' => 'Troca de headset', 'cat' => 'Requisição', 'serv' => 'Hardware', 'prior' => 'P3'],
    ['titulo' => 'Erro ao salvar arquivos no servidor', 'cat' => 'Incidente', 'serv' => 'Acesso', 'prior' => 'P1'],
    ['titulo' => 'Upgrade Windows 11', 'cat' => 'Mudança', 'serv' => 'Software', 'prior' => 'P3'],
    ['titulo' => 'Nova estação de trabalho para estagiário', 'cat' => 'Requisição', 'serv' => 'Hardware', 'prior' => 'P3'],
    ['titulo' => 'Esqueci senha do BitLocker', 'cat' => 'Incidente', 'serv' => 'Acesso', 'prior' => 'P2']
];

foreach ($seed_data as $data) {
    $uid = $users[array_rand($users)];
    $status = $statuses[array_rand($statuses)];
    $desc = "Solicitação automática gerada para testes do sistema: " . $data['titulo'];
    
    $stmt = $conn->prepare("INSERT INTO chamados (titulo, categoria, tipo_servico, descricao, status, prioridade, usuario_id, data_abertura) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param('ssssssi', $data['titulo'], $data['cat'], $data['serv'], $desc, $status, $data['prior'], $uid);
    
    if ($stmt->execute()) {
        echo "Chamado '{$data['titulo']}' criado.\n";
    } else {
        echo "Erro ao criar chamado '{$data['titulo']}': " . $conn->error . "\n";
    }
}

echo "Semeadura de chamados concluída.\n";
?>
