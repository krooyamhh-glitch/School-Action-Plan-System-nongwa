<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

try {
    ensureDatabaseIntegrity($pdo);

    $selectedYearId = isset($_GET['year_id']) ? (int)$_GET['year_id'] : 0;
    
    // ดึงปีงบประมาณทั้งหมดอย่างปลอดภัย ตรวจสอบคอลัมน์ year หรือ year_be
    $colsFy = $pdo->query("SHOW COLUMNS FROM `fiscal_years`")->fetchAll(PDO::FETCH_COLUMN);
    $orderBy = in_array('year', $colsFy) ? "year DESC, id DESC" : (in_array('year_be', $colsFy) ? "year_be DESC, id DESC" : "id DESC");
    $stmt_years = $pdo->query("SELECT * FROM fiscal_years ORDER BY $orderBy");
    $fiscalYears = $stmt_years->fetchAll(PDO::FETCH_ASSOC);

    // หากยังไม่มีปีงบประมาณเลย ให้สร้างปีงบประมาณเริ่มต้น พ.ศ. 2568
    if (empty($fiscalYears)) {
        $stmtInitYear = $pdo->prepare("
            INSERT INTO fiscal_years (
                school_id, year, year_be, start_date, end_date, is_current, status, 
                total_budget_base, utility_reserve, utility_reserve_notes, allocatable_budget
            ) VALUES (
                1, '2568', 2568, '2024-10-01', '2025-09-30', 1, 'active',
                0.00, 0.00, 'กันไว้สำหรับค่าสาธารณูปโภค (ค่าน้ำ ค่าไฟ)', 0.00
            )
        ");
        $stmtInitYear->execute();
        $selectedYearId = (int)$pdo->lastInsertId();

        $stmt_years = $pdo->query("SELECT * FROM fiscal_years ORDER BY $orderBy");
        $fiscalYears = $stmt_years->fetchAll(PDO::FETCH_ASSOC);
    }

    // ทำความสะอาดและกำหนดค่า year ให้แน่ใจว่ามีเสมอ
    foreach ($fiscalYears as &$fyItem) {
        if (!isset($fyItem['year']) || empty($fyItem['year'])) {
            $fyItem['year'] = (string)($fyItem['year_be'] ?? $fyItem['fiscal_year'] ?? (2567 + (int)$fyItem['id']));
        }
    }
    unset($fyItem);

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
    $school = $stmt_school->fetch(PDO::FETCH_ASSOC);
    if (!$school) {
        $school = [
            'name' => 'โรงเรียน',
            'affiliation' => '',
            'director_name' => ''
        ];
    }

    // บุคลากรในสถานศึกษา
    $stmt_users = $pdo->query("SELECT id, name, username, id_card, position, department, role, phone, email, is_approved, COALESCE(status, 'active') as status FROM users ORDER BY id ASC");
    $users = $stmt_users->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // แหล่งงบประมาณ
    $stmt_sources = $pdo->prepare("SELECT * FROM budget_sources WHERE fiscal_year_id = ? ORDER BY id ASC");
    $stmt_sources->execute([$selectedYearId]);
    $sources = $stmt_sources->fetchAll(PDO::FETCH_ASSOC);

    $totalBudgetReceived = 0;
    foreach ($sources as $s) {
        $totalBudgetReceived += (float)$s['amount'];
    }

    // คำนวณสรุปเงินอุดหนุนรายหัวจาก student_subsidies
    $stmtSub = $pdo->prepare("
        SELECT 
            COALESCE(SUM(student_count), 0) as total_students,
            COALESCE(SUM(student_count * subsidy_rate), 0) as normal_subsidy,
            COALESCE(SUM(student_count * small_school_subsidy), 0) as small_school_subsidy,
            COALESCE(SUM(total_subsidy_amount), 0) as total_subsidy,
            COALESCE(SUM(total_dev_amount), 0) as total_dev,
            COALESCE(SUM(total_amount), 0) as grand_total
        FROM student_subsidies 
        WHERE fiscal_year_id = ?
    ");
    $stmtSub->execute([$selectedYearId]);
    $subSummary = $stmtSub->fetch(PDO::FETCH_ASSOC);
    $calculatedSubsidyTotal = $subSummary ? (float)$subSummary['grand_total'] : 0.00;

    // การจัดสรรงบ 5 ช่อง (4 กลุ่มงาน + กันไว้ค่าใช้จ่ายอื่นๆ)
    $stmt_alloc = $pdo->prepare("SELECT * FROM department_allocations WHERE fiscal_year_id = ? ORDER BY id ASC");
    $stmt_alloc->execute([$selectedYearId]);
    $allocations = $stmt_alloc->fetchAll(PDO::FETCH_ASSOC);

    // หากยังไม่มีการกำหนด 5 กลุ่มงานสำหรับปีนี้ ให้สร้างค่าเริ่มต้น
    if (empty($allocations) && $selectedYearId > 0) {
        $defaultDepts = [
            ['academic', 'งานบริหารงานวิชาการ', 45.00, 'ยกระดับผลสัมฤทธิ์ทางการเรียนและพัฒนาคุณภาพผู้เรียน'],
            ['personnel', 'งานบุคคล', 10.00, 'พัฒนาครูและบุคลากรทางการศึกษา วินัยและมาตรฐานวิชาชีพ'],
            ['budget', 'งานงบประมาณ', 10.00, 'บริหารการเงิน บัญชี พัสดุและสินทรัพย์'],
            ['general', 'งานบริหารงานทั่วไป', 20.00, 'อาคารสถานที่ ความปลอดภัย สัมพันธ์ชุมชน และสิ่งแวดล้อม'],
            ['reserve', 'กันไว้สำหรับค่าใช้จ่ายอื่นๆ', 15.00, 'งบสำรองจ่ายกรณีฉุกเฉินและภารกิจเร่งด่วน']
        ];

        $stmtDefAlloc = $pdo->prepare("
            INSERT INTO department_allocations (
                school_id, fiscal_year_id, department, department_name, percentage, allocated_amount, notes
            ) VALUES (1, ?, ?, ?, ?, 0.00, ?)
        ");
        foreach ($defaultDepts as $d) {
            $stmtDefAlloc->execute([$selectedYearId, $d[0], $d[1], $d[2], $d[3]]);
        }

        $stmt_alloc->execute([$selectedYearId]);
        $allocations = $stmt_alloc->fetchAll(PDO::FETCH_ASSOC);
    }

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
    $projects = $stmt_proj->fetchAll(PDO::FETCH_ASSOC);

    // ดึงค่าใช้จ่ายจริงของแต่ละโครงการ
    $totalApprovedBudget = 0;
    $totalSpentAcrossAll = 0;
    $approvedCount = 0;

    foreach ($projects as &$p) {
        $stmt_exp = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as spent FROM project_expenses WHERE project_id = ?");
        $stmt_exp->execute([$p['id']]);
        $exp_row = $stmt_exp->fetch(PDO::FETCH_ASSOC);
        $spent = (float)($exp_row['spent'] ?? 0);
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

    // ดึงข้อมูลงบประมาณและยอดกันสาธารณูปโภค
    $totalBudgetBase = $currentFiscalYear ? (float)($currentFiscalYear['total_budget_base'] ?? 0) : 0;
    if ($totalBudgetBase <= 0 && $totalBudgetReceived > 0) {
        $totalBudgetBase = $totalBudgetReceived;
    }
    if ($totalBudgetBase <= 0 && $calculatedSubsidyTotal > 0) {
        $totalBudgetBase = $calculatedSubsidyTotal;
    }

    $utilityReserve = $currentFiscalYear ? (float)($currentFiscalYear['utility_reserve'] ?? 0) : 0;
    $utilityReserveNotes = $currentFiscalYear ? ($currentFiscalYear['utility_reserve_notes'] ?? 'กันไว้สำหรับค่าสาธารณูปโภค (ค่าน้ำ ค่าไฟ)') : 'กันไว้สำหรับค่าสาธารณูปโภค (ค่าน้ำ ค่าไฟ)';
    $allocatableBudget = max(0, $totalBudgetBase - $utilityReserve);

    echo json_encode([
        'status' => 'success',
        'school' => $school,
        'users' => $users,
        'fiscalYears' => $fiscalYears,
        'currentFiscalYear' => $currentFiscalYear,
        'budgetSources' => $sources,
        'departmentAllocations' => $allocations,
        'projects' => $projects,
        'subsidiesSummary' => [
            'total_students' => (int)($subSummary['total_students'] ?? 0),
            'is_small_school' => ((int)($subSummary['total_students'] ?? 0) > 0 && (int)($subSummary['total_students'] ?? 0) < 120),
            'normal_subsidy' => (float)($subSummary['normal_subsidy'] ?? 0),
            'small_school_subsidy' => (float)($subSummary['small_school_subsidy'] ?? 0),
            'total_subsidy' => (float)($subSummary['total_subsidy'] ?? 0),
            'total_dev' => (float)($subSummary['total_dev'] ?? 0),
            'grand_total' => $calculatedSubsidyTotal
        ],
        'budgetConfig' => [
            'totalBudgetBase' => $totalBudgetBase,
            'calculatedSubsidyTotal' => $calculatedSubsidyTotal,
            'utilityReserve' => $utilityReserve,
            'utilityReserveNotes' => $utilityReserveNotes,
            'allocatableBudget' => $allocatableBudget
        ],
        'summary' => [
            'totalBudgetReceived' => $totalBudgetReceived > 0 ? $totalBudgetReceived : $totalBudgetBase,
            'totalApprovedBudget' => $totalApprovedBudget,
            'totalSpentAcrossAll' => $totalSpentAcrossAll,
            'approvedProjectsCount' => $approvedCount,
            'totalProjectsCount' => count($projects),
            'disbursementRate' => $totalApprovedBudget > 0 ? round(($totalSpentAcrossAll / $totalApprovedBudget) * 100, 1) : 0
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
