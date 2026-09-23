<?php
header('Content-Type: application/json');
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$teacher_id = $_SESSION['user_id'];
$school_id = $_SESSION['school_id'];
// ใช้ปีปัจจุบันหากไม่ได้ส่งมา
$academic_year = $_GET['academic_year'] ?? '';
if (empty($academic_year)) {
    $stmt = $pdo->prepare("SELECT year FROM academic_years WHERE school_id = ? AND is_current = 1 LIMIT 1");
    $stmt->execute([$school_id]);
    $year_row = $stmt->fetch();
    $academic_year = $year_row ? $year_row['year'] : '2567';
}

$semester = $_GET['semester'] ?? '';

try {
    // ดึงห้องเรียนที่ครูคนนี้ได้รับมอบหมายกิจกรรมพัฒนาผู้เรียน (LD) 
    // โดยอิงตามตารางมอบหมายเป็นหลักตามความต้องการของผู้ใช้
    $sql = "
        SELECT DISTINCT c.* 
        FROM classrooms c
        JOIN learner_development_assignments lda ON c.id = lda.classroom_id
        WHERE c.school_id = ? 
        AND lda.teacher_id = ?
        AND lda.academic_year = ?
    ";
    
    $params = [$school_id, $teacher_id, $academic_year];
    
    // คณะครูปกติอยากให้เห็นห้องที่ตัวเองเป็นครูประจำชั้นด้วยหรือไม่? 
    // จากคำขอคือ "เฉพาะครูที่ถูกมอบหมายให้ป้อนข้อมูลมอบหมายกิจกรรมพัฒนาผู้เรียน"
    // ดังนั้นเราจะใช้ JOIN แทน LEFT JOIN และเอาเฉพาะจากตาราง assignments

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($classrooms);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
