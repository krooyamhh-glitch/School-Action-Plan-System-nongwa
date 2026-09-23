<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$teacher_id = $_SESSION['user_id'];
$school_id = $_SESSION['school_id'];

try {
    // ดึงห้องเรียนที่ครูคนนี้เป็นครูประจำชั้น หรือ ถูกมอบหมายกิจกรรมพัฒนาผู้เรียน
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.*, 
               (SELECT year from academic_years WHERE school_id = c.school_id AND is_current = 1 LIMIT 1) as current_year
        FROM classrooms c
        LEFT JOIN learner_development_assignments lda ON c.id = lda.classroom_id
        WHERE c.school_id = ? AND (c.teacher_id_1 = ? OR c.teacher_id_2 = ? OR lda.teacher_id = ?)
    ");
    $stmt->execute([$school_id, $teacher_id, $teacher_id, $teacher_id]);
    $classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($classrooms);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
