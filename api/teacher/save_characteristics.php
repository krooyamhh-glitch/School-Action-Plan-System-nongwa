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
$scores = $data['scores'] ?? [];

if (empty($subject_id) || empty($classroom_id) || empty($scores)) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

try {
    $pdo->beginTransaction();

    foreach ($scores as $s) {
        $student_id = $s['student_id'];
        $items = $s['items'] ?? [];
        $total = array_sum($items);
        $average = $total / 8;

        $stmt = $pdo->prepare('
            INSERT INTO characteristics_scores (student_id, subject_id, classroom_id, teacher_id, academic_year, semester, item1, item2, item3, item4, item5, item6, item7, item8, average_score)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            item1 = VALUES(item1), item2 = VALUES(item2), item3 = VALUES(item3), item4 = VALUES(item4),
            item5 = VALUES(item5), item6 = VALUES(item6), item7 = VALUES(item7), item8 = VALUES(item8),
            average_score = VALUES(average_score),
            teacher_id = VALUES(teacher_id)
        ');
        $stmt->execute([
            $student_id, $subject_id, $classroom_id, $teacher_id, $academic_year, $semester,
            $items[0], $items[1], $items[2], $items[3], $items[4], $items[5], $items[6], $items[7],
            $average
        ]);
    }

    $pdo->commit();
    echo json_encode(['message' => 'บันทึกคุณลักษณะอันพึงประสงค์สำเร็จ']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
