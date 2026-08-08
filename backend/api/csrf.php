<?php
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, private');

require_once __DIR__ . '/auth_helper.php';

echo json_encode([
    'status' => 'success',
    'token' => csrfToken()
]);
?>
