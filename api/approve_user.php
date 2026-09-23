<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? '';
$new_role = $data['role'] ?? 'teacher'; // Role to assign (admin, teacher)

try {
    $current_role = $_SESSION['role'];
    $current_school_id = $_SESSION['school_id'];
    
    if ($current_role === 'super_admin') {
        // Super Admin อนุมัติได้ทุกคน
        $stmt = $pdo->prepare('UPDATE users SET is_approved = 1, role = ? WHERE id = ?');
        $stmt->execute([$new_role, $user_id]);
        $count = $stmt->rowCount();
    } else if ($current_role === 'admin') {
        // Admin โรงเรียนอนุมัติได้เฉพาะครูในโรงเรียนตัวเอง
        $stmt = $pdo->prepare('UPDATE users SET is_approved = 1 WHERE id = ? AND school_id = ?');
        $stmt->execute([$user_id, $current_school_id]);
        $count = $stmt->rowCount();
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'ไม่มีสิทธิ์อนุมัติ']);
        exit;
    }
    
    if ($count > 0) {
        echo json_encode(['message' => 'อนุมัติผู้ใช้งานสำเร็จแล้ว']);
    } else {
        echo json_encode(['error' => 'ไม่พบผู้ใช้งานที่รอการอนุมัติ หรือผู้ใช้งานนี้ถูกอนุมัติไปแล้ว']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถอนุมัติผู้ใช้งานได้']);
}
?>
