<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

try {
    $academic_year = $_GET['academic_year'] ?? '2567';
    $status = $_GET['status'] ?? 'studying';
    
    $role = $_SESSION['role'] ?? 'teacher';
    $is_academic = isset($_SESSION['is_academic']) && $_SESSION['is_academic'];

    if ($role === 'teacher' && !$is_academic) {
        $stmt = $pdo->prepare('
            SELECT s.*, 
                   IFNULL(sp.prefix, s.prefix) AS prefix, 
                   IFNULL(sp.name, s.name) AS name, 
                   IFNULL(sp.last_name, s.last_name) AS last_name,
                   IFNULL(sp.parent_telegram_id, s.parent_telegram_id) AS parent_telegram_id
            FROM students s
            LEFT JOIN student_profiles sp ON s.student_profile_id = sp.id
            JOIN classrooms c ON s.classroom_id = c.id
            WHERE s.school_id = ? AND s.academic_year = ? AND s.status = ? 
              AND (c.teacher_id_1 = ? OR c.teacher_id_2 = ?)
            ORDER BY s.level ASC, s.room ASC, s.student_code ASC
        ');
        $stmt->execute([$_SESSION['school_id'], $academic_year, $status, $_SESSION['user_id'], $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare('
            SELECT s.*, 
                   IFNULL(sp.prefix, s.prefix) AS prefix, 
                   IFNULL(sp.name, s.name) AS name, 
                   IFNULL(sp.last_name, s.last_name) AS last_name,
                   IFNULL(sp.parent_telegram_id, s.parent_telegram_id) AS parent_telegram_id
            FROM students s
            LEFT JOIN student_profiles sp ON s.student_profile_id = sp.id
            WHERE s.school_id = ? AND s.academic_year = ? AND s.status = ? 
            ORDER BY s.level ASC, s.room ASC, s.student_code ASC
        ');
        $stmt->execute([$_SESSION['school_id'], $academic_year, $status]);
    }
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($students);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถดึงข้อมูลนักเรียนได้']);
}
?>
