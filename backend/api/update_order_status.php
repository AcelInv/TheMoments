<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';

requireLogin();
requireCsrfToken();

$data = json_decode(file_get_contents("php://input"));
$id = isset($data->id) ? (int) $data->id : 0;
$status = $data->status ?? '';
$allowed = ['menunggu', 'dikonfirmasi', 'diproses', 'selesai', 'dibatalkan'];
if (!$id || !in_array($status, $allowed, true)) {
    http_response_code(422); echo json_encode(["status" => "error", "message" => "Data status tidak valid."]); exit;
}

$database = new Database();
$db = $database->getConnection();
try {
    $db->beginTransaction();
    $orderStmt = $db->prepare("SELECT id, user_id, status FROM orders WHERE id = ? FOR UPDATE");
    $orderStmt->execute([$id]);
    $order = $orderStmt->fetch();
    if (!$order) throw new Exception("Pesanan tidak ditemukan.");
    $isOwner = isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] === (int) $order['user_id'];
    if (($_SESSION['user_role'] ?? '') !== 'admin' && !($isOwner && $status === 'dibatalkan' && in_array($order['status'], ['menunggu', 'dikonfirmasi'], true))) {
        throw new Exception("Anda tidak berhak mengubah pesanan ini.");
    }
    if ($order['status'] === 'selesai' && $status !== 'selesai') throw new Exception("Pesanan selesai tidak dapat diubah.");
    if ($order['status'] === 'dibatalkan' && $status !== 'dibatalkan') throw new Exception("Pesanan dibatalkan tidak dapat diaktifkan kembali.");

    if ($status === 'dibatalkan' && $order['status'] !== 'dibatalkan') {
        $items = $db->prepare("SELECT product_id, quantity, item_data FROM order_items WHERE order_id = ?");
        $items->execute([$id]);
        $restock = $db->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        foreach ($items->fetchAll() as $item) {
            $metadata = $item['item_data'] ? json_decode($item['item_data'], true) : [];
            if (($metadata['type'] ?? '') === 'custom_bouquet') {
                foreach (($metadata['components'] ?? []) as $productId => $componentQty) {
                    $restock->execute([(int) $componentQty * (int) $item['quantity'], (int) $productId]);
                }
            } else {
                $restock->execute([(int) $item['quantity'], (int) $item['product_id']]);
            }
        }
        $db->prepare("UPDATE payments SET payment_status = 'gagal' WHERE order_id = ? AND payment_status = 'belum_bayar'")->execute([$id]);
    }
    $db->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $id]);
    $db->commit();
    echo json_encode(["status" => "success", "message" => "Status pesanan berhasil diperbarui."]);
} catch (Throwable $e) {
    if ($db && $db->inTransaction()) $db->rollBack();
    error_log('Order status update error: ' . $e->getMessage());
    http_response_code(422); echo json_encode(["status" => "error", "message" => "Status pesanan tidak dapat diperbarui."]);
}
?>
