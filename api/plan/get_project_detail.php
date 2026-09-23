<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ระบุรหัสโครงการไม่ถูกต้อง']);
    exit;
}

if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่มีการเชื่อมต่อฐานข้อมูล']);
    exit;
}

try {
    $stmt_proj = $pdo->prepare("
        SELECT p.*, bs.name as budget_source_name, u.name as proposer_name, u.position as proposer_position,
               fy.year as fiscal_year_name, s.name as school_name, s.affiliation, s.director_name
        FROM projects p
        LEFT JOIN budget_sources bs ON p.budget_source_id = bs.id
        LEFT JOIN users u ON p.proposer_id = u.id
        LEFT JOIN fiscal_years fy ON p.fiscal_year_id = fy.id
        LEFT JOIN schools s ON fy.school_id = s.id
        WHERE p.id = ?
    ");
    $stmt_proj->execute([$id]);
    $project = $stmt_proj->fetch();

    if (!$project) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบโครงการ']);
        exit;
    }

    $stmt_items = $pdo->prepare("SELECT * FROM project_budget_items WHERE project_id = ? ORDER BY id ASC");
    $stmt_items->execute([$id]);
    $items = $stmt_items->fetchAll();

    $stmt_exp = $pdo->prepare("SELECT * FROM project_expenses WHERE project_id = ? ORDER BY expense_date DESC");
    $stmt_exp->execute([$id]);
    $expenses = $stmt_exp->fetchAll();

    $stmt_logs = $pdo->prepare("SELECT * FROM project_progress_logs WHERE project_id = ? ORDER BY log_date DESC");
    $stmt_logs->execute([$id]);
    $logs = $stmt_logs->fetchAll();

    $totalSpent = 0;
    foreach ($expenses as $e) {
        $totalSpent += (float)$e['amount'];
    }
    $approvedBudget = (float)$project['approved_budget'];

    echo json_encode([
        'status' => 'success',
        'project' => $project,
        'items' => $items,
        'expenses' => $expenses,
        'progress_logs' => $logs,
        'financials' => [
            'approved' => $approvedBudget,
            'spent' => $totalSpent,
            'remaining' => $approvedBudget - $totalSpent,
            'percentSpent' => $approvedBudget > 0 ? round(($totalSpent / $approvedBudget) * 100, 1) : 0
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
