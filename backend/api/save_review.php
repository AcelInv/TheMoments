<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';

requireCsrfToken();
requireLogin();
rateLimit('save_review', 10, 3600);

$data = json_decode(file_get_contents("php://input"));
$data = is_object($data) ? $data : null;
if ($data === null) {
    http_response_code(400); echo json_encode(["status" => "error", "message" => "Format ulasan tidak valid."]); exit;
}
$productId = isset($data->product_id) ? (int) $data->product_id : 0;
$rating = isset($data->rating) ? (int) $data->rating : 0;
$comment = trim((string) ($data->comment ?? ''));
if (!$productId || $rating < 1 || $rating > 5) {
    http_response_code(422); echo json_encode(["status" => "error", "message" => "Data ulasan tidak valid."]); exit;
}
if (mb_strlen($comment) > 1000) {
    http_response_code(422); echo json_encode(["status" => "error", "message" => "Ulasan maksimal 1000 karakter."]); exit;
}
try {
    $db = (new Database())->getConnection();
    $product = $db->prepare("SELECT id FROM products WHERE id = ? AND is_active = TRUE");
    $product->execute([$productId]);
    if (!$product->fetchColumn()) {
        http_response_code(404); echo json_encode(["status" => "error", "message" => "Produk tidak ditemukan."]); exit;
    }
    $completedOrder = $db->prepare("SELECT 1
        FROM orders o
        JOIN order_items oi ON oi.order_id = o.id
        WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'selesai'
        LIMIT 1");
    $completedOrder->execute([(int) $_SESSION['user_id'], $productId]);
    if (!$completedOrder->fetchColumn()) {
        http_response_code(403); echo json_encode(["status" => "error", "message" => "Ulasan hanya dapat diberikan untuk produk dari pesanan yang sudah selesai."]); exit;
    }
    $stmt = $db->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)
      ON CONFLICT (user_id, product_id) DO UPDATE
      SET rating = EXCLUDED.rating, comment = EXCLUDED.comment, updated_at = CURRENT_TIMESTAMP");
    $stmt->execute([(int) $_SESSION['user_id'], $productId, $rating, $comment ?: null]);
    echo json_encode(["status" => "success", "message" => "Ulasan berhasil disimpan."]);
} catch (Throwable $e) {
    http_response_code(422); echo json_encode(["status" => "error", "message" => "Gagal menyimpan ulasan."]);
}
?>
