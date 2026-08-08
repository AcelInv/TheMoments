<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';

requireAdmin();
requireCsrfToken();
rateLimit('update-payment-method', 60, 300);

$data = json_decode(file_get_contents('php://input'));
$orderId = isset($data->id) ? (int) $data->id : 0;
$method = strtolower(trim((string) ($data->payment_method ?? '')));
$allowedMethods = ['belum_dipilih', 'qris', 'transfer', 'cash'];

if ($orderId < 1 || !in_array($method, $allowedMethods, true)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Metode pembayaran tidak valid.']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        throw new RuntimeException('Koneksi database tidak tersedia.');
    }

    $checkOrder = $db->prepare('SELECT id, total_amount FROM orders WHERE id = :id');
    $checkOrder->execute([':id' => $orderId]);
    $order = $checkOrder->fetch();
    if (!$order) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.']);
        exit;
    }

    $checkPayment = $db->prepare('SELECT id FROM payments WHERE order_id = :order_id');
    $checkPayment->execute([':order_id' => $orderId]);

    if ($checkPayment->fetch()) {
        $statement = $db->prepare('UPDATE payments SET payment_method = :method, updated_at = CURRENT_TIMESTAMP WHERE order_id = :order_id');
        $statement->execute([':method' => $method, ':order_id' => $orderId]);
    } else {
        $statement = $db->prepare("INSERT INTO payments (order_id, payment_method, payment_status, amount)
            VALUES (:order_id, :method, 'belum_bayar', :amount)");
        $statement->execute([
            ':order_id' => $orderId,
            ':method' => $method,
            ':amount' => $order['total_amount']
        ]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Metode pembayaran berhasil diperbarui.']);
} catch (Throwable $error) {
    error_log('Payment method update error: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Metode pembayaran tidak dapat diperbarui.']);
}
