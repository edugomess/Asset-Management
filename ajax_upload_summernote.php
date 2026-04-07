<?php
/**
 * AJAX UPLOAD SUMMERNOTE: ajax_upload_summernote.php
 * Processa o upload de imagens do editor Summernote para o sistema de arquivos.
 */
include_once 'auth.php';
include_once 'conexao.php';

header('Content-Type: application/json');

// Verificar se o arquivo foi enviado
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo ou nenhum arquivo enviado.']);
    exit;
}

// Configurações de diretório
$uploadDir = 'uploads/interacoes/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Validar extensão e tipo MIME
$fileInfo = pathinfo($_FILES['image']['name']);
$extension = strtolower($fileInfo['extension']);
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['image']['tmp_name']);
finfo_close($finfo);

if (!in_array($extension, $allowedExtensions) || !in_array($mimeType, $allowedMimeTypes)) {
    echo json_encode(['success' => false, 'message' => 'Formato de arquivo não permitido. Apenas imagens são aceitas.']);
    exit;
}

// Limitar tamanho (5MB)
if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'O arquivo é muito grande (Máx: 5MB).']);
    exit;
}

// Gerar nome único para o arquivo
$newFileName = 'img_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $extension;
$targetPath = $uploadDir . $newFileName;

// Mover arquivo para o diretório final
if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
    // Retornar a URL relativa para o frontend
    echo json_encode(['success' => true, 'url' => $targetPath]);
} else {
    echo json_encode(['success' => false, 'message' => 'Falha ao mover o arquivo para o servidor.']);
}

$conn->close();
?>
