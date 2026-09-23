<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// ต้องเป็น Super Admin เท่านั้นที่สามารถเปลี่ยนบทบาทผู้ใช้เป็น Admin โรงเรียนได้
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึงข้อมูลนี้']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? '';
$role = $data['role'] ?? 'admin'; // กำหนดเป็น admin

if (empty($user_id)) {
    echo json_encode(['error' => 'ไม่พบรหัสผู้ใช้งาน']);
    exit;
}

try {
    // อัปเดตบทบาทเป็น admin และอนุมัติทันทีหากยังไม่อนุมัติ
    $stmt = $pdo->prepare('UPDATE users SET role = ?, is_approved = 1 WHERE id = ?');
    $stmt->execute([$role, $user_id]);
    echo json_encode(['message' => 'กำหนดสิทธิ์เป็น Admin โรงเรียนสำเร็จแล้ว']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถปรับปรุงข้อมูลได้: ' . $e->getMessage()]);
}
?>
