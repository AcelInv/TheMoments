<?php

header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Product.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);
$product->id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(array("status" => "error", "message" => "Parameter ID tidak ditemukan.")));

if($product->getSingle()) {
    $product_arr = array(
        "id" =>  $product->id,
        "name" => $product->name,
        "slug" => $product->slug,
        "description" => $product->description,
        "price" => (float)$product->price,
        "stock" => (int)$product->stock,
        "category_id" => $product->category_id,
        "image_url" => $product->image_data ? 'backend/api/product_image.php?id=' . $product->id : $product->image_url,
        "promo_price" => $product->promo_price !== null ? (float)$product->promo_price : null,
        "badge" => $product->badge,
        "tags" => $product->tags ? json_decode($product->tags, true) : []
    );

    http_response_code(200);
    echo json_encode(array("status" => "success", "data" => $product_arr));
} else {
    http_response_code(404);
    echo json_encode(array("status" => "error", "message" => "Produk tidak ditemukan."));
}
?>
