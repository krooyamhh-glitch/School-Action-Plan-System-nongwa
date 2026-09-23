<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$level = $_GET['level'] ?? '';
$school_id = $_SESSION['school_id'];

if (empty($level)) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT DISTINCT room FROM students WHERE school_id = ? AND level = ? AND status = 'studying' ORDER BY CAST(room AS UNSIGNED) ASC");
    $stmt->execute([$school_id, $level]);
    $rooms = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($rooms);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
