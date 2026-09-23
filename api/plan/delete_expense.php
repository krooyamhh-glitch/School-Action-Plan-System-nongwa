<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$id = (int)($data['id'] ?? 0);

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ระบุรายการไม่ถูกต้อง']);
    exit;
}

if (!$pdo) {
    echo json_encode(['status' => 'success', 'message' => 'ลบรายการเบิกจ่ายสำเร็จ (จำลอง)']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM project_expenses WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success', 'message' => 'ลบรายการเบิกจ่ายสำเร็จ']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
