<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM subjects WHERE school_id = ? ORDER BY level ASC, code ASC');
    $stmt->execute([$_SESSION['school_id']]);
    $subjects = $stmt->fetchAll();
    echo json_encode($subjects);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถดึงข้อมูลรายวิชาได้']);
}
?>
