<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && !$_SESSION['is_academic'])) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';

if (empty($id)) {
    echo json_encode(['error' => 'ไม่พบรหัสรายวิชา']);
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM subjects WHERE id = ? AND school_id = ?');
    $stmt->execute([$id, $_SESSION['school_id']]);
    echo json_encode(['message' => 'ลบรายวิชาสำเร็จแล้ว']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถลบข้อมูลได้']);
}
?>
