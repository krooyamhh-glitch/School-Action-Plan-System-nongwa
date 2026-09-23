<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึงส่วนนี้']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$classroom_id = $data['classroom_id'] ?? '';
$teacher_id_1 = $data['teacher_id_1'] ?: null;
$teacher_id_2 = $data['teacher_id_2'] ?: null;

if (!$classroom_id) {
    echo json_encode(['error' => 'ไม่พบรหัสห้องเรียน']);
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE classrooms SET teacher_id_1 = ?, teacher_id_2 = ? WHERE id = ? AND school_id = ?');
    $stmt->execute([$teacher_id_1, $teacher_id_2, $classroom_id, $_SESSION['school_id']]);

    echo json_encode([
        'status' => 'success',
        'message' => 'อัปเดตครูประจำชั้นเรียบร้อยแล้ว'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
