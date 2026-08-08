<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/User.php';
include_once __DIR__ . '/auth_helper.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id) && !empty($data->name)){
    checkUserOrAdmin($data->id);
    requireCsrfToken();
    if (!filter_var($data->id, FILTER_VALIDATE_INT) || (int) $data->id < 1 || mb_strlen(trim((string) $data->name)) < 2 || mb_strlen(trim((string) $data->name)) > 100) {
        http_response_code(422);
        echo json_encode(["status" => "error", "message" => "Data profil tidak valid."]);
        exit;
    }
    $user->id = $data->id;
    $user->name = $data->name;
    $user->phone = preg_replace('/[^0-9+\-() ]/', '', (string) ($data->phone ?? ''));

    if($user->update()){
        echo json_encode(["status" => "success", "message" => "Profil berhasil diperbarui."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal memperbarui profil."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap."]);
}
?>
