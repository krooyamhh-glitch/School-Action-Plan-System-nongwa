<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$classroom_id = $_GET['classroom_id'] ?? null;
$academic_year = $_GET['academic_year'] ?? null;
$semester = $_GET['semester'] ?? null;

if (!$classroom_id || !$academic_year || !$semester) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

try {
    // ดึงรายชื่อนักเรียนในห้อง
    $stmt = $pdo->prepare("
        SELECT s.id, s.student_code, 
               IFNULL(sp.prefix, s.prefix) AS prefix, 
               IFNULL(sp.name, s.name) AS name, 
               IFNULL(sp.last_name, s.last_name) AS last_name,
               cs.item1, cs.item2, cs.item3, cs.item4, cs.item5, cs.average_score
        FROM students s
        LEFT JOIN student_profiles sp ON s.student_profile_id = sp.id
        LEFT JOIN competency_scores cs ON s.id = cs.student_id 
             AND cs.classroom_id = ? 
             AND cs.academic_year = ? 
             AND cs.semester = ?
        WHERE s.classroom_id = ? AND s.academic_year = ? AND s.status = 'studying'
        ORDER BY s.student_code ASC
    ");
    $stmt->execute([$classroom_id, $academic_year, $semester, $classroom_id, $academic_year]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($students);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
