<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? 0;

try {
    $stmt = $pdo->prepare('DELETE FROM clubs WHERE id = ?');
    $stmt->execute([$id]);
    
    echo json_encode(['message' => 'ลบชุมนุมสำเร็จ']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
