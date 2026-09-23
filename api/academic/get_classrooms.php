<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$school_id = $_SESSION['school_id'];

try {
    $stmt = $pdo->prepare('
        SELECT c.*, 
               CASE 
                   WHEN c.teacher_id_1 IS NOT NULL THEN CONCAT(u1.name, " ", IFNULL(u1.last_name, ""))
                   ELSE (
                       SELECT CONCAT(u.name, " ", IFNULL(u.last_name, ""))
                       FROM learner_development_assignments lda
                       JOIN users u ON lda.teacher_id = u.id
                       WHERE lda.classroom_id = c.id
                       ORDER BY lda.academic_year DESC, lda.semester DESC
                       LIMIT 1
                   )
               END as teacher_name_1,
               CASE 
                   WHEN c.teacher_id_2 IS NOT NULL THEN CONCAT(u2.name, " ", IFNULL(u2.last_name, ""))
                   ELSE NULL
               END as teacher_name_2
        FROM classrooms c
        LEFT JOIN users u1 ON c.teacher_id_1 = u1.id
        LEFT JOIN users u2 ON c.teacher_id_2 = u2.id
        WHERE c.school_id = ? 
        ORDER BY c.level ASC, c.room ASC
    ');
    $stmt->execute([$school_id]);
    $classrooms = $stmt->fetchAll();
    echo json_encode($classrooms);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถดึงข้อมูลห้องเรียนได้: ' . $e->getMessage()]);
}
?>
