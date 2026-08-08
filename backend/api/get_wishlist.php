<?php
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';

$database = new Database();
$db = $database->getConnection();

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "user_id diperlukan"]);
    exit;
}

checkUserOrAdmin($user_id);

try {
    $query = "SELECT product_id FROM wishlists WHERE user_id = :uid";
    $stmt  = $db->prepare($query);
    $stmt->bindParam(":uid", $user_id);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN); // array of product_id integers
    echo json_encode(["status" => "success", "data" => array_map('intval', $rows)]);
} catch (PDOException $e) {
    error_log('Wishlist API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Wishlist tidak dapat dimuat."]);
}
?>
