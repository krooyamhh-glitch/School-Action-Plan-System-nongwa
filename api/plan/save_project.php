<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$id = isset($data['id']) ? (int)$data['id'] : null;
$fiscal_year_id = (int)($data['fiscal_year_id'] ?? 1);
$department = $data['department'] ?? 'academic';
$code = $data['code'] ?? '';
$name = $data['name'] ?? '';
$proposer_id = (int)($data['proposer_id'] ?? 8);
$strategy_alignment = $data['strategy_alignment'] ?? '';
$standard_alignment = $data['standard_alignment'] ?? 'มาตรฐานที่ 1 คุณภาพของผู้เรียน';
$rationale = $data['rationale'] ?? '';
$objectives = $data['objectives'] ?? '';
$target_qty = $data['target_qty'] ?? '';
$target_quality = $data['target_quality'] ?? '';
$start_date = !empty($data['start_date']) ? $data['start_date'] : null;
$end_date = !empty($data['end_date']) ? $data['end_date'] : null;
$location = $data['location'] ?? 'โรงเรียน';
$budget_source_id = !empty($data['budget_source_id']) ? (int)$data['budget_source_id'] : null;
$requested_budget = (float)($data['requested_budget'] ?? 0);
$expected_outcomes = $data['expected_outcomes'] ?? '';
$indicators = $data['indicators'] ?? '';
$evaluation_method = $data['evaluation_method'] ?? '';

if (empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกชื่อโครงการ']);
    exit;
}

if (!$pdo) {
    echo json_encode(['status' => 'success', 'message' => 'จำลองการบันทึกสำเร็จ (ไม่มีการเชื่อมต่อฐานข้อมูล)']);
    exit;
}

try {
    if ($id) {
        $stmt = $pdo->prepare("
            UPDATE projects SET 
                fiscal_year_id = ?, department = ?, code = ?, name = ?, proposer_id = ?,
                strategy_alignment = ?, standard_alignment = ?, rationale = ?, objectives = ?,
                target_qty = ?, target_quality = ?, start_date = ?, end_date = ?, location = ?,
                budget_source_id = ?, requested_budget = ?, expected_outcomes = ?, indicators = ?,
                evaluation_method = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $fiscal_year_id, $department, $code, $name, $proposer_id,
            $strategy_alignment, $standard_alignment, $rationale, $objectives,
            $target_qty, $target_quality, $start_date, $end_date, $location,
            $budget_source_id, $requested_budget, $expected_outcomes, $indicators,
            $evaluation_method, $id
        ]);
        $projectId = $id;
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO projects (
                fiscal_year_id, department, code, name, proposer_id,
                strategy_alignment, standard_alignment, rationale, objectives,
                target_qty, target_quality, start_date, end_date, location,
                budget_source_id, requested_budget, approved_budget, expected_outcomes,
                indicators, evaluation_method, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0.00, ?, ?, ?, 'submitted')
        ");
        $stmt->execute([
            $fiscal_year_id, $department, $code, $name, $proposer_id,
            $strategy_alignment, $standard_alignment, $rationale, $objectives,
            $target_qty, $target_quality, $start_date, $end_date, $location,
            $budget_source_id, $requested_budget, $expected_outcomes,
            $indicators, $evaluation_method
        ]);
        $projectId = (int)$pdo->lastInsertId();
    }

    // จัดการรายการค่าใช้จ่ายแจกแจง
    if (isset($data['items']) && is_array($data['items'])) {
        $pdo->prepare("DELETE FROM project_budget_items WHERE project_id = ?")->execute([$projectId]);
        $stmt_item = $pdo->prepare("
            INSERT INTO project_budget_items (project_id, category, item_name, quantity, unit, unit_price, total_price)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($data['items'] as $it) {
            $cat = $it['category'] ?? 'materials';
            $iName = $it['item_name'] ?? 'รายการค่าใช้จ่าย';
            $qty = (float)($it['quantity'] ?? 1);
            $unit = $it['unit'] ?? 'หน่วย';
            $price = (float)($it['unit_price'] ?? 0);
            $total = (float)($it['total_price'] ?? ($qty * $price));
            $stmt_item->execute([$projectId, $cat, $iName, $qty, $unit, $price, $total]);
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'บันทึกโครงการสำเร็จ', 'project_id' => $projectId]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
