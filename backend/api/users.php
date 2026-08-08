<?php
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/User.php';
include_once __DIR__ . '/auth_helper.php';

requireAdmin();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT id, name, email, role, phone, created_at FROM users ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if($rows){
    $users_arr = array("status" => "success", "data" => array());
    foreach ($rows as $row){
        extract($row);
        $user_item = array(
            "id" => $id,
            "name" => $name,
            "email" => $email,
            "role" => ($role == 'customer' ? 'user' : $role),
            "phone" => $phone,
            "created_at" => $created_at
        );
        array_push($users_arr["data"], $user_item);
    }

    http_response_code(200);
    echo json_encode($users_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Tidak ada pengguna ditemukan.", "status" => "error"));
}
?>
