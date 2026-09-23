<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$teacher_id = $_SESSION['user_id'];
$classroom_id = $data['classroom_id'];
$academic_year = $data['academic_year'];
$semester = $data['semester'];
$scores = $data['scores']; // Array of {student_id, item1, item2, item3, item4, item5}

if (!$classroom_id || !$academic_year || !$semester || !is_array($scores)) {
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO competency_scores 
        (student_id, classroom_id, teacher_id, academic_year, semester, item1, item2, item3, item4, item5, average_score)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        teacher_id = VALUES(teacher_id),
        item1 = VALUES(item1),
        item2 = VALUES(item2),
        item3 = VALUES(item3),
        item4 = VALUES(item4),
        item5 = VALUES(item5),
        average_score = VALUES(average_score)
    ");

    foreach ($scores as $s) {
        $avg = ($s['item1'] + $s['item2'] + $s['item3'] + $s['item4'] + $s['item5']) / 5;
        $stmt->execute([
            $s['student_id'],
            $classroom_id,
            $teacher_id,
            $academic_year,
            $semester,
            $s['item1'],
            $s['item2'],
            $s['item3'],
            $s['item4'],
            $s['item5'],
            $avg
        ]);
    }

    $pdo->commit();
    echo json_encode(['message' => 'บันทึกข้อมูลสำเร็จ']);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
