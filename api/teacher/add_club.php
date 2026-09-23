<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$school_id = $_SESSION['school_id'];
$name = $data['name'] ?? '';
$academic_year = $data['academic_year'] ?? '2567';

if (empty($name)) {
    echo json_encode(['error' => 'กรุณาระบุชื่อชุมนุม']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO clubs (school_id, name, academic_year) VALUES (?, ?, ?)');
    $stmt->execute([$school_id, $name, $academic_year]);
    
    echo json_encode(['message' => 'เพิ่มชุมนุมสำเร็จ', 'id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
