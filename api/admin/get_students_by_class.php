<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && !$_SESSION['is_academic'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$level = $_GET['level'] ?? '';
$room = $_GET['room'] ?? '';
$school_id = $_SESSION['school_id'];

if (empty($level)) {
    echo json_encode([]);
    exit;
}

try {
    $sql = "SELECT * FROM students WHERE school_id = ? AND level = ? AND status = 'studying'";
    $params = [$school_id, $level];
    
    if (!empty($room)) {
        $sql .= " AND room = ?";
        $params[] = $room;
    }
    
    $sql .= " ORDER BY room, name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($students);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
