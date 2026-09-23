<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// ต้องเป็น Admin โรงเรียนเท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? '';
$is_academic = $data['is_academic'] ?? false;

if (empty($user_id)) {
    echo json_encode(['error' => 'ไม่พบรหัสผู้ใช้งาน']);
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE users SET is_academic = ? WHERE id = ? AND school_id = ?');
    $stmt->execute([$is_academic ? 1 : 0, $user_id, $_SESSION['school_id']]);
    echo json_encode(['message' => 'ปรับปรุงสิทธิ์งานวิชาการสำเร็จแล้ว']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถปรับปรุงข้อมูลได้']);
}
?>
