<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// ตรวจสอบสิทธิ์ (ต้องเป็น Super Admin เท่านั้น)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึงข้อมูลนี้']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$code = $data['code'] ?? '';
$name = $data['name'] ?? '';
$affiliation = $data['affiliation'] ?? '';
$district = $data['district'] ?? '';
$province = $data['province'] ?? '';

if (strlen($code) !== 8) {
    echo json_encode(['error' => 'รหัสโรงเรียนต้องมี 8 หลัก']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO schools (code, name, affiliation, district, province) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$code, $name, $affiliation, $district, $province]);
    echo json_encode(['message' => 'สร้างโรงเรียนสำเร็จแล้ว']);
} catch (PDOException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'รหัสโรงเรียนนี้อาจมีอยู่ในระบบแล้ว หรือเกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
