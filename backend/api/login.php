<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/User.php';
include_once __DIR__ . '/auth_helper.php';

requireCsrfToken();
rateLimit('login', 5, 60);

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// ── Rate Limiting (Max 5 attempts per minute) ──
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
    if (time() - $_SESSION['last_login_attempt_time'] < 60) {
        http_response_code(429);
        echo json_encode(array("status" => "error", "message" => "Terlalu banyak percobaan login. Silakan tunggu 1 menit."));
        exit;
    } else {
        $_SESSION['login_attempts'] = 0;
    }
}

if(!empty($data->email) && !empty($data->password)){
    if(!filter_var($data->email, FILTER_VALIDATE_EMAIL)){
        http_response_code(400);
        echo json_encode(array("message" => "Format email tidak valid.", "status" => "error"));
        exit;
    }
    $user->email = $data->email;
    $email_exists = $user->emailExists();

    if($email_exists && password_verify($data->password, $user->password)){
        // Cegah Session Fixation
        session_regenerate_id(true);
        $user->refreshPasswordHash((string) $data->password);

        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_role'] = ($user->role == 'customer' ? 'user' : $user->role);
        $_SESSION['user_name'] = $user->name;
        $_SESSION['user_email'] = $user->email;
        unset($_SESSION['login_attempts']);

        http_response_code(200);
        echo json_encode(array(
            "status" => "success",
            "message" => "Login berhasil.",
            "user" => array(
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "phone" => $user->phone,
                "role" => ($user->role == 'customer' ? 'user' : $user->role)
            )
        ));
    } else {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['last_login_attempt_time'] = time();

        http_response_code(401);
        echo json_encode(array("message" => "Email atau password salah.", "status" => "error"));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Data tidak lengkap.", "status" => "error"));
}
?>
