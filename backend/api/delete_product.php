<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Metode request tidak diizinkan."]);
    exit;
}

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Product.php';
include_once __DIR__ . '/auth_helper.php';

requireAdmin();
requireCsrfToken();

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));
$id = !empty($data->id) ? $data->id : (!empty($_GET['id']) ? $_GET['id'] : null);

if (!empty($id)) {
    try {
      if ($db) {
        $product = new Product($db);
        $product->id = $id;
        if (!$product->delete()) {
            throw new RuntimeException('Produk tidak dapat dihapus.');
        }
      }
      http_response_code(200);
      echo json_encode(array("status" => "success", "message" => "Produk berhasil dihapus."));
    } catch (Throwable $e) {
      http_response_code(409);
      echo json_encode(array("status" => "error", "message" => "Produk tidak dapat dihapus karena masih digunakan pada data transaksi."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Gagal. ID produk tidak dilampirkan."));
}
?>
