<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';

requireAdmin();
requireCsrfToken();
$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$file = $_FILES['image'] ?? null;
if (!$productId || !$file || $file['error'] !== UPLOAD_ERR_OK || !isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
    http_response_code(422); echo json_encode(["status" => "error", "message" => "product_id dan gambar diperlukan."]); exit;
}
$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
if (!in_array($mime, $allowed, true) || !isset($file['size']) || $file['size'] < 1 || $file['size'] > 5 * 1024 * 1024) {
    http_response_code(422); echo json_encode(["status" => "error", "message" => "Gunakan gambar JPG, PNG, WEBP, atau GIF maksimal 5 MB."]); exit;
}
$imageInfo = @getimagesize($file['tmp_name']);
if ($imageInfo === false || ($imageInfo['mime'] ?? '') !== $mime || $imageInfo[0] > 8000 || $imageInfo[1] > 8000) {
    http_response_code(422); echo json_encode(["status" => "error", "message" => "Dimensi atau isi gambar tidak valid."]); exit;
}
try {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare('UPDATE products SET image_url = NULL, image_data = :data, image_mime_type = :mime WHERE id = :id AND is_active = TRUE');
    $stmt->bindValue(':data', file_get_contents($file['tmp_name']), PDO::PARAM_LOB);
    $stmt->bindValue(':mime', $mime);
    $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    if (!$stmt->rowCount()) throw new RuntimeException();
    echo json_encode(["status" => "success", "image_url" => "backend/api/product_image.php?id={$productId}"]);
} catch (Throwable $error) {
    http_response_code(404); echo json_encode(["status" => "error", "message" => "Produk tidak ditemukan atau gambar gagal disimpan."]);
}
?>
