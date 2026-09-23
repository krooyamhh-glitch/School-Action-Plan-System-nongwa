<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// ต้องเป็น Admin หรือ งานวิชาการ
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && !$_SESSION['is_academic'])) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$code = $data['code'] ?? '';
$name = $data['name'] ?? '';
$level = $data['level'] ?? '';
$hours = $data['hours'] ?? 40;
$credits = $data['credits'] ?? 1.0;
$learning_area = $data['learning_area'] ?? '';

if (empty($code) || empty($name) || empty($level)) {
    echo json_encode(['error' => 'กรุณากรอกข้อมูลรายวิชาให้ครบถ้วน']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO subjects (code, name, level, hours, credits, learning_area, school_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$code, $name, $level, $hours, $credits, $learning_area, $_SESSION['school_id']]);
    echo json_encode(['message' => 'เพิ่มข้อมูลรายวิชาสำเร็จแล้ว']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถเพิ่มข้อมูลได้: ' . $e->getMessage()]);
}
?>
