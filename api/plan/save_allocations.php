<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$fiscal_year_id = (int)($data['fiscal_year_id'] ?? 1);
$allocations = $data['allocations'] ?? [];

if (empty($allocations)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่มีข้อมูลการจัดสรร']);
    exit;
}

$sumPct = 0;
foreach ($allocations as $a) {
    $sumPct += (float)($a['percentage'] ?? 0);
}

if (abs($sumPct - 100.0) > 0.05) {
    echo json_encode(['status' => 'error', 'message' => 'ผลรวมเปอร์เซ็นต์ต้องได้ 100% พอดี']);
    exit;
}

if (!$pdo) {
    echo json_encode(['status' => 'success', 'message' => 'บันทึกการจัดสรรสำเร็จ (จำลอง)']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE department_allocations SET 
            percentage = ?, allocated_amount = ?, notes = ?, updated_at = NOW()
        WHERE id = ? AND fiscal_year_id = ?
    ");
    foreach ($allocations as $a) {
        $stmt->execute([
            (float)$a['percentage'],
            (float)$a['allocated_amount'],
            $a['notes'] ?? '',
            (int)$a['id'],
            $fiscal_year_id
        ]);
    }
    echo json_encode(['status' => 'success', 'message' => 'บันทึกการจัดสรรงบประมาณ 100% เรียบร้อย']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
