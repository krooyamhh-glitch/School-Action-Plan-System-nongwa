<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึง']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$app_name = $data['app_name'] ?? '';

if (empty($app_name)) {
    echo json_encode(['error' => 'กรุณาระบุชื่อแอป']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES ('app_name', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$app_name, $app_name]);
    
    echo json_encode(['status' => 'success', 'message' => 'บันทึกการตั้งค่าสำเร็จ']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
