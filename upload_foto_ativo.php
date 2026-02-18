<?php
include 'conexao.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado ou erro no upload']);
    exit;
}

$id_asset = isset($_POST['id_asset']) ? intval($_POST['id_asset']) : 0;
if ($id_asset <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID do ativo inválido']);
    exit;
}

$uploadDir = 'assets/img/ativos/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileInfo = pathinfo($_FILES['foto']['name']);
$extension = strtolower($fileInfo['extension']);
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($extension, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'Formato de arquivo não permitido']);
    exit;
}

// Generate unique filename to avoid cache issues and collisions
$newFileName = 'ativo_' . $id_asset . '_' . time() . '.' . $extension;
$targetPath = $uploadDir . $newFileName;

if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetPath)) {
    // Update database
    $imagePathForDb = '/' . $targetPath;
    $sqlUpdate = "UPDATE ativos SET imagem = ? WHERE id_asset = ?";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param('si', $imagePathForDb, $id_asset);

    if ($stmt->execute()) {
        // Log history
        $usuario_id = isset($_SESSION['id_usuarios']) ? $_SESSION['id_usuarios'] : NULL;
        $acao = 'Edição';
        $detalhes = 'Foto do ativo atualizada';

        $sqlHist = "INSERT INTO historico_ativos (ativo_id, usuario_id, acao, detalhes) VALUES (?, ?, ?, ?)";
        $stmtHist = $conn->prepare($sqlHist);
        $stmtHist->bind_param('iiss', $id_asset, $usuario_id, $acao, $detalhes);
        $stmtHist->execute();

        echo json_encode(['success' => true, 'new_image' => $imagePathForDb]);
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar banco de dados: ' . $conn->error]);
    }
}
else {
    echo json_encode(['success' => false, 'message' => 'Erro ao mover o arquivo para o diretório de destino']);
}

$conn->close();
?>
