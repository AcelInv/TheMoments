<?php
function productImageFromDataUri($value) {
    if (!is_string($value) || !preg_match('#^data:(image/(?:jpeg|png|webp|gif));base64,([A-Za-z0-9+/=\s]+)$#', $value, $matches)) return null;
    $binary = base64_decode($matches[2], true);
    if ($binary === false || strlen($binary) > 5 * 1024 * 1024) {
        throw new InvalidArgumentException('Gambar tidak valid atau melebihi 5 MB.');
    }
    $imageInfo = @getimagesizefromstring($binary);
    if ($imageInfo === false || ($imageInfo['mime'] ?? '') !== $matches[1] || $imageInfo[0] > 8000 || $imageInfo[1] > 8000) {
        throw new InvalidArgumentException('Berkas gambar tidak valid.');
    }
    return ['data' => $binary, 'mime' => $matches[1]];
}

function validateProductPayload(object $data, bool $isCreate = false): void {
    $name = trim((string) ($data->name ?? ''));
    $slug = trim((string) ($data->slug ?? ''));
    $price = $data->price ?? null;
    $promo = $data->promo_price ?? null;
    $categoryId = $data->category_id ?? null;
    $stock = $data->stock ?? 0;
    if (($isCreate && (!$name || !$slug || $price === null || $categoryId === null))
        || ($name && mb_strlen($name) > 150)
        || ($slug && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug))
        || ($price !== null && (!is_numeric($price) || (float) $price < 0 || (float) $price > 9999999999))
        || ($promo !== null && $promo !== '' && (!is_numeric($promo) || (float) $promo < 0 || ($price !== null && (float) $promo > (float) $price)))
        || ($categoryId !== null && (!filter_var($categoryId, FILTER_VALIDATE_INT) || (int) $categoryId < 1))
        || (!filter_var($stock, FILTER_VALIDATE_INT) || (int) $stock < 0 || (int) $stock > 1000000)
        || isset($data->description) && mb_strlen((string) $data->description) > 5000
        || isset($data->tags) && (!is_array($data->tags) || count($data->tags) > 20)
        || isset($data->emoji) && (mb_strlen((string) $data->emoji) > 16 || preg_match('/[<>&]/', (string) $data->emoji))) {
        throw new InvalidArgumentException('Data produk tidak valid.');
    }
}
?>
