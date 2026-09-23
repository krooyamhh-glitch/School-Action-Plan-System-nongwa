<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$name = trim($data['name'] ?? '');
$position = trim($data['position'] ?? 'ครู');
$department = $data['department'] ?? 'academic';
$role = $data['role'] ?? 'teacher';

if (empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุชื่อ-นามสกุล']);
    exit;
}

if (!$pdo) {
    echo json_encode(['status' => 'success', 'message' => 'บันทึกผู้ใช้สำเร็จ (จำลอง)']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO users (school_id, name, username, password_hash, role, position, department, is_active)
        VALUES (1, ?, ?, ?, ?, ?, ?, 1)
    ");
    $username = 'user_' . time();
    $pwd = password_hash('123', PASSWORD_DEFAULT);
    $stmt->execute([$name, $username, $pwd, $role, $position, $department]);

    echo json_encode(['status' => 'success', 'message' => 'เพิ่มผู้ใช้งานสำเร็จ']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
