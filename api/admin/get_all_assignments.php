<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$role = $_SESSION['role'] ?? '';
$position = $_SESSION['position'] ?? '';
$is_director = (strpos($position, 'ผู้อำนวยการ') !== false);
$is_academic = $_SESSION['is_academic'] ?? false;
$school_id = $_SESSION['school_id'];

if ($role !== 'admin' && !$is_director && !$is_academic) {
    http_response_code(403);
    exit;
}

$academic_year = $_GET['academic_year'] ?? '';
$semester = $_GET['semester'] ?? 1;

try {
    $sql = '
        SELECT ta.id as assignment_id, s.id as subject_id, s.code as subject_code, s.code, s.name as subject_name, s.level, c.id as classroom_id, c.room, u.name as teacher_name
        FROM teacher_assignments ta
        JOIN subjects s ON ta.subject_id = s.id
        LEFT JOIN classrooms c ON ta.classroom_id = c.id
        LEFT JOIN users u ON ta.teacher_id = u.id
        WHERE u.school_id = ? AND ta.academic_year = ? AND ta.semester = ?
        ORDER BY s.level ASC, c.room ASC, s.code ASC
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$school_id, $academic_year, $semester]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter labels to include teacher name
    foreach ($assignments as &$a) {
        $a['subject_name_long'] = "{$a['subject_code']} {$a['subject_name']} ({$a['level']}/{$a['room']}) - ครู{$a['teacher_name']}";
    }

    echo json_encode($assignments);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
