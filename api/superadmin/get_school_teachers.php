<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

if (!($pdo instanceof PDO)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูล MySQL ได้ กรุณาตรวจสอบการตั้งค่าฐานข้อมูล',
        'school' => null,
        'teachers' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$school_id = (int)($_GET['school_id'] ?? 0);

try {
    // Fetch school from DB
    $stmtS = $pdo->prepare("SELECT * FROM schools WHERE id = ?");
    $stmtS->execute([$school_id]);
    $school = $stmtS->fetch(PDO::FETCH_ASSOC);

    if (!$school) {
        echo json_encode([
            'status' => 'error',
            'message' => 'ไม่พบข้อมูลสถานศึกษาในระบบ',
            'teachers' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Fetch teachers for this school
    $stmtT = $pdo->prepare("SELECT id, name, position, department, role, phone, email, is_approved FROM users WHERE school_id = ? AND role != 'super_admin' ORDER BY id ASC");
    $stmtT->execute([$school_id]);
    $teachers = $stmtT->fetchAll(PDO::FETCH_ASSOC) ?: [];

    echo json_encode([
        'status' => 'success',
        'school' => $school,
        'teachers' => $teachers
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลบุคลากร: ' . $e->getMessage(),
        'teachers' => []
    ], JSON_UNESCAPED_UNICODE);
}
