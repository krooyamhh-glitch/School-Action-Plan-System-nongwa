<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$name = $data['name'] ?? '';
$affiliation = $data['affiliation'] ?? '';

if (empty($username) || empty($password) || empty($name)) {
    echo json_encode(['error' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit;
}

try {
    // แฮชรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare('INSERT INTO users (username, password, name, affiliation, role, is_approved) VALUES (?, ?, ?, ?, "super_admin", 1)');
    $stmt->execute([$username, $hashed_password, $name, $affiliation]);
    
    echo json_encode(['message' => 'เพิ่ม Super Admin คนใหม่สำเร็จแล้ว']);
} catch (PDOException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'ชื่อผู้ใช้นี้อาจมีอยู่ในระบบแล้ว']);
}
?>
