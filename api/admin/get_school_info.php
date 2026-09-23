<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึงส่วนนี้']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM schools WHERE id = ?');
    $stmt->execute([$_SESSION['school_id']]);
    $school = $stmt->fetch();

    if (!$school) {
        echo json_encode(['error' => 'ไม่พบข้อมูลโรงเรียน']);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'school' => $school
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
