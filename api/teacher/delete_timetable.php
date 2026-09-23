<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$id = $_GET['id'] ?? null;
$teacher_id = $_SESSION['user_id'];

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID is required']);
    exit;
}

try {
    // Only allow teachers to delete their own timetable or admins
    if ($_SESSION['role'] === 'admin') {
        $stmt = $pdo->prepare('DELETE FROM timetables WHERE id = ?');
        $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare('DELETE FROM timetables WHERE id = ? AND teacher_id = ?');
        $stmt->execute([$id, $teacher_id]);
    }

    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'ลบคาบสอนเรียบร้อยแล้ว']);
    } else {
        echo json_encode(['error' => 'ไม่พบข้อมูลหรือคุณไม่มีสิทธิ์ลบข้อมูลนี้']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
