<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

if (!($pdo instanceof PDO)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูล MySQL ได้ กรุณาตรวจสอบการตั้งค่าฐานข้อมูล'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$school_id = (int)($data['school_id'] ?? 0);
$admin_id = (int)($data['admin_id'] ?? 0);
$admin_name = trim($data['admin_name'] ?? '');

if (!$school_id || !$admin_id) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

try {
    // Demote any current school_admin of this school to teacher (optional, or keep them)
    // Promote selected user in users table
    $stmtU = $pdo->prepare("UPDATE users SET role = 'school_admin' WHERE id = ? AND school_id = ?");
    $stmtU->execute([$admin_id, $school_id]);

    // Update school assigned_admin
    $stmtS = $pdo->prepare("UPDATE schools SET assigned_admin_id = ?, assigned_admin_name = ? WHERE id = ?");
    $stmtS->execute([$admin_id, $admin_name, $school_id]);

    echo json_encode([
        'status' => 'success',
        'message' => "แต่งตั้งคุณครู \"$admin_name\" เป็น Admin ดูแลระบบโรงเรียนเรียบร้อยแล้ว"
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการแต่งตั้ง Admin: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
