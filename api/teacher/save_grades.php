<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$teacher_id = $_SESSION['user_id'];
$subject_id = $data['subject_id'] ?? '';
$classroom_id = $data['classroom_id'] ?? '';
$academic_year = $data['academic_year'] ?? '2567';
$semester = $data['semester'] ?? 1;
$grades = $data['grades'] ?? [];

if (empty($subject_id) || empty($classroom_id) || empty($grades)) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

try {
    $pdo->beginTransaction();

    foreach ($grades as $g) {
        $student_id = $g['student_id'];
        $midterm = $g['score_midterm'] ?? 0;
        $final = $g['score_final'] ?? 0;
        $total = $midterm + $final;
        
        // คำนวณเกรดเบื้องต้น (ตัวอย่าง)
        $grade = '0';
        if ($total >= 80) $grade = '4';
        else if ($total >= 75) $grade = '3.5';
        else if ($total >= 70) $grade = '3';
        else if ($total >= 65) $grade = '2.5';
        else if ($total >= 60) $grade = '2';
        else if ($total >= 55) $grade = '1.5';
        else if ($total >= 50) $grade = '1';
        else $grade = '0';

        $stmt = $pdo->prepare('
            INSERT INTO grades (student_id, subject_id, classroom_id, teacher_id, academic_year, semester, score_midterm, score_final, score_total, grade)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            score_midterm = VALUES(score_midterm),
            score_final = VALUES(score_final),
            score_total = VALUES(score_total),
            grade = VALUES(grade),
            teacher_id = VALUES(teacher_id)
        ');
        $stmt->execute([$student_id, $subject_id, $classroom_id, $teacher_id, $academic_year, $semester, $midterm, $final, $total, $grade]);
    }

    $pdo->commit();
    echo json_encode(['message' => 'บันทึกคะแนนสำเร็จ']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
