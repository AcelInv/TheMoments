<?php
header("Content-Type: application/json; charset=UTF-8");
include_once __DIR__ . '/../config/Database.php';

try {
    $db = (new Database())->getConnection();
    $productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
    $limit = min(max((int) ($_GET['limit'] ?? 12), 1), 50);
    $sql = "SELECT r.id, r.product_id, r.rating, r.comment, r.created_at, u.name AS user_name, p.name AS product_name
      FROM reviews r
      JOIN users u ON u.id = r.user_id
      JOIN products p ON p.id = r.product_id";
    $params = [];
    if ($productId) { $sql .= " WHERE r.product_id = ?"; $params[] = $productId; }
    $sql .= " ORDER BY r.created_at DESC LIMIT $limit";
    $stmt = $db->prepare($sql); $stmt->execute($params);
    $reviews = $stmt->fetchAll();
    foreach ($reviews as &$review) {
        $review['id'] = (int) $review['id'];
        $review['product_id'] = (int) $review['product_id'];
        $review['rating'] = (int) $review['rating'];
    }
    echo json_encode(["status" => "success", "data" => $reviews]);
} catch (Throwable $e) {
    http_response_code(500); echo json_encode(["status" => "error", "message" => "Gagal mengambil ulasan."]);
}
?>
