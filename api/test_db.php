<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    // ทดสอบดึงข้อมูลเบื้องต้น
    $stmt = $pdo->query('SELECT 1');
    $result = $stmt->fetch();
    
    // ตรวจสอบจำนวนผู้ใช้งาน
    $stmt = $pdo->query('SELECT COUNT(*) as user_count FROM users');
    $user_data = $stmt->fetch();
    
    // ตรวจสอบข้อมูล Super Admin
    $stmt = $pdo->prepare('SELECT username, name, role FROM users WHERE username = ?');
    $stmt->execute(['0000000000000']);
    $super_admin = $stmt->fetch();

    echo json_encode([
        'status' => 'success',
        'message' => 'เชื่อมต่อฐานข้อมูลสำเร็จ!',
        'database' => $db,
        'user_count' => $user_data['user_count'],
        'super_admin_exists' => $super_admin ? true : false,
        'super_admin_details' => $super_admin ?: 'ไม่พบข้อมูล Super Admin ในตาราง users'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ: ' . $e->getMessage()
    ]);
}
?>
