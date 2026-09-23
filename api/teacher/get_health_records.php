<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$teacher_id = $_SESSION['user_id'];
$classroom_id = $_GET['classroom_id'] ?? null;
$academic_year = $_GET['academic_year'] ?? '2567';
$semester = $_GET['semester'] ?? 1;
$record_number = $_GET['record_number'] ?? 1;

if (!$classroom_id) {
    echo json_encode(['error' => 'กรุณาระบุห้องเรียน']);
    exit;
}

try {
    // ดึงรายชื่อนักเรียนในห้อง
    $stmt = $pdo->prepare('
        SELECT s.id, s.student_code, 
               IFNULL(sp.prefix, s.prefix) AS prefix, 
               IFNULL(sp.name, s.name) AS name, 
               IFNULL(sp.last_name, s.last_name) AS last_name,
               hr.weight, hr.height, hr.recorded_date,
               hr.weight_age_result, hr.height_age_result, hr.weight_height_result
        FROM students s
        LEFT JOIN student_profiles sp ON s.student_profile_id = sp.id
        LEFT JOIN student_health_records hr ON s.id = hr.student_id 
             AND hr.academic_year = ? 
             AND hr.semester = ? 
             AND hr.record_number = ?
        WHERE s.classroom_id = ? AND s.academic_year = ? AND s.status = "studying"
        ORDER BY s.student_code ASC
    ');
    $stmt->execute([$academic_year, $semester, $record_number, $classroom_id, $academic_year]);
    $students = $stmt->fetchAll();

    echo json_encode($students);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
