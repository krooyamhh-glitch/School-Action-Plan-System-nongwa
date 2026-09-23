<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

try {
    if ($action === 'add') {
        $category_id = $data['category_id'];
        $option_text = $data['option_text'];
        
        $stmt = $pdo->prepare("INSERT INTO behavior_options (category_id, option_text) VALUES (?, ?)");
        $stmt->execute([$category_id, $option_text]);
        
        echo json_encode(['status' => 'success', 'id' => $pdo->lastInsertId()]);
    } 
    else if ($action === 'delete') {
        $id = $data['id'];
        $stmt = $pdo->prepare("DELETE FROM behavior_options WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success']);
    }
    else {
        echo json_encode(['error' => 'Action ไม่ถูกต้อง']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
