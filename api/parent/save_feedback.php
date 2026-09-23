<?php
/**
 * API to save parent feedback for a student
 */
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['parent_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$student_id = $_SESSION['student_id'];
$academic_year = $data['academic_year'] ?? '';
$semester = $data['semester'] ?? '';

// 5 ด้านใหม่
$responsibility = $data['responsibility'] ?? '';
$spare_time = $data['spare_time'] ?? '';
$relationship = $data['relationship'] ?? '';
$personality = $data['personality'] ?? '';
$health_comment = $data['health_comment'] ?? '';

if (empty($academic_year) || empty($semester)) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลปีการศึกษาไม่ถูกต้อง']);
    exit;
}

try {
    // ตรวจสอบว่ามีข้อมูลเดิมอยู่หรือไม่
    $stmt = $pdo->prepare("SELECT id FROM parent_feedback WHERE student_id = ? AND academic_year = ? AND semester = ?");
    $stmt->execute([$student_id, $academic_year, $semester]);
    $existing = $stmt->fetch();

    if ($existing) {
        // อัปเดต
        $stmt = $pdo->prepare("UPDATE parent_feedback SET 
            responsibility_comment = ?, 
            spare_time_comment = ?, 
            relationship_comment = ?, 
            personality_comment = ?, 
            health_comment = ? 
            WHERE id = ?");
        $stmt->execute([$responsibility, $spare_time, $relationship, $personality, $health_comment, $existing['id']]);
    } else {
        // เพิ่มใหม่
        $stmt = $pdo->prepare("INSERT INTO parent_feedback 
            (student_id, academic_year, semester, responsibility_comment, spare_time_comment, relationship_comment, personality_comment, health_comment) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$student_id, $academic_year, $semester, $responsibility, $spare_time, $relationship, $personality, $health_comment]);
    }

    echo json_encode(['success' => true, 'message' => 'บันทึกความคิดเห็นเรียบร้อยแล้ว']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
