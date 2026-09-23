<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && !$_SESSION['is_academic'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$q = $_GET['q'] ?? '';
$school_id = $_SESSION['school_id'];

if (empty($q)) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, prefix, name, last_name, student_code, national_id, level, room, birthday 
                           FROM students 
                           WHERE school_id = ? 
                           AND (name LIKE ? OR student_code LIKE ? OR national_id LIKE ?) 
                           LIMIT 10");
    $searchTerm = "%$q%";
    $stmt->execute([$school_id, $searchTerm, $searchTerm, $searchTerm]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($students);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
