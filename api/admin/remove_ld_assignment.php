<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$assignment_id = $data['assignment_id'] ?? 0;

try {
    $stmt = $pdo->prepare('DELETE FROM learner_development_assignments WHERE id = ?');
    $stmt->execute([$assignment_id]);
    
    echo json_encode(['message' => 'ยกเลิกการมอบหมายสำเร็จ']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
