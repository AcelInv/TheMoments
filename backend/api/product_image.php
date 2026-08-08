<?php
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$id) { http_response_code(404); exit; }
include_once __DIR__ . '/../config/Database.php';
try {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare('SELECT image_data, image_mime_type FROM products WHERE id = ? AND is_active = TRUE');
    $stmt->execute([$id]);
    $image = $stmt->fetch();
    if (!$image || empty($image['image_data'])) { http_response_code(404); exit; }
    header('Content-Type: ' . ($image['image_mime_type'] ?: 'application/octet-stream'));
    // Foto dapat diganti dari panel admin; jangan layani versi lama dari cache browser.
    header('Cache-Control: no-store, max-age=0, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo is_resource($image['image_data']) ? stream_get_contents($image['image_data']) : $image['image_data'];
} catch (Throwable $error) { http_response_code(404); }
?>
