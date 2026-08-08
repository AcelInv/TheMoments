<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once __DIR__ . '/auth_helper.php';
requireCsrfToken();

session_unset();
session_destroy();
$cookie = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 3600,
    'path' => $cookie['path'] ?? '/',
    'domain' => $cookie['domain'] ?? '',
    'secure' => $cookie['secure'] ?? false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

echo json_encode(["status" => "success", "message" => "Logged out successfully."]);
?>
