<?php
include 'conexao.php';

// Fetch user IDs
$user_ids = [];
$res = mysqli_query($conn, "SELECT id_usuarios FROM usuarios");
while ($row = mysqli_fetch_assoc($res)) {
    $user_ids[] = $row['id_usuarios'];
}

if (empty($user_ids)) {
    die("No users found to assign chamados to.");
}

$categorias = ['Incidente', 'Mudança', 'Requisição'];
$statuses = ['Aberto', 'Em Andamento', 'Fechado'];
$titulos = [
    'Computador lento',
    'Impressora sem papel',
    'Erro ao acessar sistema',
    'Solicitação de mouse novo',
    'Mudança de sala',
    'Configuração de VPN',
    'Instalação de software',
    'Problema no monitor',
    'Reset de senha',
    'Criação de usuário',
    'Internet oscilando',
    'Teclado falhando',
    'Tela piscando',
    'Sem acesso à rede',
    'Erro no Office',
    'Vírus detectado',
    'Backup falhou',
    'Solicitação de fone',
    'Cabo de rede quebrado',
    'Troca de toner'
];

$descricoes = [
    'O usuário relata lentidão ao abrir programas.',
    'Necessário repor papel na impressora do 2º andar.',
    'Erro 500 ao tentar login no ERP.',
    'Mouse atual está com clique falhando.',
    'Usuário mudará para a sala 304.',
    'Precisa de acesso remoto.',
    'Instalar Adobe Reader.',
    'Monitor não liga.',
    'Esqueceu a senha do Windows.',
    'Novo funcionário do setor financeiro.',
    'Conexão caindo frequentemente.',
    'Algumas teclas não funcionam.',
    'Tela fica piscando intermitentemente.',
    'Não consegue acessar pastas compartilhadas.',
    'Word fecha sozinho.',
    'Antivírus alertou ameaça.',
    'Backup noturno falhou.',
    'Fone de ouvido quebrou.',
    'Cabo rompido perto da mesa.',
    'Impressora pedindo toner preto.'
];

echo "Generating tickets...\n";

$inserted = 0;
for ($i = 0; $i < 20; $i++) {
    $titulo = $titulos[$i % count($titulos)]; // Cycle through titles
    $categoria = $categorias[array_rand($categorias)];
    $descricao = $descricoes[$i % count($descricoes)]; // Cycle through descriptions
    $status = $statuses[array_rand($statuses)];

    $usuario_id = $user_ids[array_rand($user_ids)];

    // 50% chance of having a responsible person assigned
    $responsavel_id = (rand(0, 1) == 1) ? $user_ids[array_rand($user_ids)] : null;

    $sql = "INSERT INTO chamados (titulo, categoria, descricao, status, data_abertura, usuario_id, responsavel_id) VALUES (?, ?, ?, ?, NOW(), ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        // Correctly handle null binding
        // "ssssii" = string, string, string, string, integer, integer
        mysqli_stmt_bind_param($stmt, "ssssii", $titulo, $categoria, $descricao, $status, $usuario_id, $responsavel_id);

        try {
            if (mysqli_stmt_execute($stmt)) {
                $inserted++;
            }
            else {
                echo "Error inserting ticket: " . mysqli_stmt_error($stmt) . "\n";
            }
        }
        catch (Exception $e) {
            echo "Exception inserting ticket: " . $e->getMessage() . "\n";
        }
        mysqli_stmt_close($stmt);
    }
    else {
        echo "Error preparing statement: " . mysqli_error($conn) . "\n";
    }
}

echo "Done. Inserted $inserted tickets.\n";
?>
