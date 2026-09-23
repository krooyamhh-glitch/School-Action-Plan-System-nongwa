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
$teacher_id = $data['teacher_id'] ?? 0;
$classroom_id = $data['classroom_id'] ?? 0;
$academic_year = $data['academic_year'] ?? '2567';
$semester = $data['semester'] ?? 1;

if (!$teacher_id || !$classroom_id) {
    echo json_encode(['error' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO learner_development_assignments (teacher_id, classroom_id, academic_year, semester)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id)
    ');
    $stmt->execute([$teacher_id, $classroom_id, $academic_year, $semester]);
    
    echo json_encode(['message' => 'มอบหมายกิจกรรมพัฒนาผู้เรียนสำเร็จ']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
