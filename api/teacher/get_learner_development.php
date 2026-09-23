<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$classroom_id = $_GET['classroom_id'] ?? 0;
$academic_year = $_GET['academic_year'] ?? '2567';
$semester = $_GET['semester'] ?? 1;

try {
    // Get students in classroom
    $stmt = $pdo->prepare('
        SELECT s.id, 
               IFNULL(sp.prefix, s.prefix) AS prefix, 
               IFNULL(sp.name, s.name) AS name, 
               IFNULL(sp.last_name, s.last_name) AS last_name, 
               s.student_code 
        FROM students s
        LEFT JOIN student_profiles sp ON s.student_profile_id = sp.id
        WHERE s.classroom_id = ? AND s.academic_year = ? 
        AND (s.status = "studying" OR s.status IS NULL OR s.status = "" OR s.status = "active")
        ORDER BY s.student_code ASC
    ');
    $stmt->execute([$classroom_id, $academic_year]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get results
    $stmt = $pdo->prepare('SELECT * FROM learner_development_results WHERE classroom_id = ? AND academic_year = ? AND semester = ?');
    $stmt->execute([$classroom_id, $academic_year, $semester]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $results_map = [];
    foreach ($results as $r) {
        $results_map[$r['student_id']] = $r;
    }

    $data = [];
    foreach ($students as $s) {
        $student_id = $s['id'];
        $res = $results_map[$student_id] ?? [
            'guidance_result' => '',
            'scout_result' => '',
            'club_id' => null,
            'club_result' => '',
            'social_result' => ''
        ];
        
        // Remove ID from result to not overwrite student ID
        if (isset($res['id'])) unset($res['id']);
        
        // Merge results into student data (ensuring name/last_name from student table are preserved)
        $merged = array_merge($res, $s);
        $data[] = $merged;
    }

    echo json_encode($data);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
