<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';

requireAdmin();
requireCsrfToken();

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id) && isset($data->deduct_qty)){
    $productId = filter_var($data->id, FILTER_VALIDATE_INT);
    $deductQty = filter_var($data->deduct_qty, FILTER_VALIDATE_INT);
    if (!$productId || $deductQty === false || $deductQty < 0 || $deductQty > 1000000) {
        http_response_code(422);
        echo json_encode(["status" => "error", "message" => "Data stok tidak valid."]);
        exit;
    }
    try {
        $check = $db->prepare("SELECT stock FROM products WHERE id = :id");
        $check->bindValue(":id", $productId, PDO::PARAM_INT);
        $check->execute();
        $row = $check->fetch();

        if($row) {
            $new_stock = (int) $row['stock'] - $deductQty;
            if($new_stock < 0) $new_stock = 0;

            $query = "UPDATE products SET stock = :new_stock WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":new_stock", $new_stock);
            $stmt->bindValue(":id", $productId, PDO::PARAM_INT);

            if($stmt->execute()){
                echo json_encode(["status" => "success", "message" => "Stok berhasil diperbarui.", "stok_baru" => $new_stock]);
            } else {
                echo json_encode(["status" => "error", "message" => "Gagal memperbarui stok."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Produk tidak ditemukan."]);
        }
    } catch (PDOException $e) {
        error_log('Stock update error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Stok tidak dapat diperbarui."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap. Butuh id dan deduct_qty."]);
}
?>
