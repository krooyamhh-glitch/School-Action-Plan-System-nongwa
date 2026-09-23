<?php
/**
 * Parent Portal Login API
 */
session_start();
require_once '../config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$national_id = trim($data['national_id'] ?? '');
$student_code = trim($data['student_code'] ?? '');

if (empty($national_id) || empty($student_code)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit;
}

try {
    // ค้นหานักเรียนที่มีเลขบัตรประชาชน และรหัสนักเรียนตรงกัน
    $stmt = $pdo->prepare("SELECT s.*, sch.name as school_name 
                           FROM students s 
                           JOIN schools sch ON s.school_id = sch.id
                           WHERE s.national_id = ? AND s.student_code = ? 
                           LIMIT 1");
    $stmt->execute([$national_id, $student_code]);
    $student = $stmt->fetch();

    if ($student) {
        // บันทึก Session สำหรับผู้ปกครอง
        $_SESSION['parent_logged_in'] = true;
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = trim($student['name'] . ' ' . ($student['last_name'] ?? ''));
        $_SESSION['student_code'] = $student['student_code'];
        $_SESSION['school_id'] = $student['school_id'];
        $_SESSION['school_name'] = $student['school_name'];
        $_SESSION['role'] = 'parent';
        
        echo json_encode([
            'success' => true, 
            'message' => 'เข้าสู่ระบบสำเร็จ',
            'student_name' => $student['name']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง กรุณาตรวจสอบเลขบัตรประชาชนหรือรหัสนักเรียนอีกครั้ง']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล']);
}
