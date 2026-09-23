<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$project_id = (int)($data['project_id'] ?? 0);
$expense_date = $data['expense_date'] ?? date('Y-m-d');
$doc_number = $data['doc_number'] ?? '';
$title = $data['title'] ?? '';
$category = $data['category'] ?? 'materials';
$amount = (float)($data['amount'] ?? 0);
$disbursed_by = $data['disbursed_by'] ?? 'เจ้าหน้าที่การเงิน';
$receipt_note = $data['receipt_note'] ?? '';

if (!$project_id || empty($title) || $amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลรายการเบิกจ่ายและจำนวนเงินให้ถูกต้อง']);
    exit;
}

if (!$pdo) {
    echo json_encode(['status' => 'success', 'message' => 'บันทึกรายการเบิกจ่ายสำเร็จ (จำลอง)']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO project_expenses (project_id, expense_date, doc_number, title, category, amount, disbursed_by, receipt_note)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$project_id, $expense_date, $doc_number, $title, $category, $amount, $disbursed_by, $receipt_note]);
    echo json_encode(['status' => 'success', 'message' => 'บันทึกรายการค่าใช้จ่ายจริงสำเร็จ']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
