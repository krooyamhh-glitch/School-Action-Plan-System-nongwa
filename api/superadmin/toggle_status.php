<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

if (!($pdo instanceof PDO)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูล MySQL ได้ กรุณาตรวจสอบการตั้งค่าฐานข้อมูล'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$school_id = (int)($data['school_id'] ?? 0);
$is_active = (int)($data['is_active'] ?? 1);
$statusStr = $is_active ? 'active' : 'inactive';

try {
    $stmt = $pdo->prepare("UPDATE schools SET status = ? WHERE id = ?");
    $stmt->execute([$statusStr, $school_id]);

    echo json_encode([
        'status' => 'success',
        'message' => $is_active ? 'เปิดใช้งานสถานศึกษาสำเร็จ' : 'ระงับสถานศึกษาเรียบร้อยแล้ว'
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการปรับปรุงสถานะ: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
