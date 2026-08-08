<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

$user_id    = isset($data->user_id)    ? (int)$data->user_id    : 0;
$product_id = isset($data->product_id) ? (int)$data->product_id : 0;

if (!$user_id || !$product_id) {
    echo json_encode(["status" => "error", "message" => "user_id dan product_id diperlukan"]);
    exit;
}

checkUserOrAdmin($user_id);
requireCsrfToken();

try {
    // Check if already wishlisted
    $check = $db->prepare("SELECT id FROM wishlists WHERE user_id = :uid AND product_id = :pid");
    $check->bindParam(":uid", $user_id);
    $check->bindParam(":pid", $product_id);
    $check->execute();

    if ($check->fetch()) {
        // Remove
        $del = $db->prepare("DELETE FROM wishlists WHERE user_id = :uid AND product_id = :pid");
        $del->bindParam(":uid", $user_id);
        $del->bindParam(":pid", $product_id);
        $del->execute();
        echo json_encode(["status" => "success", "action" => "removed"]);
    } else {
        // Add
        $ins = $db->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (:uid, :pid)");
        $ins->bindParam(":uid", $user_id);
        $ins->bindParam(":pid", $product_id);
        $ins->execute();
        echo json_encode(["status" => "success", "action" => "added"]);
    }
} catch (PDOException $e) {
    error_log('Wishlist update error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Wishlist tidak dapat diperbarui."]);
}
?>
