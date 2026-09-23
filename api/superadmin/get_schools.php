<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

if (!($pdo instanceof PDO)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูล MySQL ได้ กรุณาตรวจสอบการตั้งค่าฐานข้อมูล',
        'schools' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Select schools from database
    $stmt = $pdo->query("
        SELECT 
            s.*,
            s.code AS smis_code,
            u.name AS assigned_admin_name,
            u.id AS assigned_admin_id
        FROM schools s
        LEFT JOIN users u ON u.school_id = s.id AND u.role = 'school_admin'
        ORDER BY s.id ASC
    ");
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'schools' => $schools,
        'source' => 'mysql'
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลโรงเรียน: ' . $e->getMessage(),
        'schools' => []
    ], JSON_UNESCAPED_UNICODE);
}
