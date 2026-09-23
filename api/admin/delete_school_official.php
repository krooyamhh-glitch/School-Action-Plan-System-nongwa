<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึงส่วนนี้']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$school_id = $_SESSION['school_id'];

if (!$id) {
    echo json_encode(['error' => 'ไม่พบรหัสข้อมูล']);
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM school_officials WHERE id = ? AND school_id = ?');
    $stmt->execute([$id, $school_id]);
    echo json_encode(['status' => 'success', 'message' => 'ลบข้อมูลสำเร็จ']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
