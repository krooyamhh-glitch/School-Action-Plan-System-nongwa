<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$student_id = $data['student_id'] ?? '';
$subject_id = $data['subject_id'] ?? '';
$category = $data['category'] ?? ''; // characteristics, analytical, activities, clubs
$score = $data['score'] ?? 3;
$year = $data['academic_year'] ?? date('Y');
$semester = $data['semester'] ?? 1;

if (empty($student_id) || empty($subject_id) || empty($category)) {
    echo json_encode(['error' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id FROM evaluation_scores WHERE student_id = ? AND subject_id = ? AND category = ? AND academic_year = ? AND semester = ?');
    $stmt->execute([$student_id, $subject_id, $category, $year, $semester]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare('UPDATE evaluation_scores SET score = ? WHERE id = ?');
        $stmt->execute([$score, $existing['id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO evaluation_scores (student_id, subject_id, category, score, academic_year, semester) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$student_id, $subject_id, $category, $score, $year, $semester]);
    }
    echo json_encode(['message' => 'บันทึกคะแนนประเมินสำเร็จแล้ว']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถบันทึกข้อมูลได้: ' . $e->getMessage()]);
}
?>
