<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$subject_id = $_GET['subject_id'] ?? '';
$classroom_id = $_GET['classroom_id'] ?? '';
$academic_year = $_GET['academic_year'] ?? '2567';
$semester = $_GET['semester'] ?? 1;

if (empty($subject_id) || empty($classroom_id)) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM learning_units WHERE subject_id = ? AND classroom_id = ? AND academic_year = ? AND semester = ? ORDER BY id ASC');
    $stmt->execute([$subject_id, $classroom_id, $academic_year, $semester]);
    $units = $stmt->fetchAll();
    echo json_encode($units);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
