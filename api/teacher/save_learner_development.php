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
$classroom_id = $data['classroom_id'] ?? 0;
$academic_year = $data['academic_year'] ?? '2567';
$semester = $data['semester'] ?? 1;
$results = $data['results'] ?? [];

try {
    $pdo->beginTransaction();

    foreach ($results as $r) {
        $student_id = $r['student_id'];
        $guidance = $r['guidance_result'] ?? '';
        $scout = $r['scout_result'] ?? '';
        $club_id = $r['club_id'] ?? null;
        $club_result = $r['club_result'] ?? '';
        $social = $r['social_result'] ?? '';

        $stmt = $pdo->prepare('
            INSERT INTO learner_development_results 
            (student_id, classroom_id, academic_year, semester, guidance_result, scout_result, club_id, club_result, social_result, teacher_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            guidance_result = VALUES(guidance_result),
            scout_result = VALUES(scout_result),
            club_id = VALUES(club_id),
            club_result = VALUES(club_result),
            social_result = VALUES(social_result),
            teacher_id = VALUES(teacher_id)
        ');
        $stmt->execute([$student_id, $classroom_id, $academic_year, $semester, $guidance, $scout, $club_id, $club_result, $social, $teacher_id]);
    }

    $pdo->commit();
    echo json_encode(['message' => 'บันทึกข้อมูลสำเร็จ']);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
