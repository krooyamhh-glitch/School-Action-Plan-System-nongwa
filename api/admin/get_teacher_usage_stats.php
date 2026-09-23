<?php
header('Content-Type: application/json');
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

$role = $_SESSION['role'] ?? '';
$position = $_SESSION['position'] ?? '';
$school_id = $_SESSION['school_id'] ?? null;
$is_director = (strpos($position, 'ผู้อำนวยการ') !== false);

if ($role !== 'admin' && $role !== 'super_admin' && !$is_director) {
    die(json_encode(['error' => 'Forbidden']));
}

try {
    // Get all teachers in the same school
    $sql = "SELECT id, name, last_name, position, username, login_count, last_login 
            FROM users 
            WHERE school_id = ? AND role = 'teacher'
            ORDER BY last_login DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$school_id]);
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($stats);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
