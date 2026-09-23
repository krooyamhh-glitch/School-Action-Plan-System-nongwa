<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึง']);
    exit;
}

if (!isset($_FILES['logo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ไม่พบไฟล์ที่อัปโหลด']);
    exit;
}

$file = $_FILES['logo'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 1024 * 1024; // 1MB

if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'อนุญาตเฉพาะไฟล์รูปภาพ (JPG, PNG, GIF, WEBP)']);
    exit;
}

if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['error' => 'ขนาดไฟล์ต้องไม่เกิน 1MB']);
    exit;
}

$upload_dir = __DIR__ . '/../../uploads/app/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'app_logo_' . time() . '.' . $extension;
$target_path = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    $relative_path = 'uploads/app/' . $filename;
    
    // บันทึกลงฐานข้อมูล
    try {
        $stmt = $pdo->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES ('app_logo', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$relative_path, $relative_path]);
        
        echo json_encode([
            'status' => 'success',
            'url' => $relative_path
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'บันทึกข้อมูลไม่สำเร็จ: ' . $e->getMessage()]);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการบันทึกไฟล์']);
}
?>
