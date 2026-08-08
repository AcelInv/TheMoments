<?php
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';

$database = new Database();
$db = $database->getConnection();

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if ($user_id) {
    checkUserOrAdmin($user_id);
} else {
    requireAdmin();
}

try {
    if($user_id) {
        $query = "SELECT o.*, u.name as customer_name, u.email as customer_email, p.payment_method, p.payment_status,
                         s.courier, s.tracking_number, s.status AS shipment_status
                  FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  LEFT JOIN payments p ON o.id = p.order_id
                  LEFT JOIN shipments s ON s.order_id = o.id
                  WHERE o.user_id = :uid 
                  ORDER BY o.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":uid", $user_id);
    } else {
        $query = "SELECT o.*, u.name as customer_name, u.email as customer_email, p.payment_method, p.payment_status,
                         s.courier, s.tracking_number, s.status AS shipment_status
                  FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  LEFT JOIN payments p ON o.id = p.order_id
                  LEFT JOIN shipments s ON s.order_id = o.id
                  ORDER BY o.created_at DESC";
        $stmt = $db->prepare($query);
    }
    
    $stmt->execute();
    $orders = $stmt->fetchAll();

    foreach($orders as &$order) {
        $qItems = "SELECT oi.*, p.name as name, p.image_url, p.emoji,
                          CASE WHEN p.image_data IS NOT NULL THEN 1 ELSE 0 END AS has_image_data
                   FROM order_items oi 
                   JOIN products p ON oi.product_id = p.id 
                   WHERE oi.order_id = :oid";
        $sItems = $db->prepare($qItems);
        $sItems->bindParam(":oid", $order['id']);
        $sItems->execute();
        $order['items'] = $sItems->fetchAll();
        foreach ($order['items'] as &$item) {
            $item['name'] = htmlspecialchars((string) ($item['name'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $item['emoji'] = htmlspecialchars((string) ($item['emoji'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $item['quantity'] = (int) $item['quantity'];
            $item['price'] = (float) $item['price'];
            $item['subtotal'] = (float) $item['subtotal'];
            $item['item_data'] = !empty($item['item_data']) ? json_decode($item['item_data'], true) : null;
            if ((int) $item['has_image_data'] === 1) {
                $item['image_url'] = 'backend/api/product_image.php?id=' . $item['product_id'];
            }
            unset($item['has_image_data']);
        }
        unset($item);
        $order['num'] = $order['invoice_number'];
        $order['total'] = (int)$order['total_amount'];
        $order['userId'] = $order['user_id'];
        $order['customerName'] = $order['customer_name'];
        $order['customerEmail'] = $order['customer_email'];
        $paymentMethod = $order['payment_method'] ?: 'belum_dipilih';
        $order['payMethod'] = $paymentMethod === 'bank_transfer' ? 'transfer' : $paymentMethod;
        $order['payStatus'] = $order['payment_status'] ?: 'belum_bayar';
        $order['date'] = date('d M Y', strtotime($order['created_at']));
    }

    echo json_encode(["status" => "success", "data" => $orders]);
} catch (PDOException $e) {
    error_log('Orders API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Pesanan tidak dapat dimuat."]);
}
?>
