<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$project_id = (int)($data['project_id'] ?? 0);
$progress_percentage = (int)($data['progress_percentage'] ?? 0);
$execution_status = $data['execution_status'] ?? 'in_progress';
$results_summary = $data['results_summary'] ?? '';
$obstacles = $data['obstacles'] ?? '';
$recommendations = $data['recommendations'] ?? '';
$recorded_by = $data['recorded_by'] ?? 'ผู้รับผิดชอบโครงการ';

if (!$project_id) {
    echo json_encode(['status' => 'error', 'message' => 'ระบุโครงการไม่ถูกต้อง']);
    exit;
}

if (!$pdo) {
    echo json_encode(['status' => 'success', 'message' => 'บันทึกความก้าวหน้าสำเร็จ (จำลอง)']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE projects SET 
            progress_percentage = ?,
            execution_status = ?,
            results_summary = ?,
            obstacles = ?,
            recommendations = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$progress_percentage, $execution_status, $results_summary, $obstacles, $recommendations, $project_id]);

    $stmt_log = $pdo->prepare("
        INSERT INTO project_progress_logs (project_id, log_date, progress_percent, details, obstacles, solutions, recorded_by)
        VALUES (?, CURDATE(), ?, ?, ?, ?, ?)
    ");
    $stmt_log->execute([$project_id, $progress_percentage, $results_summary ?: 'รายงานผลความก้าวหน้า', $obstacles, $recommendations, $recorded_by]);

    echo json_encode(['status' => 'success', 'message' => 'บันทึกการติดตามความก้าวหน้าสำเร็จ']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
