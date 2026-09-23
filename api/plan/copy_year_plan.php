<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$from_year_id = (int)($data['from_year_id'] ?? 2);
$to_year_id = (int)($data['to_year_id'] ?? 1);

if (!$from_year_id || !$to_year_id) {
    echo json_encode(['status' => 'error', 'message' => 'ระบุปีงบประมาณต้นทางและปลายทางไม่ถูกต้อง']);
    exit;
}

if (!$pdo) {
    echo json_encode(['status' => 'success', 'message' => 'คัดลอกแผนงานสำเร็จ (จำลอง)']);
    exit;
}

try {
    // ดึงโครงการจากปีต้นทาง
    $stmt_from = $pdo->prepare("SELECT * FROM projects WHERE fiscal_year_id = ?");
    $stmt_from->execute([$from_year_id]);
    $projects = $stmt_from->fetchAll();

    $stmt_insert = $pdo->prepare("
        INSERT INTO projects (
            fiscal_year_id, department, code, name, proposer_id, strategy_alignment, standard_alignment,
            rationale, objectives, target_qty, target_quality, location, requested_budget,
            approved_budget, expected_outcomes, indicators, evaluation_method, status
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, 'submitted'
        )
    ");

    $count = 0;
    foreach ($projects as $p) {
        $stmt_insert->execute([
            $to_year_id, $p['department'], $p['code'], $p['name'], $p['proposer_id'],
            $p['strategy_alignment'], $p['standard_alignment'], $p['rationale'], $p['objectives'],
            $p['target_qty'], $p['target_quality'], $p['location'], $p['requested_budget'],
            $p['approved_budget'], $p['expected_outcomes'], $p['indicators'], $p['evaluation_method']
        ]);
        $count++;
    }

    echo json_encode(['status' => 'success', 'message' => "คัดลอกโครงการจำนวน $count โครงการสำเร็จ"]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
