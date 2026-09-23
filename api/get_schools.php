<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// ตรวจสอบสิทธิ์ (Super Admin หรือ Admin โรงเรียน)
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

try {
    $stmt = $pdo->query('SELECT * FROM schools ORDER BY name ASC');
    $schools = $stmt->fetchAll();
    echo json_encode($schools);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถดึงข้อมูลโรงเรียนได้']);
}
?>
