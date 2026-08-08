<?php
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../config/Database.php';

$database = new Database();
$db = $database->getConnection();

try {
    $query = "SELECT * FROM categories ORDER BY name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $categories = $stmt->fetchAll();

    echo json_encode(["status" => "success", "data" => $categories]);
} catch (PDOException $e) {
    error_log('Categories API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Kategori tidak dapat dimuat."]);
}
?>
