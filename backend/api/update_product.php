<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
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

if(!empty($data->id)) {
    try {
    if (!filter_var($data->id, FILTER_VALIDATE_INT) || (int) $data->id < 1) throw new InvalidArgumentException();
    validateProductPayload($data);
    $product->id = $data->id;
    if (!$product->getSingle()) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Produk tidak ditemukan."]);
        exit;
    }
    $product->category_id = isset($data->category_id) ? $data->category_id : $product->category_id;
    $product->name = isset($data->name) ? $data->name : $product->name;
    $product->slug = isset($data->slug) ? $data->slug : $product->slug;
    $product->description = isset($data->description) ? $data->description : $product->description;
    $product->price = isset($data->price) ? $data->price : $product->price;
    $product->promo_price = property_exists($data, 'promo_price') && $data->promo_price !== '' ? $data->promo_price : (property_exists($data, 'promo_price') ? null : $product->promo_price);
    $product->badge = property_exists($data, 'badge') ? (in_array($data->badge, ['new', 'sale', 'popular'], true) ? $data->badge : null) : $product->badge;
    $product->tags = isset($data->tags) && is_array($data->tags) ? json_encode($data->tags, JSON_UNESCAPED_UNICODE) : $product->tags;
    $product->stock = isset($data->stock) ? $data->stock : $product->stock;
    if (property_exists($data, 'image_url')) {
        $image = productImageFromDataUri($data->image_url);
        if ($image) {
            $product->image_url = null;
            $product->image_data = $image['data'];
            $product->image_mime_type = $image['mime'];
        } elseif ($data->image_url === '') {
            $product->image_url = null;
            $product->image_data = null;
            $product->image_mime_type = null;
        }
    }

    if($product->update()) {
        http_response_code(200);
        echo json_encode(array("status" => "success", "message" => "Produk berhasil diupdate."));
    } else {
        http_response_code(503); 
        echo json_encode(array("status" => "error", "message" => "Gagal mengupdate produk."));
    }
    } catch (Throwable $error) {
        http_response_code(422);
        echo json_encode(["status" => "error", "message" => "Data produk tidak valid atau produk tidak dapat diperbarui."]);
    }
} else {
    http_response_code(400); 
    echo json_encode(array("status" => "error", "message" => "Gagal. ID produk harus dilampirkan."));
}
?>
