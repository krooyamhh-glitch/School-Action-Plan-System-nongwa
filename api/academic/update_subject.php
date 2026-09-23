<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// ต้องเป็น Admin หรือ งานวิชาการ
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && !$_SESSION['is_academic'])) {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึง']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';
$code = $data['code'] ?? '';
$name = $data['name'] ?? '';
$level = $data['level'] ?? '';
$hours = $data['hours'] ?? 40;
$credits = $data['credits'] ?? 1.0;
$learning_area = $data['learning_area'] ?? '';

if (empty($id) || empty($code) || empty($name) || empty($level)) {
    echo json_encode(['error' => 'กรุณากรอกข้อมูลรายวิชาให้ครบถ้วน']);
    exit;
}

try {
    // ตรวจสอบว่าเป็นรายวิชาของโรงเรียนตัวเอง
    $stmt = $pdo->prepare('UPDATE subjects SET code = ?, name = ?, level = ?, hours = ?, credits = ?, learning_area = ? WHERE id = ? AND school_id = ?');
    $stmt->execute([$code, $name, $level, $hours, $credits, $learning_area, $id, $_SESSION['school_id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'แก้ไขข้อมูลรายวิชาสำเร็จแล้ว']);
    } else {
        echo json_encode(['error' => 'ไม่พบข้อมูลรายวิชา หรือไม่มีการเปลี่ยนแปลง']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถแก้ไขข้อมูลได้: ' . $e->getMessage()]);
}
?>
