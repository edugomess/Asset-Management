<?php
include 'conexao.php';

$titulos = [
    "Problema no Outlook", "Computador não liga", "Troca de Mouse", 
    "Acesso ao ERP bloqueado", "Lentidão na rede", "Instalação do pacote Office",
    "Monitor piscando", "Troca de teclado", "Configuração de impressora",
    "Acesso VPN", "Atualização do Windows", "Tela Azul", "Sistema fora do ar",
    "Esqueci a senha", "Solicitação de fone de ouvido", "Erro ao salvar arquivo"
];

$categorias = ['Incidente', 'Mudança', 'Requisição'];
$servicos = ['Hardware', 'Software', 'Rede', 'Acesso', 'Outros'];
$status_list = ['Aberto', 'Em Andamento', 'Congelado', 'Resolvido', 'Fechado', 'Cancelado'];
$prioridades = ['P1', 'P2', 'P3', 'P4'];

$res_users = mysqli_query($conn, "SELECT id_usuarios FROM usuarios");
$valid_users = [];
while($row = mysqli_fetch_assoc($res_users)) {
    $valid_users[] = $row['id_usuarios'];
}

if (empty($valid_users)) {
    die("Erro: Nenhum usuário encontrado no banco para vincular aos chamados.");
}

echo "Iniciando a inserção de 600 chamados aleatórios...\n";
$success_count = 0;

for ($i = 0; $i < 600; $i++) {
    $titulo = $titulos[array_rand($titulos)] . " - " . rand(1000, 9999);
    $categoria = $categorias[array_rand($categorias)];
    $tipo_servico = $servicos[array_rand($servicos)];
    $descricao = "Descrição gerada automaticamente para o chamado #" . rand(1000, 9999) . ". O usuário relatou este problema recentemente.";
    $status = $status_list[array_rand($status_list)];
    $prioridade = $prioridades[array_rand($prioridades)];
    
    // Random date within the last 365 days
    $days_ago = rand(1, 365);
    $hours_ago = rand(0, 23);
    $mins_ago = rand(0, 59);
    $data_abertura = date('Y-m-d H:i:s', strtotime("-$days_ago days -$hours_ago hours -$mins_ago minutes"));
    
    $usuario_id = $valid_users[array_rand($valid_users)];
    $responsavel_id = $valid_users[array_rand($valid_users)];
    
    // If closed or resolved, add data_fechamento
    $data_fechamento = null;
    $nota_resolucao = null;
    if (in_array($status, ['Resolvido', 'Fechado'])) {
        $resolve_days = rand(0, 5);
        $data_fechamento = date('Y-m-d H:i:s', strtotime($data_abertura . " +$resolve_days days +2 hours"));
        $nota_resolucao = "Problema resolvido com sucesso pela equipe de TI.";
    }

    $sql = "INSERT INTO chamados (
        titulo, categoria, tipo_servico, descricao, status, prioridade, data_abertura, usuario_id, responsavel_id, data_fechamento, nota_resolucao
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssssssiiss", 
            $titulo, $categoria, $tipo_servico, $descricao, $status, $prioridade, 
            $data_abertura, $usuario_id, $responsavel_id, $data_fechamento, $nota_resolucao
        );
        if ($stmt->execute()) {
            $success_count++;
        }
        $stmt->close();
    }
}

echo "Finalizado! Foram inseridos $success_count chamados com sucesso.\n";
?>
