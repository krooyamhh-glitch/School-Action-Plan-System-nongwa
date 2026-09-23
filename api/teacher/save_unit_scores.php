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
$scores = $data['scores'] ?? []; // [{student_id, unit_id, score}]
$grades = $data['grades'] ?? []; // [{student_id, score_units, score_final, score_total, score_percent, grade}]

if (empty($subject_id) || empty($classroom_id)) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. บันทึกคะแนนหน่วยการเรียนรู้รายหน่วย
    if (!empty($scores)) {
        foreach ($scores as $s) {
            $student_id = $s['student_id'];
            $unit_id = $s['unit_id'];
            $score = $s['score'] ?? 0;

            $stmt = $pdo->prepare('
                INSERT INTO unit_scores (student_id, learning_unit_id, score)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE score = VALUES(score)
            ');
            $stmt->execute([$student_id, $unit_id, $score]);
        }
    }

    // 2. บันทึกคะแนนสรุปและเกรดลงในตาราง grades
    if (!empty($grades)) {
        foreach ($grades as $g) {
            $student_id = $g['student_id'];
            $score_units = $g['score_units'] ?? 0;
            $score_final = $g['score_final'] ?? 0;
            
            // ให้เซิร์ฟเวอร์คำนวณคะแนนรวมอีกครั้งเพื่อความถูกต้อง
            $score_total = $score_units + $score_final;
            
            // คำนวณร้อยละ (พยายามดึงคะแนนเต็มจากฐานข้อมูลถ้าเป็นไปได้ แต่เบื้องต้นใช้ตามที่ส่งมาและคำนวณใหม่)
            $score_percent = $g['score_percent'] ?? 0;
            $grade = $g['grade'] ?? '0';

            $stmt = $pdo->prepare('
                INSERT INTO grades (student_id, subject_id, classroom_id, teacher_id, academic_year, semester, 
                                  score_units, score_final, score_total, score_percent, grade)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    score_units = VALUES(score_units),
                    score_final = VALUES(score_final),
                    score_total = VALUES(score_total),
                    score_percent = VALUES(score_percent),
                    grade = VALUES(grade),
                    teacher_id = VALUES(teacher_id)
            ');
            $stmt->execute([
                $student_id, $subject_id, $classroom_id, $teacher_id, $academic_year, $semester,
                $score_units, $score_final, $score_total, $score_percent, $grade
            ]);
        }
    }

    $pdo->commit();
    echo json_encode(['message' => 'บันทึกคะแนนสำเร็จ']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
