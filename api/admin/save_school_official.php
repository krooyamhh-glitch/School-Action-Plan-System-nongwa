<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึงส่วนนี้']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$name = $data['name'] ?? '';
$position = $data['position'] ?? '';
$role_key = $data['role_key'] ?? '';
$school_id = $_SESSION['school_id'];

if (empty($name) || empty($position) || empty($role_key)) {
    echo json_encode(['error' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit;
}

try {
    if ($id) {
        $stmt = $pdo->prepare('UPDATE school_officials SET name = ?, position = ?, role_key = ? WHERE id = ? AND school_id = ?');
        $stmt->execute([$name, $position, $role_key, $id, $school_id]);
        echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลสำเร็จ']);
    } else {
        $stmt = $pdo->prepare('INSERT INTO school_officials (school_id, name, position, role_key) VALUES (?, ?, ?, ?)');
        $stmt->execute([$school_id, $name, $position, $role_key]);
        echo json_encode(['status' => 'success', 'message' => 'เพิ่มข้อมูลสำเร็จ']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
