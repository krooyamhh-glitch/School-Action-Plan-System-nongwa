<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$name = $data['name'] ?? '';
$last_name = $data['last_name'] ?? '';
$password = $data['password'] ?? '';

if (empty($name)) {
    echo json_encode(['error' => 'กรุณากรอกชื่อ']);
    exit;
}

try {
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET name = ?, last_name = ?, password = ? WHERE id = ?');
        $stmt->execute([$name, $last_name, $hashedPassword, $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, last_name = ? WHERE id = ?');
        $stmt->execute([$name, $last_name, $_SESSION['user_id']]);
    }
    
    // อัปเดต Session
    $_SESSION['name'] = $name;
    $_SESSION['last_name'] = $last_name;
    
    echo json_encode(['message' => 'อัปเดตข้อมูลสำเร็จแล้ว']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถอัปเดตข้อมูลได้: ' . $e->getMessage()]);
}
?>
