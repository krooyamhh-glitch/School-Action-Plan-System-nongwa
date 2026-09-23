<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$teacher_id = $_GET['teacher_id'] ?? 0;
$academic_year = $_GET['academic_year'] ?? '2567';
$semester = $_GET['semester'] ?? 1;

try {
    $stmt = $pdo->prepare('
        SELECT lda.id as assignment_id, c.level, c.room, c.id as classroom_id
        FROM learner_development_assignments lda
        JOIN classrooms c ON lda.classroom_id = c.id
        WHERE lda.teacher_id = ? AND lda.academic_year = ? AND lda.semester = ?
        ORDER BY c.level ASC, c.room ASC
    ');
    $stmt->execute([$teacher_id, $academic_year, $semester]);
    $assignments = $stmt->fetchAll();

    echo json_encode($assignments);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
