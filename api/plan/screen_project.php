<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$project_id = (int)($data['project_id'] ?? 0);
$adjusted_budget = isset($data['adjusted_budget']) ? (float)$data['adjusted_budget'] : null;
$screening_note = $data['screening_note'] ?? '';
$action = $data['action'] ?? 'screened';

if (!$project_id) {
    echo json_encode(['status' => 'error', 'message' => 'ระบุโครงการไม่ถูกต้อง']);
    exit;
}

if (!$pdo) {
    echo json_encode(['status' => 'success', 'message' => 'บันทึกการกลั่นกรองสำเร็จ (จำลอง)']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE projects SET 
            approved_budget = COALESCE(?, approved_budget),
            screening_note = ?,
            status = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$adjusted_budget, $screening_note, $action, $project_id]);
    echo json_encode(['status' => 'success', 'message' => 'บันทึกการกลั่นกรองและปรับวงเงินเรียบร้อย']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
