<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$subject_id = $data['subject_id'] ?? '';
$classroom_id = $data['classroom_id'] ?? '';
$academic_year = $data['academic_year'] ?? '2567';
$semester = $data['semester'] ?? 1;
$unit_name = $data['unit_name'] ?? '';
$max_score = $data['max_score'] ?? 10;
$unit_id = $data['id'] ?? null;

if (empty($subject_id) || empty($classroom_id) || empty($unit_name)) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

try {
    if ($unit_id) {
        $stmt = $pdo->prepare('UPDATE learning_units SET unit_name = ?, max_score = ? WHERE id = ?');
        $stmt->execute([$unit_name, $max_score, $unit_id]);
        echo json_encode(['message' => 'แก้ไขหน่วยการเรียนรู้สำเร็จ']);
    } else {
        $stmt = $pdo->prepare('INSERT INTO learning_units (subject_id, classroom_id, academic_year, semester, unit_name, max_score) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$subject_id, $classroom_id, $academic_year, $semester, $unit_name, $max_score]);
        echo json_encode(['message' => 'เพิ่มหน่วยการเรียนรู้สำเร็จ', 'id' => $pdo->lastInsertId()]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
