<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$current_role = $_SESSION['role'];
$current_school_id = $_SESSION['school_id'];

try {
    if ($current_role === 'super_admin') {
        // Super Admin เห็นทุกคนที่ยังไม่ได้รับอนุมัติ
        $stmt = $pdo->query('SELECT u.*, s.name as school_name FROM users u LEFT JOIN schools s ON u.school_id = s.id WHERE u.is_approved = 0');
    } else if ($current_role === 'admin') {
        // Admin โรงเรียนเห็นเฉพาะครูในโรงเรียนตัวเองที่ยังไม่ได้รับอนุมัติ
        $stmt = $pdo->prepare('SELECT u.*, s.name as school_name FROM users u LEFT JOIN schools s ON u.school_id = s.id WHERE u.is_approved = 0 AND u.school_id = ?');
        $stmt->execute([$current_school_id]);
    } else {
        echo json_encode([]);
        exit;
    }
    
    $users = $stmt->fetchAll();
    echo json_encode($users);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถดึงข้อมูลผู้ใช้งานได้']);
}
?>
