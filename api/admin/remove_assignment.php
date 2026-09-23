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
$assignment_id = $data['assignment_id'] ?? '';

if (empty($assignment_id)) {
    echo json_encode(['error' => 'ไม่พบรหัสงานสอน']);
    exit;
}

try {
    // ตรวจสอบว่างานสอนอยู่ในโรงเรียนเดียวกัน
    $stmt = $pdo->prepare('
        SELECT ta.id 
        FROM teacher_assignments ta
        JOIN users u ON ta.teacher_id = u.id
        WHERE ta.id = ? AND u.school_id = ?
    ');
    $stmt->execute([$assignment_id, $_SESSION['school_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['error' => 'งานสอนไม่ได้อยู่ในโรงเรียนของคุณ']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM teacher_assignments WHERE id = ?');
    $stmt->execute([$assignment_id]);
    echo json_encode(['message' => 'ยกเลิกงานสอนสำเร็จแล้ว']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถยกเลิกงานสอนได้: ' . $e->getMessage()]);
}
?>
