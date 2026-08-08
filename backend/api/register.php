<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/User.php';
include_once __DIR__ . '/auth_helper.php';

requireCsrfToken();
rateLimit('register', 3, 3600);

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->name) &&
    !empty($data->email) &&
    !empty($data->password)
){
    if(!filter_var($data->email, FILTER_VALIDATE_EMAIL) || strlen((string) $data->email) > 100){
        http_response_code(400);
        echo json_encode(array("message" => "Format email tidak valid.", "status" => "error"));
        exit;
    }

    if (mb_strlen(trim((string) $data->name)) < 2 || mb_strlen(trim((string) $data->name)) > 100 || strlen((string) $data->password) < 10 || strlen((string) $data->password) > 128) {
        http_response_code(422);
        echo json_encode(["message" => "Nama atau password tidak memenuhi ketentuan keamanan.", "status" => "error"]);
        exit;
    }
    $user->name = trim((string) $data->name);
    $user->email = $data->email;
    $user->password = $data->password;
    $user->phone = isset($data->phone) ? preg_replace('/[^0-9+\-() ]/', '', (string) $data->phone) : '';

    if($user->emailExists()){
        http_response_code(400);
        echo json_encode(array("message" => "Email sudah terdaftar.", "status" => "error"));
    } else {
        if($user->create()){
            $last_id = $user->created_id;
            session_regenerate_id(true);
            $_SESSION['user_id'] = $last_id;
            $_SESSION['user_role'] = 'user';
            $_SESSION['user_name'] = $user->name;
            $_SESSION['user_email'] = $user->email;

            http_response_code(201); 
            echo json_encode(array(
                "status" => "success",
                "message" => "Akun berhasil dibuat.",
                "user" => array(
                    "id" => $last_id,
                    "name" => $user->name,
                    "email" => $user->email,
                    "phone" => $user->phone,
                    "role" => "user"
                )
            ));
        } else {
            http_response_code(503); 
            echo json_encode(array("message" => "Gagal membuat akun.", "status" => "error"));
        }
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Data tidak lengkap.", "status" => "error"));
}
?>
