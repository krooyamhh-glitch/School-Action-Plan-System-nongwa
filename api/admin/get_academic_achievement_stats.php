<?php
header('Content-Type: application/json');
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

$role = $_SESSION['role'] ?? '';
$position = $_SESSION['position'] ?? '';
$school_id = $_SESSION['school_id'] ?? null;
$is_director = (strpos($position, 'ผู้อำนวยการ') !== false);

if ($role !== 'admin' && $role !== 'super_admin' && !$is_director) {
    die(json_encode(['error' => 'Forbidden']));
}

$academic_year = $_GET['academic_year'] ?? '';
$semester = $_GET['semester'] ?? '';

if (empty($academic_year) || empty($semester)) {
    die(json_encode(['error' => 'Missing parameters']));
}

try {
    // 1. Get Grade Distribution by Level
    $sql_dist = "SELECT s.level, g.grade, COUNT(*) as count 
                 FROM grades g
                 JOIN students s ON g.student_id = s.id
                 WHERE s.school_id = ? AND g.academic_year = ? AND g.semester = ?
                 GROUP BY s.level, g.grade";
    
    $stmt_dist = $pdo->prepare($sql_dist);
    $stmt_dist->execute([$school_id, $academic_year, $semester]);
    $distribution_raw = $stmt_dist->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get Average Score/GPA by Level
    // Note: grade is stored as string like '4', '3.5', 'ร', 'มส'
    // We only calculate GPA for numeric grades
    $sql_avg = "SELECT s.level, AVG(CASE WHEN g.grade REGEXP '^[0-9.]+$' THEN CAST(g.grade AS DECIMAL(3,2)) ELSE NULL END) as avg_gpa
                FROM grades g
                JOIN students s ON g.student_id = s.id
                WHERE s.school_id = ? AND g.academic_year = ? AND g.semester = ?
                GROUP BY s.level";
    
    $stmt_avg = $pdo->prepare($sql_avg);
    $stmt_avg->execute([$school_id, $academic_year, $semester]);
    $averages = $stmt_avg->fetchAll(PDO::FETCH_ASSOC);

    // Format data for charts
    $result = [
        'distribution' => [],
        'averages' => $averages
    ];

    foreach ($distribution_raw as $row) {
        $level = $row['level'];
        $grade = $row['grade'];
        $count = (int)$row['count'];

        if (!isset($result['distribution'][$level])) {
            $result['distribution'][$level] = [];
        }
        $result['distribution'][$level][$grade] = $count;
    }

    echo json_encode($result);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
