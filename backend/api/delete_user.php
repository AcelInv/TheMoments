<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/User.php';
include_once __DIR__ . '/auth_helper.php';

requireAdmin();
requireCsrfToken();

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id)){
    $query = "DELETE FROM users WHERE id = :id AND role != 'admin'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data->id);

    if($stmt->execute()){
        if($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(array("message" => "Pengguna berhasil dihapus.", "status" => "success"));
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Gagal menghapus: User tidak ditemukan atau merupakan Admin.", "status" => "error"));
        }
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Gagal menghapus pengguna dari database.", "status" => "error"));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "ID Pengguna tidak ditemukan.", "status" => "error"));
}
?>
