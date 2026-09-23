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

    // Ensure columns exist
    $stmt = $pdo->query("SHOW COLUMNS FROM analytical_scores LIKE 'item1'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE analytical_scores 
            ADD COLUMN item1 INT DEFAULT 0 AFTER semester,
            ADD COLUMN item2 INT DEFAULT 0 AFTER item1,
            ADD COLUMN item3 INT DEFAULT 0 AFTER item2,
            ADD COLUMN item4 INT DEFAULT 0 AFTER item3,
            ADD COLUMN item5 INT DEFAULT 0 AFTER item4,
            ADD COLUMN average_score FLOAT DEFAULT 0 AFTER item5
        ");
    }

    foreach ($scores as $s) {
        $student_id = $s['student_id'];
        $items = $s['items'] ?? [];
        $total = array_sum($items);
        $average = $total / 5;

        $stmt = $pdo->prepare('
            INSERT INTO analytical_scores (student_id, subject_id, classroom_id, teacher_id, academic_year, semester, item1, item2, item3, item4, item5, average_score)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            item1 = VALUES(item1), item2 = VALUES(item2), item3 = VALUES(item3), item4 = VALUES(item4), item5 = VALUES(item5),
            average_score = VALUES(average_score),
            teacher_id = VALUES(teacher_id)
        ');
        $stmt->execute([
            $student_id, $subject_id, $classroom_id, $teacher_id, $academic_year, $semester,
            $items[0] ?? 0, $items[1] ?? 0, $items[2] ?? 0, $items[3] ?? 0, $items[4] ?? 0,
            $average
        ]);
    }

    $pdo->commit();
    echo json_encode(['message' => 'บันทึกอ่านคิดวิเคราะห์สำเร็จ']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
