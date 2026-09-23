<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';

if (empty($id)) {
    echo json_encode(['error' => 'ไม่พบรหัสโรงเรียน']);
    exit;
}

try {
    // ตรวจสอบว่ามีครูอยู่ในโรงเรียนนี้ไหม
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE school_id = ?');
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['error' => 'ไม่สามารถลบได้ เนื่องจากมีคุณครูสังกัดอยู่ในโรงเรียนนี้']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM schools WHERE id = ?');
    $stmt->execute([$id]);
    echo json_encode(['message' => 'ลบโรงเรียนสำเร็จแล้ว']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถลบข้อมูลได้']);
}
?>
