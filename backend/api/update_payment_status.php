<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';

requireAdmin();
requireCsrfToken();
loadFloraticaEnvironment();

if (getenv('FLORATICA_ALLOW_MANUAL_PAYMENT_CONFIRMATION') !== 'true') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Konfirmasi pembayaran manual dinonaktifkan. Status pembayaran harus berasal dari verifikasi payment gateway."]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id) && !empty($data->status)){
    if (!in_array($data->status, ['belum_bayar', 'lunas', 'gagal'], true)) {
        http_response_code(422);
        echo json_encode(["status" => "error", "message" => "Status pembayaran tidak valid."]);
        exit;
    }
    try {
        $check = $db->prepare("SELECT id FROM payments WHERE order_id = :oid");
        $check->bindParam(":oid", $data->id);
        $check->execute();

        if($check->fetch()) {
            $query = "UPDATE payments SET payment_status = :status WHERE order_id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":status", $data->status);
            $stmt->bindParam(":id", $data->id);
        } else {
            $query = "INSERT INTO payments (order_id, payment_status, payment_method, amount) 
                      SELECT id, :status, 'transfer_bank', total_amount FROM orders WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":status", $data->status);
            $stmt->bindParam(":id", $data->id);
        }

        if($stmt->execute()){
            echo json_encode(["status" => "success", "message" => "Status pembayaran berhasil diperbarui."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal memperbarui status pembayaran."]);
        }
    } catch (PDOException $e) {
        error_log('Payment status error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Status pembayaran tidak dapat diperbarui."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap."]);
}
?>
