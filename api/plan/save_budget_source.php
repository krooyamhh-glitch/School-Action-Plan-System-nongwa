<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$fiscal_year_id = (int)($data['fiscal_year_id'] ?? 1);
$id = isset($data['id']) && $data['id'] ? (int)$data['id'] : null;
$name = trim($data['name'] ?? '');
$category = $data['category'] ?? 'subsidy';
$amount = (float)($data['amount'] ?? 0);
$received_date = !empty($data['received_date']) ? $data['received_date'] : null;
$description = $data['description'] ?? '';

if (empty($name) || $amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุชื่อแหล่งงบประมาณและจำนวนเงินให้ถูกต้อง']);
    exit;
}

if (!$pdo) {
    echo json_encode(['status' => 'success', 'message' => 'บันทึกสำเร็จ (จำลอง)']);
    exit;
}

try {
    if ($id) {
        $stmt = $pdo->prepare("
            UPDATE budget_sources SET 
                name = ?, category = ?, amount = ?, received_date = ?, description = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $category, $amount, $received_date, $description, $id]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO budget_sources (fiscal_year_id, name, category, amount, received_date, description)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$fiscal_year_id, $name, $category, $amount, $received_date, $description]);
    }
    echo json_encode(['status' => 'success', 'message' => 'บันทึกแหล่งงบประมาณสำเร็จ']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
