<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';

function failOrder($message, $status = 422) {
    http_response_code($status);
    echo json_encode(["status" => "error", "message" => $message]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));
$userId = isset($data->user_id) ? (int) $data->user_id : 0;
$items = isset($data->items) && is_array($data->items) ? $data->items : [];

if (!$userId || !$items || count($items) > 100) failOrder("Data pesanan tidak lengkap.");
checkUserOrAdmin($userId);
requireCsrfToken();
rateLimit('create-order', 10, 300);

try {
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) failOrder("Koneksi database tidak tersedia.", 500);
    $db->beginTransaction();

    $findProduct = $db->prepare("SELECT id, name, slug, price, promo_price, stock FROM products WHERE id = :id AND is_active = TRUE FOR UPDATE");
    $findCustom = $db->prepare("SELECT id, name, price, promo_price FROM products WHERE slug = 'custom-bouquet' AND is_active = TRUE LIMIT 1 FOR UPDATE");
    $reduceStock = $db->prepare("UPDATE products SET stock = stock - :qty WHERE id = :id");
    $lineItems = [];
    $total = 0;

    foreach ($items as $item) {
        $qty = isset($item->qty) ? (int) $item->qty : 0;
        $rawId = isset($item->id) ? (string) $item->id : '';
        if ($qty < 1 || $qty > 1000 || $rawId === '') throw new Exception("Item pesanan tidak valid.");

        $isCustom = str_starts_with($rawId, 'custom-');
        if ($isCustom) {
            $components = isset($item->components) && is_object($item->components)
                ? get_object_vars($item->components) : [];
            if (!$components) throw new Exception("Komposisi buket custom tidak valid.");
            $findCustom->execute();
            $customProduct = $findCustom->fetch();
            if (!$customProduct) throw new Exception("Produk buket custom belum tersedia.");

            $unitPrice = $customProduct['promo_price'] !== null ? (float) $customProduct['promo_price'] : (float) $customProduct['price'];
            $validComponents = [];
            foreach ($components as $componentId => $componentQty) {
                $componentId = (int) $componentId;
                $componentQty = (int) $componentQty;
                if ($componentId < 1 || $componentQty < 1) continue;
                $findProduct->execute([':id' => $componentId]);
                $component = $findProduct->fetch();
                $needed = $componentQty * $qty;
                if (!$component || $component['stock'] < $needed) {
                    throw new Exception("Stok komponen buket tidak mencukupi.");
                }
                $price = $component['promo_price'] !== null ? (float) $component['promo_price'] : (float) $component['price'];
                $unitPrice += $price * $componentQty;
                $validComponents[(string) $componentId] = $componentQty;
                $reduceStock->execute([':qty' => $needed, ':id' => $componentId]);
            }
            if (!$validComponents) throw new Exception("Komposisi buket custom kosong.");
            $lineItems[] = [
                'product_id' => (int) $customProduct['id'], 'quantity' => $qty,
                'price' => $unitPrice, 'subtotal' => $unitPrice * $qty,
                'item_data' => json_encode(['type' => 'custom_bouquet', 'components' => $validComponents], JSON_UNESCAPED_UNICODE)
            ];
            $total += $unitPrice * $qty;
            continue;
        }

        $productId = (int) $rawId;
        if ($productId < 1 || (string) $productId !== $rawId) throw new Exception("Produk pesanan tidak valid.");
        $findProduct->execute([':id' => $productId]);
        $product = $findProduct->fetch();
        if (!$product) throw new Exception("Produk tidak ditemukan.");
        if ($product['stock'] < $qty) throw new Exception("Stok produk '" . $product['name'] . "' tidak mencukupi.");
        $price = $product['promo_price'] !== null ? (float) $product['promo_price'] : (float) $product['price'];
        $lineItems[] = ['product_id' => $productId, 'quantity' => $qty, 'price' => $price, 'subtotal' => $price * $qty, 'item_data' => null];
        $total += $price * $qty;
        $reduceStock->execute([':qty' => $qty, ':id' => $productId]);
    }

    $invoice = 'FLR-' . date('Ymd-His') . '-' . strtoupper(bin2hex(random_bytes(3)));
    $address = trim((string) ($data->shipping_address ?? 'Dikonfirmasi via WhatsApp'));
    if (mb_strlen($address) < 5 || mb_strlen($address) > 2000) throw new Exception('Alamat pengiriman tidak valid.');
    $deliveryAt = null;
    if (!empty($data->delivery_at)) {
        $deliveryAt = new DateTimeImmutable((string) $data->delivery_at);
        if ($deliveryAt < new DateTimeImmutable('today')) throw new Exception('Jadwal pengiriman tidak valid.');
        $deliveryAt = $deliveryAt->format('Y-m-d H:i:sP');
    }
    $insertOrder = $db->prepare("INSERT INTO orders (user_id, invoice_number, total_amount, status, shipping_address, delivery_at, customer_note)
      VALUES (:user_id, :invoice, :total, 'menunggu', :address, :delivery_at, :note) RETURNING id");
    $insertOrder->execute([
        ':user_id' => $userId, ':invoice' => $invoice, ':total' => $total,
        ':address' => $address,
        ':delivery_at' => $deliveryAt,
        ':note' => mb_substr(trim((string) ($data->customer_note ?? '')), 0, 1000) ?: null
    ]);
    $orderId = (int) $insertOrder->fetchColumn();
    $insertItem = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal, item_data)
      VALUES (:order_id, :product_id, :quantity, :price, :subtotal, :item_data)");
    foreach ($lineItems as $line) {
        $insertItem->execute([':order_id' => $orderId] + $line);
    }
    // Metode pembayaran dipilih admin setelah komunikasi dengan pelanggan,
    // bukan dikendalikan oleh request checkout dari frontend.
    $method = 'belum_dipilih';
    $db->prepare("INSERT INTO payments (order_id, payment_method, payment_status, amount) VALUES (?, ?, 'belum_bayar', ?)")
       ->execute([$orderId, $method, $total]);
    $db->prepare("DELETE FROM carts WHERE user_id = ?")->execute([$userId]);
    $db->commit();
    echo json_encode(["status" => "success", "message" => "Pesanan berhasil dibuat.", "order_id" => $orderId, "invoice_number" => $invoice, "total_amount" => $total]);
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    error_log('Order creation error: ' . $e->getMessage());
    failOrder('Pesanan tidak dapat diproses. Periksa data pesanan dan stok lalu coba kembali.');
}
?>
