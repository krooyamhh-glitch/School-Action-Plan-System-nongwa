<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$project_id = (int)($data['project_id'] ?? 0);
$action = $data['action'] ?? 'approved';
$director_note = $data['director_note'] ?? '';
$approved_budget = isset($data['approved_budget']) ? (float)$data['approved_budget'] : null;

if (!$project_id) {
    echo json_encode(['status' => 'error', 'message' => 'ระบุโครงการไม่ถูกต้อง']);
    exit;
}

if (!$pdo) {
    echo json_encode(['status' => 'success', 'message' => 'บันทึกการอนุมัติสำเร็จ (จำลอง)']);
    exit;
}

try {
    $approved_at = ($action === 'approved') ? date('Y-m-d H:i:s') : null;
    $exec_status = ($action === 'approved') ? 'in_progress' : 'not_started';

    $stmt = $pdo->prepare("
        UPDATE projects SET 
            status = ?,
            director_note = ?,
            approved_budget = COALESCE(?, approved_budget),
            approved_at = ?,
            execution_status = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$action, $director_note, $approved_budget, $approved_at, $exec_status, $project_id]);
    echo json_encode(['status' => 'success', 'message' => 'บันทึกการอนุมัติโครงการสำเร็จ']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
