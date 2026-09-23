<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$school_id = $_SESSION['school_id'];

try {
    $stmt = $pdo->prepare('
        SELECT id, name, last_name, position, role
        FROM users 
        WHERE school_id = ? AND role IN ("teacher", "admin") AND is_approved = 1
        ORDER BY name ASC
    ');
    $stmt->execute([$school_id]);
    $teachers = $stmt->fetchAll();
    echo json_encode($teachers);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถดึงข้อมูลครูได้: ' . $e->getMessage()]);
}
?>
