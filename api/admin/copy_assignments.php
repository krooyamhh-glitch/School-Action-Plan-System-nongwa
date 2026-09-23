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
$teacher_id = $data['teacher_id'] ?? '';
$target_year = $data['target_year'] ?? '';
$target_semester = $data['target_semester'] ?? 1;

if (empty($teacher_id) || empty($target_year)) {
    echo json_encode(['error' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$source_year = (int)$target_year - 1;

try {
    $pdo->beginTransaction();

    // 1. ดึงงานสอนจากปีที่แล้ว (เทอมเดียวกัน)
    $stmt = $pdo->prepare('SELECT subject_id, classroom_id FROM teacher_assignments WHERE teacher_id = ? AND academic_year = ? AND semester = ?');
    $stmt->execute([$teacher_id, (string)$source_year, $target_semester]);
    $old_assignments = $stmt->fetchAll();

    if (empty($old_assignments)) {
        echo json_encode(['error' => "ไม่พบข้อมูลงานสอนของปีการศึกษา $source_year ภาคเรียนที่ $target_semester"]);
        exit;
    }

    $count = 0;
    foreach ($old_assignments as $assign) {
        // ตรวจสอบว่ามีอยู่แล้วหรือยังในปีปัจจุบัน
        $check = $pdo->prepare('SELECT id FROM teacher_assignments WHERE teacher_id = ? AND subject_id = ? AND classroom_id <=> ? AND academic_year = ? AND semester = ?');
        $check->execute([$teacher_id, $assign['subject_id'], $assign['classroom_id'], $target_year, $target_semester]);
        
        if (!$check->fetch()) {
            $insert = $pdo->prepare('INSERT INTO teacher_assignments (teacher_id, subject_id, classroom_id, academic_year, semester) VALUES (?, ?, ?, ?, ?)');
            $insert->execute([$teacher_id, $assign['subject_id'], $assign['classroom_id'], $target_year, $target_semester]);
            $count++;
        }
    }

    $pdo->commit();
    echo json_encode(['message' => "คัดลอกงานสอนสำเร็จ จำนวน $count รายการ"]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถคัดลอกงานสอนได้: ' . $e->getMessage()]);
}
?>
