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

$qtd = 35; // Vamos criar 35 chamados hoje
echo "Iniciando a inserção de $qtd chamados de HOJE...\n";
$success_count = 0;

for ($i = 0; $i < $qtd; $i++) {
    $titulo = $titulos[array_rand($titulos)] . " - " . rand(1000, 9999);
    $categoria = $categorias[array_rand($categorias)];
    $tipo_servico = $servicos[array_rand($servicos)];
    $descricao = "Descrição gerada automaticamente para o chamado de hoje #" . rand(1000, 9999);
    $status = $status_list[array_rand($status_list)];
    $prioridade = $prioridades[array_rand($prioridades)];
    
    // Data de hoje com horas aleatórias (entre 00:00 e agora)
    $hours_ago = rand(0, (int)date('H'));
    $mins_ago = rand(0, 59);
    $data_abertura = date('Y-m-d H:i:s', strtotime("today +$hours_ago hours +$mins_ago minutes"));
    
    // Garantir que não passe da hora atual
    if (strtotime($data_abertura) > time()) {
        $data_abertura = date('Y-m-d H:i:s');
    }
    
    $usuario_id = $valid_users[array_rand($valid_users)];
    $responsavel_id = $valid_users[array_rand($valid_users)];
    
    $data_fechamento = null;
    $nota_resolucao = null;
    if (in_array($status, ['Resolvido', 'Fechado'])) {
        $resolve_mins = rand(10, 300);
        $data_fechamento = date('Y-m-d H:i:s', strtotime($data_abertura . " +$resolve_mins minutes"));
        if (strtotime($data_fechamento) > time()) {
            $data_fechamento = date('Y-m-d H:i:s');
        }
        $nota_resolucao = "Resolvido hoje mesmo pela equipe.";
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

echo "Finalizado! Foram inseridos $success_count chamados com a data de HOJE.\n";
?>
