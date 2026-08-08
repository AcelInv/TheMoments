<?php
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';

requireAdmin();

$database = new Database();
$db = $database->getConnection();

try {
    // ── All orders with payment info ──
    $qOrders = "SELECT o.id, o.invoice_number, o.total_amount, o.status, o.shipping_address, o.delivery_at, o.customer_note,
                       o.created_at,
                       u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                       p.payment_method, p.payment_status, p.amount as paid_amount
                FROM orders o
                JOIN users u ON o.user_id = u.id
                LEFT JOIN payments p ON o.id = p.order_id
                ORDER BY o.created_at DESC";
    $stmt = $db->prepare($qOrders);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Attach items ──
    foreach ($orders as &$order) {
        $qItems = "SELECT oi.quantity as qty, oi.price, oi.subtotal, pr.name, pr.emoji
                   FROM order_items oi
                   JOIN products pr ON oi.product_id = pr.id
                   WHERE oi.order_id = :oid";
        $sItems = $db->prepare($qItems);
        $sItems->bindParam(":oid", $order['id']);
        $sItems->execute();
        $order['items'] = $sItems->fetchAll(PDO::FETCH_ASSOC);
        $order['total']  = (int)$order['total_amount'];
        $order['date']   = date('d M Y', strtotime($order['created_at']));
        $order['num']    = $order['invoice_number'];
        $order['payStatus'] = $order['payment_status'] ?: 'belum_bayar';
        $order['payMethod'] = $order['payment_method'] ?: 'belum_dipilih';
        $addr = strtolower(trim($order['shipping_address'] ?? ''));
        // Classify: if address contains meaningful text → delivery, else → pickup
        $isDelivery = !empty($addr)
            && $addr !== 'dikonfirmasi via whatsapp'
            && strlen($addr) > 5;
        $order['delivery_type'] = $isDelivery ? 'antar' : 'ambil';
    }
    unset($order);

    echo json_encode(["status" => "success", "data" => $orders]);
} catch (PDOException $e) {
    error_log('Reports API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Laporan tidak dapat dimuat."]);
}
?>
