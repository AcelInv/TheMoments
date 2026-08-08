<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, DELETE");
include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';
$data = json_decode(file_get_contents("php://input"));
$reviewId = isset($data->id) ? (int) $data->id : 0;
if (!$reviewId) { http_response_code(422); echo json_encode(["status" => "error", "message" => "ID ulasan diperlukan."]); exit; }
requireLogin();
requireCsrfToken();
try {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("DELETE FROM reviews WHERE id = ? AND (user_id = ? OR ? = 'admin')");
    $stmt->execute([$reviewId, $_SESSION['user_id'], $_SESSION['user_role']]);
    if (!$stmt->rowCount()) throw new Exception();
    echo json_encode(["status" => "success", "message" => "Ulasan dihapus."]);
} catch (Throwable $e) {
    http_response_code(404); echo json_encode(["status" => "error", "message" => "Ulasan tidak ditemukan atau tidak diizinkan."]);
}
?>
