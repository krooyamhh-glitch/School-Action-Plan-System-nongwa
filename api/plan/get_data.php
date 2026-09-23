<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

if (!$pdo) {
    // Return empty fallback structure if DB is not connected
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

try {
    $selectedYearId = isset($_GET['year_id']) ? (int)$_GET['year_id'] : 0;
    
    // ดึงปีงบประมาณทั้งหมด
    $stmt_years = $pdo->query("SELECT * FROM fiscal_years ORDER BY year DESC");
    $fiscalYears = $stmt_years->fetchAll();

    if ($selectedYearId === 0 && !empty($fiscalYears)) {
        foreach ($fiscalYears as $y) {
            if ($y['is_current'] == 1) {
                $selectedYearId = (int)$y['id'];
                break;
            }
        }
        if ($selectedYearId === 0) $selectedYearId = (int)$fiscalYears[0]['id'];
    }

    // ปีงบประมาณปัจจุบัน
    $currentFiscalYear = null;
    foreach ($fiscalYears as $y) {
        if ($y['id'] == $selectedYearId) {
            $currentFiscalYear = $y;
            break;
        }
    }

    // ข้อมูลโรงเรียน
    $stmt_school = $pdo->query("SELECT * FROM schools LIMIT 1");
    $school = $stmt_school->fetch();
    if (!$school) {
        $school = [
            'name' => 'โรงเรียน',
            'affiliation' => '',
            'director_name' => ''
        ];
    }

    // บุคลากรในสถานศึกษา
    $stmt_users = $pdo->query("SELECT id, name, username, id_card, position, department, role, phone, email, status FROM users ORDER BY id ASC");
    $users = $stmt_users->fetchAll() ?: [];

    // แหล่งงบประมาณ
    $stmt_sources = $pdo->prepare("SELECT * FROM budget_sources WHERE fiscal_year_id = ? ORDER BY id ASC");
    $stmt_sources->execute([$selectedYearId]);
    $sources = $stmt_sources->fetchAll();

    $totalBudgetReceived = 0;
    foreach ($sources as $s) {
        $totalBudgetReceived += (float)$s['amount'];
    }

    // การจัดสรรงบ 4 กลุ่มงาน
    $stmt_alloc = $pdo->prepare("SELECT * FROM department_allocations WHERE fiscal_year_id = ? ORDER BY id ASC");
    $stmt_alloc->execute([$selectedYearId]);
    $allocations = $stmt_alloc->fetchAll();

    // โครงการ
    $stmt_proj = $pdo->prepare("
        SELECT p.*, bs.name as budget_source_name, u.name as proposer_name 
        FROM projects p 
        LEFT JOIN budget_sources bs ON p.budget_source_id = bs.id 
        LEFT JOIN users u ON p.proposer_id = u.id 
        WHERE p.fiscal_year_id = ? 
        ORDER BY p.id ASC
    ");
    $stmt_proj->execute([$selectedYearId]);
    $projects = $stmt_proj->fetchAll();

    // ดึงค่าใช้จ่ายจริงของแต่ละโครงการ
    $totalApprovedBudget = 0;
    $totalSpentAcrossAll = 0;
    $approvedCount = 0;

    foreach ($projects as &$p) {
        $stmt_exp = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as spent FROM project_expenses WHERE project_id = ?");
        $stmt_exp->execute([$p['id']]);
        $exp_row = $stmt_exp->fetch();
        $spent = (float)$exp_row['spent'];
        $approved = (float)$p['approved_budget'];
        $remaining = $approved - $spent;
        $pct = $approved > 0 ? round(($spent / $approved) * 100, 1) : 0;

        $p['financials'] = [
            'requested' => (float)$p['requested_budget'],
            'approved' => $approved,
            'spent' => $spent,
            'remaining' => $remaining,
            'percentSpent' => $pct
        ];

        if ($p['status'] === 'approved') {
            $approvedCount++;
            $totalApprovedBudget += $approved;
        }
        $totalSpentAcrossAll += $spent;
    }
    unset($p);

    echo json_encode([
        'status' => 'success',
        'school' => $school,
        'users' => $users,
        'fiscalYears' => $fiscalYears,
        'currentFiscalYear' => $currentFiscalYear,
        'budgetSources' => $sources,
        'departmentAllocations' => $allocations,
        'projects' => $projects,
        'summary' => [
            'totalBudgetReceived' => $totalBudgetReceived,
            'totalApprovedBudget' => $totalApprovedBudget,
            'totalSpentAcrossAll' => $totalSpentAcrossAll,
            'approvedProjectsCount' => $approvedCount,
            'totalProjectsCount' => count($projects),
            'disbursementRate' => $totalApprovedBudget > 0 ? round(($totalSpentAcrossAll / $totalApprovedBudget) * 100, 1) : 0
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
