<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';

requireAdmin();
requireCsrfToken();
$data = json_decode(file_get_contents('php://input'));
$orderId = isset($data->order_id) ? (int) $data->order_id : 0;
$status = $data->status ?? 'pending';
if (!$orderId || !in_array($status, ['pending', 'shipped', 'delivered', 'returned'], true)) {
    http_response_code(422); echo json_encode(["status" => "error", "message" => "Data pengiriman tidak valid."]); exit;
}
if (mb_strlen(trim((string) ($data->tracking_number ?? ''))) > 100 || mb_strlen(trim((string) ($data->courier ?? ''))) > 50) {
    http_response_code(422); echo json_encode(["status" => "error", "message" => "Data pengiriman terlalu panjang."]); exit;
}
try {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("INSERT INTO shipments (order_id, tracking_number, courier, status)
      VALUES (:order_id, :tracking_number, :courier, :status)
      ON CONFLICT (order_id) DO UPDATE SET tracking_number = EXCLUDED.tracking_number, courier = EXCLUDED.courier, status = EXCLUDED.status");
    $stmt->execute([
      ':order_id' => $orderId,
      ':tracking_number' => trim((string) ($data->tracking_number ?? '')) ?: null,
      ':courier' => trim((string) ($data->courier ?? '')) ?: null,
      ':status' => $status
    ]);
    echo json_encode(["status" => "success", "message" => "Pengiriman berhasil diperbarui."]);
} catch (Throwable $error) {
    http_response_code(422); echo json_encode(["status" => "error", "message" => "Gagal memperbarui pengiriman."]);
}
?>
