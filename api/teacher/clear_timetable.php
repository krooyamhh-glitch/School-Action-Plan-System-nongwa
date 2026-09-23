<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$is_admin_or_academic = ($_SESSION['role'] === 'admin' || (isset($_SESSION['is_academic']) && $_SESSION['is_academic'] == 1));
$teacher_id = $_SESSION['user_id'];
if ($is_admin_or_academic && isset($data['teacher_id']) && !empty($data['teacher_id'])) {
    $teacher_id = $data['teacher_id'];
}
$academic_year = $data['academic_year'] ?? '';
$semester = $data['semester'] ?? 1;

if (!$academic_year) {
    echo json_encode(['error' => 'ปีการศึกษาไม่ถูกต้อง']);
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM timetables WHERE teacher_id = ? AND academic_year = ? AND semester = ?');
    $stmt->execute([$teacher_id, $academic_year, $semester]);
    
    echo json_encode(['message' => 'ล้างตารางสอนทั้งหมดเรียบร้อยแล้ว']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
