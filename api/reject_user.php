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

try {
    $current_role = $_SESSION['role'];
    $current_school_id = $_SESSION['school_id'];
    
    if ($current_role === 'super_admin') {
        // Super Admin ลบได้ทุกคนที่ยังไม่ได้รับอนุมัติ
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND is_approved = 0');
        $stmt->execute([$user_id]);
    } else if ($current_role === 'admin') {
        // Admin โรงเรียนลบได้เฉพาะครูในโรงเรียนตัวเองที่ยังไม่ได้รับอนุมัติ
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND school_id = ? AND is_approved = 0');
        $stmt->execute([$user_id, $current_school_id]);
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'ไม่มีสิทธิ์ดำเนินการ']);
        exit;
    }
    
    echo json_encode(['message' => 'ปฏิเสธการสมัครและลบข้อมูลสำเร็จแล้ว']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถดำเนินการได้: ' . $e->getMessage()]);
}
?>
