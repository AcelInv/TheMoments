<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Product.php';
include_once __DIR__ . '/auth_helper.php';
include_once __DIR__ . '/product_image_helper.php';

requireAdmin();
requireCsrfToken();

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->name) && !empty($data->slug) && isset($data->price) && isset($data->category_id)) {
    try {
    validateProductPayload($data, true);
    $product->category_id = $data->category_id;
    $product->name = $data->name;
    $product->slug = $data->slug;
    $product->description = $data->description ?? "";
    $product->price = $data->price;
    $product->promo_price = isset($data->promo_price) && $data->promo_price !== '' ? $data->promo_price : null;
    $product->badge = isset($data->badge) && in_array($data->badge, ['new', 'sale', 'popular'], true) ? $data->badge : null;
    $product->tags = json_encode(isset($data->tags) && is_array($data->tags) ? $data->tags : [], JSON_UNESCAPED_UNICODE);
    $product->stock = $data->stock ?? 0;
    $image = productImageFromDataUri($data->image_url ?? null);
    // Produk baru hanya menerima gambar tervalidasi yang disimpan sebagai BYTEA.
    $product->image_url = null;
    $product->image_data = $image['data'] ?? null;
    $product->image_mime_type = $image['mime'] ?? null;
    $product->emoji = null;

    if($product->create()) {
        http_response_code(201); 
        echo json_encode(array("status" => "success", "message" => "Produk berhasil dibuat."));
    } else {
        http_response_code(503); 
        echo json_encode(array("status" => "error", "message" => "Gagal membuat produk."));
    }
    } catch (Throwable $error) {
        http_response_code(422);
        echo json_encode(["status" => "error", "message" => "Data produk tidak valid."]);
    }
} else {
    http_response_code(400); 
    echo json_encode(array("status" => "error", "message" => "Gagal. Data tidak lengkap. Pastikan mengirim name, slug, price, dan category_id."));
}
?>
