<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

try {
    // ดึงหมวดหมู่ทั้งหมด
    $stmt = $pdo->query("SELECT * FROM behavior_categories ORDER BY id ASC");
    $categories = $stmt->fetchAll();

    // ดึงตัวเลือกทั้งหมด
    $stmt = $pdo->query("SELECT * FROM behavior_options ORDER BY id ASC");
    $options = $stmt->fetchAll();

    // จัดกลุ่มตัวเลือกตามหมวดหมู่
    foreach ($categories as &$cat) {
        $cat['options'] = array_filter($options, function($opt) use ($cat) {
            return $opt['category_id'] == $cat['id'];
        });
        $cat['options'] = array_values($cat['options']);
    }

    echo json_encode([
        'status' => 'success',
        'categories' => $categories
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
