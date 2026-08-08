<?php
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';

$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
if (!$userId) {
    echo json_encode(["status" => "error", "message" => "user_id diperlukan."]);
    exit;
}

checkUserOrAdmin($userId);

try {
    $database = new Database();
    $db = $database->getConnection();
    $query = "SELECT c.id AS cart_id, c.product_id, c.quantity, c.unit_price, c.item_data,
                     p.name, p.slug, p.emoji, cat.slug AS category_slug
              FROM carts c
              JOIN products p ON p.id = c.product_id
              LEFT JOIN categories cat ON cat.id = p.category_id
              WHERE c.user_id = :user_id
              ORDER BY c.created_at ASC, c.id ASC";
    $stmt = $db->prepare($query);
    $stmt->execute([":user_id" => $userId]);

    echo json_encode(["status" => "success", "data" => $stmt->fetchAll()]);
} catch (PDOException $e) {
    error_log('Cart API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Keranjang tidak dapat dimuat."]);
}
?>
