<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';

$data = json_decode(file_get_contents("php://input"));
$userId = isset($data->user_id) ? (int) $data->user_id : 0;
$items = isset($data->items) && is_array($data->items) ? $data->items : null;

if (!$userId || $items === null) {
    echo json_encode(["status" => "error", "message" => "Data keranjang tidak lengkap."]);
    exit;
}

checkUserOrAdmin($userId);
requireCsrfToken();
if (count($items) > 100) {
    http_response_code(422);
    echo json_encode(["status" => "error", "message" => "Keranjang melebihi batas item."]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $db->beginTransaction();

    $delete = $db->prepare("DELETE FROM carts WHERE user_id = :user_id");
    $delete->execute([":user_id" => $userId]);

    $findProduct = $db->prepare("SELECT id, name, price, promo_price FROM products WHERE id = :id AND is_active = TRUE");
    $findCustomProduct = $db->prepare("SELECT id, price, promo_price FROM products WHERE slug = 'custom-bouquet' AND is_active = TRUE LIMIT 1");
    $insert = $db->prepare(
        "INSERT INTO carts (user_id, product_id, quantity, unit_price, item_data)
         VALUES (:user_id, :product_id, :quantity, :unit_price, :item_data)"
    );

    foreach ($items as $item) {
        $quantity = isset($item->qty) ? (int) $item->qty : 0;
        if ($quantity < 1 || $quantity > 1000) {
            throw new Exception("Jumlah produk tidak valid.");
        }

        $rawId = isset($item->id) ? (string) $item->id : '';
        $isCustom = strpos($rawId, 'custom-') === 0;

        if ($isCustom) {
            $components = isset($item->components) && is_object($item->components)
                ? get_object_vars($item->components)
                : [];
            if (!$components) {
                throw new Exception("Komposisi buket custom tidak valid.");
            }

            $findCustomProduct->execute();
            $customProduct = $findCustomProduct->fetch();
            if (!$customProduct) {
                throw new Exception("Produk buket custom tidak ditemukan.");
            }

            $customNameParts = [];
            $validComponents = [];
            $unitPrice = $customProduct['promo_price'] !== null ? (float) $customProduct['promo_price'] : (float) $customProduct['price'];
            foreach ($components as $componentId => $componentQty) {
                $componentId = (int) $componentId;
                $componentQty = (int) $componentQty;
                if ($componentId < 1 || $componentQty < 1) continue;

                $findProduct->execute([":id" => $componentId]);
                $component = $findProduct->fetch();
                if (!$component) {
                    throw new Exception("Komponen buket tidak ditemukan.");
                }
                $validComponents[(string) $componentId] = $componentQty;
                $customNameParts[] = $component['name'] . ' (' . $componentQty . 'x)';
                $componentPrice = $component['promo_price'] !== null ? (float) $component['promo_price'] : (float) $component['price'];
                $unitPrice += $componentPrice * $componentQty;
            }

            if (!$validComponents) {
                throw new Exception("Komposisi buket custom kosong.");
            }

            $itemData = json_encode([
                'type' => 'custom_bouquet',
                'name' => 'Buket Custom: ' . implode(', ', $customNameParts),
                'emoji' => '',
                'bg' => '#FFF0F0',
                'components' => $validComponents
            ], JSON_UNESCAPED_UNICODE);
            $insert->execute([
                ':user_id' => $userId,
                ':product_id' => $customProduct['id'],
                ':quantity' => $quantity,
                ':unit_price' => $unitPrice,
                ':item_data' => $itemData
            ]);
            continue;
        }

        $productId = (int) $rawId;
        if ($productId < 1 || (string) $productId !== $rawId) {
            throw new Exception("Produk keranjang tidak valid.");
        }
        $findProduct->execute([":id" => $productId]);
        $product = $findProduct->fetch();
        if (!$product) {
            throw new Exception("Produk keranjang tidak ditemukan.");
        }

        $insert->execute([
            ':user_id' => $userId,
            ':product_id' => $product['id'],
            ':quantity' => $quantity,
            ':unit_price' => $product['promo_price'] !== null ? $product['promo_price'] : $product['price'],
            ':item_data' => null
        ]);
    }

    $db->commit();
    echo json_encode(["status" => "success"]);
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    error_log('Cart sync error: ' . $e->getMessage());
    http_response_code(422);
    echo json_encode(["status" => "error", "message" => "Keranjang tidak dapat disimpan. Periksa item dan coba lagi."]);
}
?>
