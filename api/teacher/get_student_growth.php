<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$student_id = $_GET['student_id'] ?? null;

if (!$student_id) {
    echo json_encode(['error' => 'ไม่พบรหัสนักเรียน']);
    exit;
}

try {
    // หา profile_id ของนักเรียนคนนี้ก่อน
    $stmt_p = $pdo->prepare("SELECT student_profile_id FROM students WHERE id = ?");
    $stmt_p->execute([$student_id]);
    $student_info = $stmt_p->fetch();
    $profile_id = $student_info['student_profile_id'] ?? null;

    if ($profile_id) {
        // ดึงข้อมูลสุขภาพจากทุกปีที่เชื่อมโยงกับโปรไฟล์นี้
        $stmt = $pdo->prepare('
            SELECT hr.academic_year, hr.semester, hr.record_number, hr.weight, hr.height, hr.recorded_date
            FROM student_health_records hr
            JOIN students s ON hr.student_id = s.id
            WHERE s.student_profile_id = ?
            ORDER BY hr.academic_year ASC, hr.semester ASC, hr.record_number ASC
        ');
        $stmt->execute([$profile_id]);
    } else {
        // Fallback กรณีไม่มี profile_id
        $stmt = $pdo->prepare('
            SELECT academic_year, semester, record_number, weight, height, recorded_date
            FROM student_health_records
            WHERE student_id = ?
            ORDER BY academic_year ASC, semester ASC, record_number ASC
        ');
        $stmt->execute([$student_id]);
    }
    $records = $stmt->fetchAll();

    echo json_encode($records);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
