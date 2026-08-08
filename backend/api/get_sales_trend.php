<?php
header("Content-Type: application/json; charset=UTF-8");
include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/auth_helper.php';
requireAdmin();

try {
    $db = (new Database())->getConnection();
    $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
    $months = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    $monthly = array_map(fn($number, $name) => ['bulan' => $number, 'nama_bulan' => $name, 'jumlah_pesanan' => 0, 'total_penjualan' => 0, 'jumlah_item' => 0], array_keys($months), $months);
    $sql = "SELECT EXTRACT(MONTH FROM o.created_at)::int AS bulan, COUNT(o.id) AS jumlah_pesanan,
      COALESCE(SUM(o.total_amount), 0) AS total_penjualan, COALESCE(SUM(items.quantity), 0) AS jumlah_item
      FROM orders o
      LEFT JOIN (SELECT order_id, SUM(quantity) AS quantity FROM order_items GROUP BY order_id) items ON items.order_id = o.id
      WHERE EXTRACT(YEAR FROM o.created_at) = ? AND o.status <> 'dibatalkan'
      GROUP BY EXTRACT(MONTH FROM o.created_at)";
    $stmt = $db->prepare($sql); $stmt->execute([$year]);
    foreach ($stmt->fetchAll() as $row) {
        $index = (int) $row['bulan'] - 1;
        $monthly[$index]['jumlah_pesanan'] = (int) $row['jumlah_pesanan'];
        $monthly[$index]['total_penjualan'] = (float) $row['total_penjualan'];
        $monthly[$index]['jumlah_item'] = (int) $row['jumlah_item'];
    }
    $years = $db->query("SELECT DISTINCT EXTRACT(YEAR FROM created_at)::int AS y FROM orders ORDER BY y DESC")->fetchAll(PDO::FETCH_COLUMN);
    if (!$years) $years = [$year];
    $top = $db->prepare("SELECT p.name, p.emoji, SUM(oi.quantity) AS total_qty, SUM(oi.subtotal) AS total_rev
      FROM order_items oi JOIN orders o ON o.id = oi.order_id JOIN products p ON p.id = oi.product_id
      WHERE EXTRACT(YEAR FROM o.created_at) = ? AND o.status <> 'dibatalkan'
      GROUP BY p.id, p.name, p.emoji ORDER BY total_qty DESC LIMIT 5");
    $top->execute([$year]);
    echo json_encode(["status" => "success", "year" => $year, "available_years" => array_map('intval', $years), "monthly" => $monthly, "top_products" => $top->fetchAll()]);
} catch (Throwable $e) {
    http_response_code(500); echo json_encode(["status" => "error", "message" => "Gagal mengambil tren penjualan."]);
}
?>
