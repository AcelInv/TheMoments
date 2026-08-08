<?php

header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Product.php';

$database = new Database();
$db = $database->getConnection();

try {
    $stmt = $db->prepare("SELECT p.id, p.category_id, p.name, p.emoji, p.slug, p.description, p.price, p.promo_price, p.badge, p.tags, p.is_active, p.stock, p.image_url, p.created_at, p.updated_at,
        CASE WHEN p.image_data IS NOT NULL THEN 1 ELSE 0 END AS has_image_data, c.slug AS category_slug, c.name AS category_name,
        COALESCE(ROUND(AVG(r.rating), 1), 0) AS rating, COUNT(r.id) AS review_count
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN reviews r ON r.product_id = p.id
        WHERE p.is_active = TRUE
        GROUP BY p.id, c.slug, c.name
        ORDER BY p.created_at DESC, p.id DESC");
    $stmt->execute();
    $products = $stmt->fetchAll();
    foreach ($products as &$product) {
        $product['id'] = (int) $product['id'];
        $product['category_id'] = (int) $product['category_id'];
        $product['price'] = (float) $product['price'];
        $product['promo_price'] = $product['promo_price'] !== null ? (float) $product['promo_price'] : null;
        $product['stock'] = (int) $product['stock'];
        $product['rating'] = (float) $product['rating'];
        $product['review_count'] = (int) $product['review_count'];
        $product['emoji'] = htmlspecialchars((string) ($product['emoji'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $product['tags'] = $product['tags'] ? json_decode($product['tags'], true) : [];
        if (!is_array($product['tags'])) $product['tags'] = [];
        if ((int) $product['has_image_data'] === 1) {
            // URL berubah setiap produk diperbarui agar browser tidak memakai foto lama dari cache.
            $version = rawurlencode((string) ($product['updated_at'] ?? ''));
            $product['image_url'] = 'backend/api/product_image.php?id=' . $product['id'] . '&v=' . $version;
        }
        unset($product['has_image_data']);
    }
    echo json_encode(["status" => "success", "data" => $products]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Gagal mengambil katalog."]);
}
?>
